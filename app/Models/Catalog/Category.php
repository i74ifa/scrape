<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Catalog category node. Maintains a materialized `path`/`depth` so an entire
 * subtree can be fetched with one indexed LIKE query (no recursion).
 */
class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'name_translations', 'slug', 'image'];

    protected $casts = [
        'name_translations' => 'array',
        'depth' => 'integer',
    ];

    protected static function booted(): void
    {
        // Maintain the materialized path. `path`/`depth` are derived from the
        // parent — never mass-assigned — so they stay correct no matter who
        // creates the row.
        static::creating(fn (Category $category) => $category->setLineage());

        static::updating(function (Category $category) {
            if ($category->isDirty('parent_id')) {
                $category->setLineage();
            }
        });

        // When a node moves, its descendants' stored paths must move with it.
        static::updated(function (Category $category) {
            if ($category->wasChanged('path')) {
                $category->cascadeLineageToDescendants();
            }
        });
    }

    /**
     * Public URL for the stored image path. Null when empty; already-absolute
     * values pass through untouched; relative paths resolve via the disk.
     */
    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : Storage::url($this->image);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Catalog products directly attached to this category.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'category_catalog_product',
            'category_id',
            'catalog_product_id'
        );
    }

    /**
     * Store-wide attributes this category offers to its products' variant editor.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute');
    }

    /**
     * The LIKE prefix that matches every descendant of this node, e.g. a node
     * with path '/1/' and id 4 yields '/1/4/'.
     */
    public function descendantPrefix(): string
    {
        return $this->path.$this->id.'/';
    }

    /**
     * Ancestor ids encoded in this node's materialized path, root-first.
     * Path '/1/4/' → [1, 4]; a root node (path '/') → [].
     *
     * @return array<int>
     */
    public function ancestorIds(): array
    {
        return array_values(array_filter(array_map('intval', explode('/', (string) $this->path))));
    }

    /**
     * Build an id → breadcrumb-label map ("إلكترونيات › الهواتف › آيفون") for a
     * set of categories — ancestor names joined with the leaf's own name. All
     * ancestors are resolved in a single extra query.
     *
     * @param  iterable<self>  $categories
     * @return array<int, string>
     */
    public static function pathLabels(iterable $categories): array
    {
        $categories = collect($categories);

        $ancestorIds = $categories
            ->flatMap(fn (self $c) => $c->ancestorIds())
            ->unique()
            ->all();

        $names = empty($ancestorIds)
            ? collect()
            : static::whereIn('id', $ancestorIds)->pluck('name', 'id');

        return $categories->mapWithKeys(fn (self $c) => [
            $c->id => collect($c->ancestorIds())
                ->map(fn ($id) => $names->get($id))
                ->filter()
                ->push($c->name)
                ->implode(' › '),
        ])->all();
    }

    /**
     * Scope to this category plus its entire subtree.
     */
    public function scopeInSubtreeOf(Builder $query, Category $root): Builder
    {
        return $query->where(function (Builder $q) use ($root) {
            $q->whereKey($root->id)
                ->orWhere('path', 'like', $root->descendantPrefix().'%');
        });
    }

    /**
     * Ids of this category and all its descendants.
     *
     * @return array<int>
     */
    public function subtreeIds(): array
    {
        return static::query()
            ->where(fn (Builder $q) => $q
                ->whereKey($this->id)
                ->orWhere('path', 'like', $this->descendantPrefix().'%'))
            ->pluck('id')
            ->all();
    }

    /** Derive `path`/`depth` from the (current) parent. */
    protected function setLineage(): void
    {
        $parent = $this->parent_id
            ? static::find($this->parent_id)
            : null;

        $this->path = $parent ? $parent->descendantPrefix() : '/';
        $this->depth = $parent ? $parent->depth + 1 : 0;
    }

    /**
     * After this node's path changed, rewrite every descendant's stored path
     * and depth in a single UPDATE (prefix swap).
     */
    protected function cascadeLineageToDescendants(): void
    {
        $oldPrefix = $this->getOriginal('path').$this->id.'/';
        $newPrefix = $this->descendantPrefix();

        if ($oldPrefix === $newPrefix) {
            return;
        }

        $depthDelta = (int) $this->depth - (int) $this->getOriginal('depth');
        $pdo = DB::getPdo();

        static::query()
            ->where('path', 'like', $oldPrefix.'%')
            ->update([
                'path' => DB::raw('REPLACE(path, '.$pdo->quote($oldPrefix).', '.$pdo->quote($newPrefix).')'),
                'depth' => DB::raw('depth + '.$depthDelta),
            ]);
    }
}
