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
use App\Models\CartItem;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->with('items.product:id,name,image,price', 'platform')->get();

        // delte zero carts
        $cart->each(function ($cart) {
            if ($cart->items()->count() == 0 || $cart->total === 0) {
                $cart->delete();
            }
        });

        return CartResource::collection($cart);
    }

    private function createOrUpdateScrapedProduct(string $url, $productDto, Platform $platform): Product
    {
        $convertedPrice = Currency::convert($productDto->price, $productDto->currency, 'SAR');
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
            'user_id' => auth()->id(),
        ]);
    }

    public function store(Request $request, Platform $platform)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'url' => 'required',
                'selectors' => ['required', 'array'],
            ])->validate();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
        $cart = Cart::getCart($platform->id);
        $scraperService = $platform->scraping($validatedData['selectors']);
        $product = $this->createOrUpdateScrapedProduct($validatedData['url'], $scraperService, $platform);
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

        return response()->json([
            'message' => 'Scraping started',
            'product' => ProductResource::make($product),
        ]);
    }

    public function incrementQty(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cartItem = CartItem::where('user_id', auth()->id())->where('id', $request->cart_item_id)->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->quantity++;
        $cartItem->save();

        return response()->json([
            'message' => __('Cart item updated successfully'),
            'cart_item' => CartItemResource::make($cartItem),
        ]);
    }

    public function totals(Request $request)
    {
        // $request->validate([
        //     'cart_ids' => 'required|array',
        //     'cart_ids.*' => 'exists:carts,id',
        // ]);

        // $carts = Cart::where('user_id', auth()->id())->whereIn('id', $request->cart_ids)->get();
        $carts = Cart::where('user_id', auth()->id())->get();

        return response()->json([
            'subtotal' => $carts->sum('subtotal'),
            'tax' => $carts->sum('tax'),
            'shipping' => $carts->sum('shipping'),
            'discount' => $carts->sum('discount'),
            'local_shipping' => $carts->sum('local_shipping'),
            'total' => $carts->sum('total'),
        ]);
    }
}
