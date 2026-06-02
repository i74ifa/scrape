<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartItemResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Platform;
use App\Services\Weight;
use App\Services\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Models\CartBundle;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', user('id'))->with('items.product:id,name,image,price', 'platform')->get();

        // delte zero carts
        $cart->each(function ($cart) {
            if ($cart->items()->count() == 0 || $cart->total === 0) {
                $cart->delete();
                $cart->cart_bundle->updateSummary();
            }
        });

        return CartResource::collection($cart);
    }

    private function createOrUpdateScrapedProduct(string $url, $productDto, Platform $platform): Product
    {
        $convertedPrice = Currency::convert($productDto->price, $productDto->currency, 'SAR')->value();
        return Product::updateOrCreate([
            'url' => $url,
            'name' => $productDto->name,
        ], [
            'slug' => Str::slug($productDto->name ?? 'unknown-product'),
            'description' => $productDto->description ?? $productDto->name,
            'image' => $productDto->image,
            'images' => json_encode($productDto->images ?? []),
            'price' => $convertedPrice,
            'currency' => 'USD', // all prices are converted to USD before saving TODO: remove the behavior :)
            'sale_price' => $convertedPrice,
            'platform_id' => $platform->id,
            'category_id' => $productDto->category,
            'weight' => Weight::parse($productDto->weight ?? Product::DEFAULT_WEIGHT_GRAMS)?->toGrams(),
            'user_id' => user('id'),
        ]);
    }

    public function store(Request $request, Platform $platform)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'url' => 'nullable|url',
                'product_id' => 'nullable|exists:products,id',
                'selectors' => ['required_without:product_id', 'array'],
            ])->validate();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        $withId = $request->filled('product_id');

        $userId = user('id');

        DB::beginTransaction();
        try {
            $cartBundle = CartBundle::getActiveCartBundle();
            $cart = $cartBundle->carts()->where('platform_id', $platform->id)->firstOrCreate([
                'platform_id' => $platform->id,
            ], [
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'total' => 0,
                'user_id' => $userId,
                'discount' => 0,
            ]);

            if ($withId) {
                $product = Product::find($validatedData['product_id']);
            } else {
                $scraperService = $platform->scraping($validatedData['selectors']);
                $product = $this->createOrUpdateScrapedProduct($validatedData['url'], $scraperService, $platform);
            }
            $cartItem = $cart->items()->where('product_id', $product->id)->first();

            if ($cartItem) {
                $cartItem->quantity++;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price,
                    'total' => $product->price,
                ]);
            }

            $cart->updateSummary();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json($e, 400);
        }

        return response()->json([
            'message' => 'Scraping started',
            'product' => ProductResource::make($product),
        ]);
    }

    public function updateQuantity(CartItem $cartItem, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        if ($request->quantity == 0) {
            $cartItem->delete();
            $cartItem->cart->updateSummary();
            return response()->json([
                'status' => 'success',
                'message' => __('Cart item deleted successfully'),
            ]);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->total = $cartItem->price * $cartItem->quantity;
        $cartItem->save();

        $cartItem->cart->updateSummary();

        return response()->json([
            'status' => 'success',
            'message' => __('Cart item updated successfully'),
            'cart_item' => CartItemResource::make($cartItem),
        ]);
    }

    public function totals(Request $request)
    {
        $bundle = CartBundle::where('user_id', user('id'))->first();

        if ($bundle && $bundle->subtotal === 0) {
            $bundle->carts()->delete();
            $bundle->delete();
            return response()->json([
                'data' => (object)[]
            ]);
        }

        if (!$bundle) {
            return response()->json([
                'data' => (object)[]
            ]);
        }

        return response()->json([
            'subtotal' => money($bundle->subtotal),
            'tax' => money($bundle->tax),
            'shipping' => money($bundle->shipping),
            'discount' => money($bundle->discount),
            'local_shipping' => money($bundle->local_shipping),
            'total' => money($bundle->total),
            'address_id' => $bundle->address_id,
        ]);
    }

    public function count(Request $request)
    {
        $count = $request->user()->carts()->withCount('items')->get();

        return response()->json(['count' => $count->sum('items_count')]);
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        $cartItem->cart->updateSummary();

        return response()->json([
            'status' => 'success',
            'message' => __('Cart item deleted successfully'),
        ]);
    }

    public function storeById(Product $product, Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ])->validate();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        $cartBundle = CartBundle::getActiveCartBundle();
        $cart = $cartBundle->carts()->where('platform_id', $product->platform_id)->firstOrCreate(
            ['platform_id' => $product->platform_id],
            [
                'user_id'        => user('id'),
                'subtotal'       => 0,
                'tax'            => 0,
                'shipping'       => 0,
                'total'          => 0,
                'discount'       => 0,
            ]
        );

        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        if ($cartItem) {
            $cartItem->quantity += $validatedData['quantity'];
            $cartItem->total = $cartItem->price * $cartItem->quantity;
            $cartItem->save();
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => $validatedData['quantity'],
                'price'      => $product->price,
                'total'      => $product->price * $validatedData['quantity'],
            ]);
        }

        $cart->updateSummary();

        return response()->json([
            'status' => 'success',
            'message' => __('Cart item added successfully'),
            'cart_item' => CartItemResource::make($cartItem),
        ]);
    }
}
