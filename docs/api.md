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
