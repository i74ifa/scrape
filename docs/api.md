# Talabye API — Catalog

Public, read-only storefront endpoints for browsing the **merchant catalog**
(categories, brands, products, and product variants). These power category
browsing, brand pages, product listings, and product detail screens in the app.

> The catalog is separate from the existing scraped-product / cart flow. Catalog
> endpoints live under the `catalog/` prefix and never touch cart or orders.

---

## Conventions

**Base URL**

```
{APP_URL}/api
```

All paths below are relative to `/api` (e.g. `GET /api/catalog/products`).

**Auth** — none. All catalog read endpoints are public. Do **not** send a Bearer
token; they work for guests and logged-in users alike.

**Headers**

```
Accept: application/json
```

**Money fields are formatted display strings, not numbers.** `price`,
`sale_price`, `effective_price`, and `price_range.{min,max}` are already
currency-formatted and localized on the server (e.g. `"139,860 ر.ي"`). Render
them as-is. Do **not** parse them for arithmetic — if you need raw numbers for
calculations, that is a separate change to the API (ask the backend).

**Active only.** Lists return only active products / active brands. Requesting a
single inactive product or brand returns `404`.

**Errors** — JSON, e.g. `404`:

```json
{ "message": "Route not found" }
```

---

## Pagination (products list)

`GET /api/catalog/products` uses **cursor** pagination. The response wraps `data`
with `links` and `meta`:

```json
{
  "data": [ /* product objects */ ],
  "links": { "first": null, "last": null, "prev": null, "next": null },
  "meta": {
    "path": "https://talabye.com/api/catalog/products",
    "per_page": 15,
    "next_cursor": "eyJpZCI6NX0",
    "prev_cursor": null
  }
}
```

Fetch the next page by passing the `next_cursor` back as `?cursor=`. When
`next_cursor` is `null`, there are no more pages.

```
GET /api/catalog/products?cursor=eyJpZCI6NX0&limit=15
```

The other list endpoints (categories, brands) are **not** paginated — they return
a plain `{ "data": [...] }` array.

---

## Endpoints

### 1. List products

```
GET /api/catalog/products
```

**Query params** (all optional)

| Param         | Type   | Description |
|---------------|--------|-------------|
| `category_id` | int    | Filter to a category **and its entire subtree** (a parent category returns products in its child categories too). |
| `brand_id`    | int    | Filter by brand. |
| `search`      | string | Matches `name`, `sku`, or `tags` (LIKE). |
| `limit`       | int    | Page size (default `15`). |
| `cursor`      | string | Pagination cursor (see above). |

**Response** — `data[]` of product objects (list shape: includes `brand`,
`images`, and `variants`, but variant `options` are omitted in the list — fetch
the detail endpoint for full variant option/swatch data).

```json
{
  "data": [
    {
      "id": 5,
      "name": "آيفون 15",
      "slug": "ayfon-15",
      "short_description": null,
      "description": null,
      "price": "139,860 ر.ي",
      "sale_price": null,
      "effective_price": "139,860 ر.ي",
      "price_range": { "min": "139,860 ر.ي", "max": "139,860 ر.ي" },
      "end_discount_date": null,
      "weight": null,
      "sku": null,
      "promotion": null,
      "tags": null,
      "specifications": [],
      "has_variants": true,
      "is_digital": false,
      "is_active": true,
      "image": null,
      "images": [],
      "brand": {
        "id": 6, "name": "Apple", "slug": "apple",
        "image": null, "is_active": true
      },
      "variants": [
        {
          "id": 7, "sku": "R1", "barcode": null,
          "price": "139,860 ر.ي", "sale_price": null,
          "effective_price": "139,860 ر.ي", "weight": null, "is_active": true
        }
      ]
    }
  ],
  "links": { "...": "..." },
  "meta": { "...": "..." }
}
```

Field notes:
- `image` — the primary gallery image URL (or first image), or `null`.
- `images[]` — full gallery: `{ "url": string, "is_primary": bool }`.
- `price_range` — present for variant products (`has_variants: true`); `null` for
  simple products. Use it to show a "from X to Y" range.
- `effective_price` — the price the customer pays (`sale_price` if set, else
  `price`).

---

### 2. Get one product

```
GET /api/catalog/products/{slug}
```

Returns a single product (`{ "data": { ... } }`) with `brand`, `categories`,
`images`, and `variants` — and each variant carries its **`options`** (the
attribute value it's built from, with color swatch):

```json
{
  "data": {
    "id": 5,
    "name": "آيفون 15",
    "...": "(all list fields)",
    "categories": [
      { "id": 17, "name": "هواتف", "slug": "ph", "image": null,
        "parent_id": 16, "depth": 1 }
    ],
    "variants": [
      {
        "id": 7, "sku": "R1", "barcode": null,
        "price": "139,860 ر.ي", "sale_price": null,
        "effective_price": "139,860 ر.ي", "weight": null, "is_active": true,
        "options": [
          { "attribute": "اللون", "value": "أحمر", "color": "#f00" }
        ]
      }
    ]
  }
}
```

Use `variants[].options` to build the variant picker (e.g. color swatches keyed
by `color`, size chips by `value`). An inactive or missing product → `404`.

---

### 3. List categories

```
GET /api/catalog/categories
```

**Query params** (all optional)

| Param       | Type | Description |
|-------------|------|-------------|
| `tree`      | 1    | Return the full nested tree (roots, each with nested `children`). |
| `parent_id` | int  | Return the direct children of a node (lazy tree expansion). |
| _(none)_    | —    | Default: root-level categories only. |

**Response**

```json
{
  "data": [
    {
      "id": 16, "name": "إلكترونيات", "slug": "el",
      "image": null, "parent_id": null, "depth": 0,
      "products_count": 12,
      "children": [
        { "id": 17, "name": "هواتف", "slug": "ph",
          "image": null, "parent_id": 16, "depth": 1 }
      ]
    }
  ]
}
```

- `children` is present only when `?tree=1` (nested) — otherwise omitted.
- `products_count` — products directly in that category.
- To list products of a category (including its subtree), call
  `GET /api/catalog/products?category_id={id}`.

**Patterns**
- Top-level category bar / grid → `GET /api/catalog/categories`
- Full category drawer at once → `GET /api/catalog/categories?tree=1`
- Expand-on-tap tree → `GET /api/catalog/categories?parent_id={id}`

---

### 4. Get one category

```
GET /api/catalog/categories/{id}
```

```json
{
  "data": {
    "id": 16, "name": "إلكترونيات", "slug": "el",
    "image": null, "parent_id": null, "depth": 0,
    "products_count": 12,
    "children": [ { "id": 17, "name": "هواتف", "...": "..." } ]
  }
}
```

---

### 5. List brands

```
GET /api/catalog/brands
```

**Query params**

| Param    | Type   | Description |
|----------|--------|-------------|
| `search` | string | Filter by brand name (LIKE). |

**Response** — active brands only, with product counts.

```json
{
  "data": [
    { "id": 6, "name": "Apple", "slug": "apple",
      "image": null, "is_active": true, "products_count": 4 }
  ]
}
```

To list a brand's products: `GET /api/catalog/products?brand_id={id}`.

---

### 6. Get one brand

```
GET /api/catalog/brands/{id}
```

```json
{
  "data": { "id": 6, "name": "Apple", "slug": "apple",
            "image": null, "is_active": true, "products_count": 4 }
}
```

Inactive or missing brand → `404`.

---

## Typical app flows

**Catalog home**
1. `GET /api/catalog/categories` → category chips/grid.
2. `GET /api/catalog/brands` → brand strip.
3. `GET /api/catalog/products?limit=15` → featured/latest products; page with `cursor`.

**Category screen**
1. `GET /api/catalog/categories/{id}` → header + subcategory chips (`children`).
2. `GET /api/catalog/products?category_id={id}` → product grid (includes subtree).

**Product detail**
1. `GET /api/catalog/products/{id}` → gallery (`images`), price/`price_range`,
   `specifications`, and the variant picker from `variants[].options`.

**Search**
- `GET /api/catalog/products?search={q}` (debounce; page with `cursor`).

---

# Cart & Orders

Authenticated customer endpoints for the catalog: add products to a cart,
manage it, then check out into an order. These live under the same `catalog/`
prefix but, unlike the read-only browsing endpoints above, **require login**.

> Cart and orders are scoped to the **catalog**. They are separate from the
> legacy scraped-product cart/checkout flow and never mix line items.

## Conventions

**Auth — required.** Every endpoint in this section is behind
`auth:sanctum`. Send the user's token:

```
Authorization: Bearer {token}
Accept: application/json
```

No token (or an invalid one) → `401 Unauthenticated`.

**One cart per user.** A cart is created lazily on first access — there is no
"create cart" call. `GET /api/catalog/cart` always returns a cart (empty
`items: []` if nothing has been added).

**Live pricing.** Cart prices are read live from the catalog on every request,
so they always reflect the current price/sale. Prices are **frozen only at
checkout** — the order snapshots them. Money fields are formatted display
strings (same rule as the catalog: render as-is, don't parse).

**Variants.** When a product `has_variants`, a valid `variant_id` belonging to
that product is required; a simple product must not be sent a `variant_id`
(it's ignored). Adding the same product+variant again **stacks quantity** onto
the existing line rather than creating a duplicate.

**Validation errors** → `422` with `{ "message", "errors": { field: [...] } }`.

---

## Cart object

Returned by every cart endpoint (except `count`):

```json
{
  "data": {
    "id": 3,
    "items": [
      {
        "id": 12,
        "product_id": 5,
        "variant_id": 7,
        "name": "آيفون 15",
        "variant_label": "أحمر",
        "image": "https://talabye.com/storage/...",
        "quantity": 2,
        "unit_price": "139,860 ر.ي",
        "line_total": "279,720 ر.ي",
        "available": true
      }
    ],
    "total_quantity": 2,
    "subtotal": "279,720 ر.ي",
    "total": "279,720 ر.ي"
  }
}
```

Field notes:
- `variant_label` — human label of the chosen variant (e.g. `"أحمر"`), or `null`
  for a simple product.
- `available` — `false` if the product was deactivated/deleted after it was
  added. Such lines are **excluded at checkout** (see below).
- `total` currently equals `subtotal` (no shipping/discount applied yet).

---

## Cart endpoints

### 1. Get the cart

```
GET /api/catalog/cart
```

Returns the [cart object](#cart-object). Always succeeds for a logged-in user.

### 2. Cart item count

```
GET /api/catalog/cart/count
```

A lightweight badge counter — sum of all line quantities. Does **not** return
the full cart.

```json
{ "count": 2 }
```

### 3. Add to cart

```
POST /api/catalog/cart
```

| Param        | Type | Required | Description |
|--------------|------|----------|-------------|
| `product_id` | int  | yes      | Must be an **active** catalog product. |
| `variant_id` | int  | conditional | Required when the product `has_variants`; must belong to the product. Omit for simple products. |
| `quantity`   | int  | no       | Defaults to `1` (min `1`). Stacks onto an existing matching line. |

Returns the updated [cart object](#cart-object).

Errors:
- `422` `variant_id` — `"يجب اختيار خيار صالح لهذا المنتج."` (variant product
  with a missing/invalid variant).
- `422` `product_id` — product missing or inactive.

### 4. Update line quantity

```
PUT /api/catalog/cart/items/{item}
```

`{item}` is the cart **item** `id` (the `items[].id` from the cart object).

| Param      | Type | Required | Description |
|------------|------|----------|-------------|
| `quantity` | int  | yes      | `min:0`. Setting `0` **removes** the line. |

Returns the updated [cart object](#cart-object). An item not in the caller's
cart → `403`.

### 5. Remove a line

```
DELETE /api/catalog/cart/items/{item}
```

Removes the line. Returns the updated [cart object](#cart-object).
Not the caller's item → `403`.

### 6. Clear the cart

```
POST /api/catalog/cart/clear
```

Empties all lines. Returns the (now empty) [cart object](#cart-object).

---

## Order object

```json
{
  "data": {
    "id": 9,
    "code": "CAT-XXXXXX",
    "status": "pending",
    "status_label": "قيد الانتظار",
    "status_color": "warning",
    "subtotal": "279,720 ر.ي",
    "total": "279,720 ر.ي",
    "total_quantity": 2,
    "note": "اتصل قبل التوصيل",
    "address": {
      "address_one": "...",
      "phone": "...",
      "latitude": "...",
      "longitude": "..."
    },
    "items": [
      {
        "id": 21,
        "product_id": 5,
        "variant_id": 7,
        "name": "آيفون 15",
        "variant_label": "أحمر",
        "image": "https://talabye.com/storage/...",
        "unit_price": "139,860 ر.ي",
        "quantity": 2,
        "total": "279,720 ر.ي"
      }
    ],
    "items_count": 1,
    "created_at": "2026-06-21T19:00:00.000000Z"
  }
}
```

Field notes:
- `status` — machine value; `status_label` is Arabic; `status_color` is a UI
  token (`warning` / `primary` / `secondary` / `success` / `danger`).
  Lifecycle: `pending → confirmed → shipped → delivered`, with `cancelled` as a
  terminal off-ramp from any non-terminal state. Status transitions are
  **admin-only** — there is no customer endpoint to change status.
- `address` — a **snapshot** taken at checkout; it survives later edits or
  deletion of the user's address.
- `items` are present only on the **show** endpoint; the **list** endpoint omits
  them and returns `items_count` instead.
- Order line fields (`name`, `variant_label`, `unit_price`, `total`) are
  frozen snapshots — they don't change if the catalog changes afterwards.

---

## Order endpoints

### 7. List my orders

```
GET /api/catalog/orders
```

The caller's orders, newest first, **paginated** (length-aware, 10 per page).
Each order omits `items` and includes `items_count`. Standard Laravel
pagination wrapper:

```json
{
  "data": [ /* order objects without items */ ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 10, "total": 24, "...": "..." }
}
```

### 8. Get one order

```
GET /api/catalog/orders/{order}
```

Returns the full [order object](#order-object) **with `items`**. Another user's
order → `403`.

### 9. Checkout (place an order)

```
POST /api/catalog/orders/checkout
```

Turns the current cart into an order, then **empties the cart** (in one DB
transaction). Snapshots each line's price + the shipping address.

| Param        | Type   | Required | Description |
|--------------|--------|----------|-------------|
| `address_id` | int    | no       | A shipping address belonging to the user. Falls back to the user's **default** address if omitted. |
| `note`       | string | no       | Optional order note (`max:1000`). |

Only **active** products are carried into the order; unavailable lines are
dropped silently. On success → `201`:

```json
{
  "message": "تم إنشاء الطلب بنجاح",
  "order": { /* full order object with items */ }
}
```

Errors:
- `422` `{ "message": "السلة فارغة أو تحتوي على منتجات غير متوفرة." }` — no
  purchasable lines.
- `404` `{ "message": "لم يتم العثور على عنوان للشحن." }` — no chosen address and
  no default address on file.

---

## Typical app flows

**Add to cart → badge**
1. `POST /api/catalog/cart` `{ product_id, variant_id?, quantity? }`.
2. `GET /api/catalog/cart/count` → update the nav badge.

**Cart screen**
1. `GET /api/catalog/cart` → render lines, subtotal, total.
2. `PUT /api/catalog/cart/items/{id}` `{ quantity }` for +/- (send `0` to remove).
3. `DELETE /api/catalog/cart/items/{id}` to remove a line.

**Checkout**
1. `POST /api/catalog/orders/checkout` `{ address_id?, note? }` → `201` with the
   placed order; cart is now empty.

**Order history**
1. `GET /api/catalog/orders` → paginated list (page via `?page=`).
2. `GET /api/catalog/orders/{id}` → full detail with items.
