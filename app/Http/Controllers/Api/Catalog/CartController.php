<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogCartResource;
use App\Models\Catalog\CatalogCart;
use App\Models\Catalog\CatalogCartItem;
use App\Models\Catalog\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Customer-facing catalog cart. One cart per user; pricing is derived live from
 * the catalog so it always reflects current prices.
 */
class CartController extends Controller
{
    public function index()
    {
        return new CatalogCartResource($this->loadedCart());
    }

    public function count()
    {
        $cart = CatalogCart::forUser(user()->id);

        return response()->json(['count' => (int) $cart->items()->sum('quantity')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', Rule::exists('catalog_products', 'id')->where('is_active', true)],
            'variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        /** @var Product $product */
        $product = Product::with('variants')->findOrFail($data['product_id']);
        $variantId = $data['variant_id'] ?? null;
        $quantity = (int) ($data['quantity'] ?? 1);

        // A product with variants requires a valid variant that belongs to it;
        // a simple product must not carry one.
        if ($product->has_variants) {
            if (! $variantId || ! $product->variants->firstWhere('id', $variantId)) {
                throw ValidationException::withMessages(['variant_id' => 'يجب اختيار خيار صالح لهذا المنتج.']);
            }
        } else {
            $variantId = null;
        }

        $cart = CatalogCart::forUser(user()->id);

        // Stack quantity onto an existing matching line, or create a new one.
        $item = $cart->items()->firstOrNew([
            'catalog_product_id' => $product->id,
            'product_variant_id' => $variantId,
        ]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->save();

        return new CatalogCartResource($this->loadedCart());
    }

    public function updateQuantity(Request $request, CatalogCartItem $item)
    {
        $this->authorizeItem($item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        if ($data['quantity'] === 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $data['quantity']]);
        }

        return new CatalogCartResource($this->loadedCart());
    }

    public function destroy(CatalogCartItem $item)
    {
        $this->authorizeItem($item);
        $item->delete();

        return new CatalogCartResource($this->loadedCart());
    }

    public function clear()
    {
        CatalogCart::forUser(user()->id)->items()->delete();

        return new CatalogCartResource($this->loadedCart());
    }

    /** The current user's cart with everything the resources need eager-loaded. */
    private function loadedCart(): CatalogCart
    {
        return CatalogCart::forUser(user()->id)->load([
            'items.product.images',
            'items.variant.attributeValues',
        ]);
    }

    /** Guard: an item must belong to the current user's cart. */
    private function authorizeItem(CatalogCartItem $item): void
    {
        abort_unless($item->cart && $item->cart->user_id === user()->id, 403);
    }
}
