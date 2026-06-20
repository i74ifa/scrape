<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreAttributeRequest;
use App\Http\Requests\Catalog\UpdateAttributeRequest;
use App\Models\Catalog\Attribute;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Manages the store-wide attribute library (the variant axes "اللون", "التخزين"
 * and their values) and the categories each attribute is offered to.
 */
class AttributeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Catalog/Attributes/Index', [
            'attributes' => Attribute::with([
                'values' => fn ($q) => $q->orderBy('position'),
                'categories:id,name',
            ])
                ->orderBy('position')
                ->orderBy('name')
                ->paginate(20)
                ->through(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'position' => $a->position,
                    'is_color' => $a->is_color,
                    'values' => $a->values->map(fn ($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'color' => $v->color,
                    ])->values(),
                    'categories' => $a->categories->map(fn ($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                    ])->values(),
                ])
                ->withQueryString(),
        ]);
    }

    public function store(StoreAttributeRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $attribute = Attribute::create([
                'name' => $data['name'],
                'position' => $data['position'] ?? 0,
                'is_color' => $data['is_color'] ?? false,
            ]);

            $this->syncValues($attribute, $data['values']);
            $attribute->categories()->sync($data['category_ids'] ?? []);
        });

        return back()->with('success', 'تمت إضافة الخاصية بنجاح');
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $data = $request->validated();

        DB::transaction(function () use ($attribute, $data) {
            $attribute->update([
                'name' => $data['name'],
                'position' => $data['position'] ?? 0,
                'is_color' => $data['is_color'] ?? false,
            ]);

            $this->syncValues($attribute, $data['values']);
            $attribute->categories()->sync($data['category_ids'] ?? []);
        });

        return back()->with('success', 'تم تحديث الخاصية بنجاح');
    }

    public function destroy(Attribute $attribute)
    {
        // values + category links + variant links cascade (FK cascadeOnDelete).
        $attribute->delete();

        return back()->with('success', 'تم حذف الخاصية بنجاح');
    }

    /**
     * Reconcile an attribute's value rows against the payload. Rows carrying an
     * `id` that belongs to this attribute are updated in place (rename/recolor)
     * so variants referencing them survive; rows without a matching id are
     * created; existing rows absent from the payload are deleted. Ids that don't
     * belong to this attribute are ignored — never trusted from the request.
     *
     * @param  array<int, array{id?: int|null, value: string, color?: string|null}>  $values
     */
    private function syncValues(Attribute $attribute, array $values): void
    {
        $ownIds = $attribute->values()->pluck('id')->all();
        $keepIds = [];

        foreach (array_values($values) as $i => $row) {
            $value = trim($row['value']);
            $color = ($row['color'] ?? '') !== '' ? $row['color'] : null;
            $id = isset($row['id']) && in_array((int) $row['id'], $ownIds, true)
                ? (int) $row['id']
                : null;

            // Match by id (rename in place) or, lacking a trusted id, by the
            // value string — so re-adding an existing value reuses its row
            // instead of colliding on the (attribute_id, value) unique index.
            $av = $id
                ? $attribute->values()->whereKey($id)->first()
                : $attribute->values()->firstOrNew(['value' => $value]);

            $av->value = $value;
            $av->color = $color;
            $av->position = $i;
            $av->save();

            $keepIds[] = $av->id;
        }

        $attribute->values()->whereNotIn('id', $keepIds ?: [0])->delete();
    }
}
