<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Platform;
use App\Services\Weight;
use App\Services\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->with('items.product:id,name,image,price', 'platform')->get();
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
}
