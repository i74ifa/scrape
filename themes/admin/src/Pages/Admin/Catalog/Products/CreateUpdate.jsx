import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout";
import ColorPickerInput from "@/Components/ColorPickerInput";
import {
    Card,
    CardContent,
    Button,
    Modal,
    Checkbox,
    CheckboxControl,
    CheckboxIndicator,
    Autocomplete,
    ListBox,
    SearchField,
    EmptyState,
    Spinner,
    Tag,
    TagGroup,
} from "@heroui/react";
import { useAsyncList } from "@/lib/useAsyncList";
import {
    ArrowRight,
    Save,
    Plus,
    Trash2,
    X,
    Boxes,
    Sparkles,
    FolderPlus,
    Tag as TagIcon,
    Award,
    ImagePlus,
    Star,
    Loader2,
} from "lucide-react";

// ---- styling tokens --------------------------------------------------------
const input =
    "w-full h-11 px-4 text-sm rounded-2xl border border-zinc-200 dark:border-zinc-600 bg-transparent shadow-none outline-none focus:border-blue-500";
const label = "block mb-1.5 text-xs font-bold text-zinc-500";
const card = "bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none";

// ---- helpers ----------------------------------------------------------------
const cartesian = (arrays) =>
    arrays.reduce((acc, arr) => acc.flatMap((a) => arr.map((b) => [...a, b])), [
        [],
    ]);

const comboKey = (values) => values.join(" / ");

export default function CreateUpdate({ product = null }) {
    const isEdit = !!product;

    const form = useForm({
        name: product?.name ?? "",
        slug: product?.slug ?? "",
        short_description: product?.short_description ?? "",
        description: product?.description ?? "",
        price: product?.price ?? "",
        sale_price: product?.sale_price ?? "",
        end_discount_date: product?.end_discount_date ?? "",
        weight: product?.weight ?? "",
        sku: product?.sku ?? "",
        promotion: product?.promotion ?? "",
        tags: product?.tags ?? "",
        brand_id: product?.brand_id ?? "",
        category_ids: product?.category_ids ?? [],
        images: product?.images ?? [],
        specifications: product?.specifications ?? [],
        is_active: product?.is_active ?? true,
        is_digital: product?.is_digital ?? false,
        has_variants: product?.has_variants ?? false,
        options: product?.options ?? [],
        variants: product?.variants ?? [],
    });

    const { data, setData, errors, processing } = form;

    const set = (k) => (e) => setData(k, e?.target ? e.target.value : e);

    // ---- selected categories (display labels alongside category_ids) --------
    const [selectedCats, setSelectedCats] = useState(product?.categories ?? []);

    // ---- selected brand ({id,name} for the picker label) --------------------
    const [selectedBrand, setSelectedBrand] = useState(product?.brand ?? null);

    const submit = (e) => {
        e.preventDefault();
        form.transform((d) => ({
            ...d,
            // Send only path + is_primary; the url is a display-only convenience.
            images: d.images.map((img) => ({
                path: img.path,
                is_primary: !!img.is_primary,
            })),
            options: d.has_variants ? d.options : [],
            variants: d.has_variants ? d.variants : [],
        }));
        const opts = {
            onSuccess: () =>
                router.visit(route("admin.catalog.products.index")),
        };
        if (isEdit) {
            form.put(route("admin.catalog.products.update", product.id), opts);
        } else {
            form.post(route("admin.catalog.products.store"), opts);
        }
    };

    // ---- specifications -----------------------------------------------------
    const addSpec = () =>
        setData("specifications", [
            ...data.specifications,
            { key: "", value: "" },
        ]);
    const setSpec = (i, field, v) =>
        setData(
            "specifications",
            data.specifications.map((s, idx) =>
                idx === i ? { ...s, [field]: v } : s,
            ),
        );
    const removeSpec = (i) =>
        setData(
            "specifications",
            data.specifications.filter((_, idx) => idx !== i),
        );

    return (
        <AdminLayout title={isEdit ? "تعديل المنتج" : "إضافة منتج"}>
            <form id="product-form" onSubmit={submit} className="flex flex-col gap-6 pb-28">
                {/* Header */}
                <div className="px-2 flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="flat"
                            isIconOnly
                            className="rounded-full"
                            onPress={() =>
                                router.visit(route("admin.catalog.products.index"))
                            }
                        >
                            <ArrowRight className="w-4 h-4" />
                        </Button>
                        <h2 className="text-xl font-black">
                            {isEdit ? "تعديل المنتج" : "إضافة منتج جديد"}
                        </h2>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main column */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        {/* Basic info */}
                        <Card className={card}>
                            <CardContent className="p-6 space-y-4">
                                <h3 className="font-black text-base">
                                    المعلومات الأساسية
                                </h3>
                                <div>
                                    <label className={label}>اسم المنتج *</label>
                                    <input
                                        className={input}
                                        value={data.name}
                                        onChange={set("name")}
                                        placeholder="مثال: آيفون 15 برو"
                                    />
                                    {errors.name && (
                                        <p className="text-xs text-red-500 font-bold mt-1 px-1">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className={label}>
                                            الرابط (اختياري)
                                        </label>
                                        <input
                                            className={input}
                                            value={data.slug}
                                            onChange={set("slug")}
                                            placeholder="يُولّد تلقائياً"
                                            dir="ltr"
                                        />
                                    </div>
                                    <div>
                                        <label className={label}>
                                            رمز SKU (اختياري)
                                        </label>
                                        <input
                                            className={input}
                                            value={data.sku}
                                            onChange={set("sku")}
                                            dir="ltr"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className={label}>وصف مختصر</label>
                                    <input
                                        className={input}
                                        value={data.short_description}
                                        onChange={set("short_description")}
                                        maxLength={255}
                                    />
                                </div>

                                <div>
                                    <label className={label}>الوصف الكامل</label>
                                    <textarea
                                        className={`${input} h-32 py-3 resize-none`}
                                        value={data.description}
                                        onChange={set("description")}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Images */}
                        <ImagesSection
                            images={data.images}
                            setImages={(imgs) => setData("images", imgs)}
                            card={card}
                        />

                        {/* Pricing */}
                        <Card className={card}>
                            <CardContent className="p-6 space-y-4">
                                <h3 className="font-black text-base">التسعير</h3>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className={label}>السعر *</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className={input}
                                            value={data.price}
                                            onChange={set("price")}
                                            dir="ltr"
                                        />
                                        {errors.price && (
                                            <p className="text-xs text-red-500 font-bold mt-1 px-1">
                                                {errors.price}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className={label}>
                                            سعر التخفيض
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className={input}
                                            value={data.sale_price}
                                            onChange={set("sale_price")}
                                            dir="ltr"
                                        />
                                        {errors.sale_price && (
                                            <p className="text-xs text-red-500 font-bold mt-1 px-1">
                                                {errors.sale_price}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className={label}>
                                            تاريخ انتهاء التخفيض
                                        </label>
                                        <input
                                            type="date"
                                            className={input}
                                            value={data.end_discount_date}
                                            onChange={set("end_discount_date")}
                                            dir="ltr"
                                        />
                                    </div>
                                    <div>
                                        <label className={label}>الوزن</label>
                                        <input
                                            className={input}
                                            value={data.weight}
                                            onChange={set("weight")}
                                            placeholder="مثال: 1.2kg"
                                            dir="ltr"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Variants */}
                        <VariantsSection
                            data={data}
                            setData={setData}
                            errors={errors}
                            input={input}
                            label={label}
                        />

                        {/* Specifications */}
                        <Card className={card}>
                            <CardContent className="p-6 space-y-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="font-black text-base">
                                        المواصفات
                                    </h3>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="flat"
                                        color="primary"
                                        startContent={
                                            <Plus className="w-3.5 h-3.5" />
                                        }
                                        onPress={addSpec}
                                    >
                                        إضافة مواصفة
                                    </Button>
                                </div>
                                {data.specifications.length === 0 ? (
                                    <p className="text-xs text-zinc-400">
                                        لا توجد مواصفات. أضف مثل: المعالج / A17.
                                    </p>
                                ) : (
                                    <div className="space-y-2">
                                        {data.specifications.map((s, i) => (
                                            <div
                                                key={i}
                                                className="flex items-center gap-2"
                                            >
                                                <input
                                                    className={input}
                                                    placeholder="الخاصية"
                                                    value={s.key}
                                                    onChange={(e) =>
                                                        setSpec(
                                                            i,
                                                            "key",
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <input
                                                    className={input}
                                                    placeholder="القيمة"
                                                    value={s.value}
                                                    onChange={(e) =>
                                                        setSpec(
                                                            i,
                                                            "value",
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    isIconOnly
                                                    className="text-rose-500 shrink-0"
                                                    onPress={() => removeSpec(i)}
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Side column */}
                    <div className="flex flex-col gap-6">
                        {/* Status */}
                        <Card className={card}>
                            <CardContent className="p-6 space-y-4">
                                <h3 className="font-black text-base">النشر</h3>
                                <ToggleRow
                                    label="مفعّل (ظاهر في المتجر)"
                                    value={data.is_active}
                                    onChange={(v) => setData("is_active", v)}
                                />
                                <ToggleRow
                                    label="منتج رقمي"
                                    value={data.is_digital}
                                    onChange={(v) => setData("is_digital", v)}
                                />
                            </CardContent>
                        </Card>

                        {/* Brand */}
                        <BrandSection
                            brandId={data.brand_id}
                            setBrandId={(id) => setData("brand_id", id)}
                            selectedBrand={selectedBrand}
                            setSelectedBrand={setSelectedBrand}
                        />

                        {/* Categories */}
                        <CategoriesSection
                            selectedCats={selectedCats}
                            setSelectedCats={setSelectedCats}
                            categoryIds={data.category_ids}
                            setCategoryIds={(ids) => setData("category_ids", ids)}
                            input={input}
                        />

                        {/* Tags / promotion */}
                        <Card className={card}>
                            <CardContent className="p-6 space-y-4">
                                <h3 className="font-black text-base">
                                    التسويق
                                </h3>
                                <div>
                                    <label className={label}>عرض ترويجي</label>
                                    <input
                                        className={input}
                                        value={data.promotion}
                                        onChange={set("promotion")}
                                        placeholder="مثال: خصم العيد"
                                    />
                                </div>
                                <div>
                                    <label className={label}>
                                        كلمات مفتاحية (مفصولة بفاصلة)
                                    </label>
                                    <input
                                        className={input}
                                        value={data.tags}
                                        onChange={set("tags")}
                                        placeholder="هاتف, آيفون, ابل"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

            </form>

            {/* Sticky save bar — portaled to <body> so `position: fixed` anchors
                to the viewport, not the AdminLayout glass panel (backdrop-filter
                ancestors become the containing block for fixed descendants). */}
            {createPortal(
                <div className="fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl border-t border-zinc-100 dark:border-zinc-800 px-4 py-3">
                    <div className="max-w-6xl mx-auto flex items-center justify-end gap-3">
                        <Button
                            variant="flat"
                            className="rounded-full font-bold px-6"
                            onPress={() =>
                                router.visit(route("admin.catalog.products.index"))
                            }
                        >
                            إلغاء
                        </Button>
                        <Button
                            type="submit"
                            form="product-form"
                            color="primary"
                            isLoading={processing}
                            startContent={<Save className="w-4 h-4" />}
                            className="rounded-full font-black px-8 h-11"
                        >
                            {isEdit ? "حفظ التعديلات" : "حفظ المنتج"}
                        </Button>
                    </div>
                </div>,
                document.body
            )}
        </AdminLayout>
    );
}

// ===========================================================================
// Toggle row (compound Checkbox)
// ===========================================================================
function ToggleRow({ label, value, onChange }) {
    return (
        <Checkbox
            isSelected={value}
            onChange={onChange}
            className="flex items-center gap-3 cursor-pointer"
        >
            <CheckboxControl>
                <CheckboxIndicator />
            </CheckboxControl>
            <span className="text-sm font-bold">{label}</span>
        </Checkbox>
    );
}

// ===========================================================================
// Categories — async multi-select + inline "new category" modal
// ===========================================================================
function CategoriesSection({
    selectedCats,
    setSelectedCats,
    categoryIds,
    setCategoryIds,
    input,
}) {
    const [newOpen, setNewOpen] = useState(false);

    // Async category search powered by the lookup endpoint. Empty query loads
    // root categories; typing searches by name (debounced by React Aria).
    const list = useAsyncList({
        async load({ filterText, signal }) {
            const { data } = await axios.get(
                route("admin.catalog.categories.lookup"),
                {
                    params: {
                        search: filterText || undefined,
                        roots: filterText ? undefined : 1,
                    },
                    signal,
                },
            );
            return { items: data };
        },
    });

    // Selection lives in two mirrored stores: categoryIds (ids → the form) and
    // selectedCats ({id,name,path} → tag labels).
    const onSelectionChange = (keys) => {
        const ids = [...(keys ?? [])].map(Number);
        const known = new Map(selectedCats.map((c) => [c.id, c]));
        list.items.forEach((it) =>
            known.set(it.id, { id: it.id, name: it.name, path: it.path }),
        );
        setCategoryIds(ids);
        setSelectedCats(
            ids.map(
                (id) => known.get(id) ?? { id, name: `#${id}`, path: `#${id}` },
            ),
        );
    };

    const onRemoveTags = (keys) => {
        const removed = new Set([...keys].map(Number));
        setCategoryIds(categoryIds.filter((id) => !removed.has(id)));
        setSelectedCats(selectedCats.filter((c) => !removed.has(c.id)));
    };

    return (
        <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
            <CardContent className="p-6 space-y-3">
                <div className="flex items-center justify-between">
                    <h3 className="font-black text-base">التصنيفات</h3>
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        className="text-emerald-600"
                        startContent={<FolderPlus className="w-3.5 h-3.5" />}
                        onPress={() => setNewOpen(true)}
                    >
                        جديد
                    </Button>
                </div>

                <Autocomplete
                    allowsEmptyCollection
                    className="w-full"
                    placeholder="اختر تصنيفاً..."
                    selectionMode="multiple"
                    value={categoryIds}
                    onChange={onSelectionChange}
                >
                    <Autocomplete.Trigger>
                        <Autocomplete.Value>
                            {({ defaultChildren, isPlaceholder, state }) => {
                                if (
                                    isPlaceholder ||
                                    state.selectedItems.length === 0
                                ) {
                                    return defaultChildren;
                                }

                                return (
                                    <TagGroup size="sm" onRemove={onRemoveTags}>
                                        <TagGroup.List>
                                            {selectedCats.map((c) => (
                                                <Tag key={c.id} id={c.id}>
                                                    {c.path ?? c.name}
                                                </Tag>
                                            ))}
                                        </TagGroup.List>
                                    </TagGroup>
                                );
                            }}
                        </Autocomplete.Value>
                        <Autocomplete.Indicator />
                    </Autocomplete.Trigger>
                    <Autocomplete.Popover>
                        <Autocomplete.Filter
                            inputValue={list.filterText}
                            onInputChange={list.setFilterText}
                        >
                            <SearchField
                                autoFocus
                                className="sticky top-0 z-10"
                                name="search"
                                variant="secondary"
                            >
                                <SearchField.Group>
                                    <SearchField.SearchIcon />
                                    <SearchField.Input placeholder="ابحث..." />
                                    {list.isLoading ? (
                                        <Spinner
                                            size="sm"
                                            className="absolute top-1/2 left-2 -translate-y-1/2"
                                        />
                                    ) : (
                                        <SearchField.ClearButton />
                                    )}
                                </SearchField.Group>
                            </SearchField>
                            <ListBox
                                className="max-h-56 overflow-y-auto"
                                items={list.items}
                                renderEmptyState={() => (
                                    <EmptyState>
                                        {list.isLoading
                                            ? "جاري البحث..."
                                            : "لا نتائج."}
                                    </EmptyState>
                                )}
                            >
                                {(item) => (
                                    <ListBox.Item
                                        id={item.id}
                                        textValue={item.path ?? item.name}
                                    >
                                        {item.path ?? item.name}
                                        <ListBox.ItemIndicator />
                                    </ListBox.Item>
                                )}
                            </ListBox>
                        </Autocomplete.Filter>
                    </Autocomplete.Popover>
                </Autocomplete>

                <p className="text-[11px] text-zinc-400">
                    صنّف المنتج لتسهيل تصفّحه في المتجر.
                </p>
            </CardContent>

            <NewCategoryModal
                isOpen={newOpen}
                onClose={() => setNewOpen(false)}
                onCreated={(cat) => {
                    setCategoryIds([...categoryIds, cat.id]);
                    setSelectedCats([
                        ...selectedCats,
                        { id: cat.id, name: cat.name, path: cat.path ?? cat.name },
                    ]);
                    setNewOpen(false);
                }}
            />
        </Card>
    );
}

// Inline new-category create. Reuses the categories.store route, then resolves
// the new id via the lookup endpoint (store returns an Inertia redirect).
function NewCategoryModal({ isOpen, onClose, onCreated }) {
    const [name, setName] = useState("");
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");

    const create = () => {
        if (!name.trim()) return;
        setSaving(true);
        setError("");
        router.post(
            route("admin.catalog.categories.store"),
            { name: name.trim() },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: async () => {
                    try {
                        const { data } = await axios.get(
                            route("admin.catalog.categories.lookup"),
                            { params: { search: name.trim() } },
                        );
                        const match =
                            data.find((c) => c.name === name.trim()) ?? data[0];
                        if (match) onCreated(match);
                        setName("");
                    } finally {
                        setSaving(false);
                    }
                },
                onError: () => {
                    setError("تعذّر إنشاء التصنيف");
                    setSaving(false);
                },
            },
        );
    };

    return (
        <Modal
            isOpen={isOpen}
            onOpenChange={(o) => !o && onClose()}
            placement="center"
            backdrop="blur"
        >
            <Modal.Backdrop>
                <Modal.Container>
                    <Modal.Dialog>
                        <Modal.Header className="font-black text-lg flex items-center gap-3">
                            <div className="w-9 h-9 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <FolderPlus className="w-4 h-4 text-emerald-600" />
                            </div>
                            تصنيف جديد
                        </Modal.Header>
                        <Modal.Body>
                            <div className="p-2">
                                <input
                                    autoFocus
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    onKeyDown={(e) =>
                                        e.key === "Enter" &&
                                        (e.preventDefault(), create())
                                    }
                                    placeholder="اسم التصنيف"
                                    className="w-full h-11 px-4 text-sm rounded-2xl border border-zinc-200 dark:border-zinc-600 outline-none"
                                />
                                {error && (
                                    <p className="text-xs text-red-500 font-bold mt-2">
                                        {error}
                                    </p>
                                )}
                            </div>
                        </Modal.Body>
                        <Modal.Footer>
                            <Button variant="flat" onPress={onClose}>
                                إلغاء
                            </Button>
                            <Button
                                color="primary"
                                isLoading={saving}
                                onPress={create}
                            >
                                إنشاء
                            </Button>
                        </Modal.Footer>
                    </Modal.Dialog>
                </Modal.Container>
            </Modal.Backdrop>
        </Modal>
    );
}

// ===========================================================================
// Images — gallery with immediate upload, delete, and set-primary.
// ===========================================================================
function ImagesSection({ images, setImages, card }) {
    const inputRef = useRef(null);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState("");

    const ensurePrimary = (list) =>
        list.length && !list.some((i) => i.is_primary)
            ? list.map((i, idx) => ({ ...i, is_primary: idx === 0 }))
            : list;

    const onFiles = async (files) => {
        const picked = Array.from(files || []).filter((f) =>
            f.type.startsWith("image/"),
        );
        if (!picked.length) return;
        setUploading(true);
        setError("");
        try {
            const uploaded = [];
            for (const file of picked) {
                const fd = new FormData();
                fd.append("image", file);
                const { data } = await axios.post(
                    route("admin.catalog.products.images.upload"),
                    fd,
                );
                uploaded.push({
                    path: data.path,
                    url: data.url,
                    is_primary: false,
                });
            }
            setImages(ensurePrimary([...images, ...uploaded]));
        } catch (e) {
            setError("تعذّر رفع الصورة. تأكد أنها صورة وأقل من 4 ميجابايت.");
        } finally {
            setUploading(false);
            if (inputRef.current) inputRef.current.value = "";
        }
    };

    const removeImage = (path) =>
        setImages(ensurePrimary(images.filter((i) => i.path !== path)));

    const setPrimary = (path) =>
        setImages(images.map((i) => ({ ...i, is_primary: i.path === path })));

    return (
        <Card className={card}>
            <CardContent className="p-6 space-y-4">
                <div className="flex items-center justify-between">
                    <h3 className="font-black text-base">صور المنتج</h3>
                    <Button
                        type="button"
                        size="sm"
                        variant="tertiary"
                        color="primary"
                        isLoading={uploading}
                        startContent={<ImagePlus className="w-3.5 h-3.5" />}
                        onPress={() => inputRef.current?.click()}
                    >
                        إضافة صور
                    </Button>
                    <input
                        ref={inputRef}
                        type="file"
                        accept="image/*"
                        multiple
                        className="hidden"
                        onChange={(e) => onFiles(e.target.files)}
                    />
                </div>

                {error && (
                    <p className="text-xs text-red-500 font-bold px-1">{error}</p>
                )}

                {images.length === 0 ? (
                    <button
                        type="button"
                        onClick={() => inputRef.current?.click()}
                        className="w-full rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 p-10 flex flex-col items-center gap-2 text-zinc-400 hover:border-blue-400 transition-colors"
                    >
                        {uploading ? (
                            <Loader2 className="w-7 h-7 animate-spin" />
                        ) : (
                            <ImagePlus className="w-7 h-7" />
                        )}
                        <span className="text-xs font-bold">
                            اضغط لإضافة صور المنتج
                        </span>
                    </button>
                ) : (
                    <div className="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        {images.map((img) => (
                            <div
                                key={img.path}
                                className={`group relative aspect-square rounded-2xl overflow-hidden border ${
                                    img.is_primary
                                        ? "border-blue-500 ring-2 ring-blue-500/30"
                                        : "border-zinc-200 dark:border-zinc-700"
                                }`}
                            >
                                <img
                                    src={img.url}
                                    alt=""
                                    className="w-full h-full object-cover"
                                />
                                {img.is_primary && (
                                    <span className="absolute top-1 right-1 text-[10px] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded-md">
                                        رئيسية
                                    </span>
                                )}
                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    {!img.is_primary && (
                                        <button
                                            type="button"
                                            onClick={() => setPrimary(img.path)}
                                            title="تعيين كصورة رئيسية"
                                            className="w-8 h-8 rounded-full bg-white/90 text-amber-500 flex items-center justify-center hover:bg-white"
                                        >
                                            <Star className="w-4 h-4" />
                                        </button>
                                    )}
                                    <button
                                        type="button"
                                        onClick={() => removeImage(img.path)}
                                        title="حذف"
                                        className="w-8 h-8 rounded-full bg-white/90 text-rose-500 flex items-center justify-center hover:bg-white"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
                <p className="text-[11px] text-zinc-400">
                    الصورة الرئيسية تظهر في قائمة المنتجات. مرّر فوق الصورة
                    لتعيينها رئيسية أو حذفها.
                </p>
            </CardContent>
        </Card>
    );
}

// ===========================================================================
// Brand — async single-select + inline "quick brand" create
// ===========================================================================
function BrandSection({ brandId, setBrandId, selectedBrand, setSelectedBrand }) {
    const [newOpen, setNewOpen] = useState(false);

    // Async brand search. Empty query loads the most recent brands.
    const list = useAsyncList({
        async load({ filterText, signal }) {
            const { data } = await axios.get(
                route("admin.catalog.brands.lookup"),
                {
                    params: { search: filterText || undefined },
                    signal,
                },
            );
            return { items: data };
        },
    });

    // Single-brand constraint on the multi-select primitive: keep only the
    // newly-added key (or clear when the current one is toggled off).
    const onSelectionChange = (keys) => {
        const ids = [...(keys ?? [])].map(Number);
        const next = ids.find((id) => id !== brandId) ?? null;
        if (next == null) {
            setBrandId(null);
            setSelectedBrand(null);
            return;
        }
        const match = list.items.find((b) => b.id === next);
        setBrandId(next);
        setSelectedBrand(
            match
                ? { id: match.id, name: match.name }
                : { id: next, name: `#${next}` },
        );
    };

    const onRemoveTags = () => {
        setBrandId(null);
        setSelectedBrand(null);
    };

    return (
        <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
            <CardContent className="p-6 space-y-3">
                <div className="flex items-center justify-between">
                    <h3 className="font-black text-base">العلامة التجارية</h3>
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        className="text-emerald-600"
                        startContent={<Award className="w-3.5 h-3.5" />}
                        onPress={() => setNewOpen(true)}
                    >
                        جديدة
                    </Button>
                </div>

                <Autocomplete
                    allowsEmptyCollection
                    className="w-full"
                    placeholder="اختر علامة تجارية..."
                    selectionMode="multiple"
                    value={brandId ? [brandId] : []}
                    onChange={onSelectionChange}
                >
                    <Autocomplete.Trigger>
                        <Autocomplete.Value>
                            {({ defaultChildren, isPlaceholder, state }) => {
                                if (
                                    isPlaceholder ||
                                    state.selectedItems.length === 0 ||
                                    !selectedBrand
                                ) {
                                    return defaultChildren;
                                }
                                return (
                                    <TagGroup size="sm" onRemove={onRemoveTags}>
                                        <TagGroup.List>
                                            <Tag
                                                key={selectedBrand.id}
                                                id={selectedBrand.id}
                                            >
                                                {selectedBrand.name}
                                            </Tag>
                                        </TagGroup.List>
                                    </TagGroup>
                                );
                            }}
                        </Autocomplete.Value>
                        <Autocomplete.Indicator />
                    </Autocomplete.Trigger>
                    <Autocomplete.Popover>
                        <Autocomplete.Filter
                            inputValue={list.filterText}
                            onInputChange={list.setFilterText}
                        >
                            <SearchField
                                autoFocus
                                className="sticky top-0 z-10"
                                name="search"
                                variant="secondary"
                            >
                                <SearchField.Group>
                                    <SearchField.SearchIcon />
                                    <SearchField.Input placeholder="ابحث..." />
                                    {list.isLoading ? (
                                        <Spinner
                                            size="sm"
                                            className="absolute top-1/2 left-2 -translate-y-1/2"
                                        />
                                    ) : (
                                        <SearchField.ClearButton />
                                    )}
                                </SearchField.Group>
                            </SearchField>
                            <ListBox
                                className="max-h-56 overflow-y-auto"
                                items={list.items}
                                renderEmptyState={() => (
                                    <EmptyState>
                                        {list.isLoading
                                            ? "جاري البحث..."
                                            : "لا نتائج."}
                                    </EmptyState>
                                )}
                            >
                                {(item) => (
                                    <ListBox.Item
                                        id={item.id}
                                        textValue={item.name}
                                    >
                                        {item.name}
                                        <ListBox.ItemIndicator />
                                    </ListBox.Item>
                                )}
                            </ListBox>
                        </Autocomplete.Filter>
                    </Autocomplete.Popover>
                </Autocomplete>

                <p className="text-[11px] text-zinc-400">
                    اربط المنتج بعلامته التجارية، أو أنشئ واحدة جديدة.
                </p>
            </CardContent>

            <NewBrandModal
                isOpen={newOpen}
                onClose={() => setNewOpen(false)}
                onCreated={(brand) => {
                    setBrandId(brand.id);
                    setSelectedBrand({ id: brand.id, name: brand.name });
                    setNewOpen(false);
                }}
            />
        </Card>
    );
}

// Inline quick-create. Reuses the brands.store route, then resolves the new id
// via the lookup endpoint (store returns an Inertia redirect, not JSON).
function NewBrandModal({ isOpen, onClose, onCreated }) {
    const [name, setName] = useState("");
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");

    const create = () => {
        if (!name.trim()) return;
        setSaving(true);
        setError("");
        router.post(
            route("admin.catalog.brands.store"),
            { name: name.trim() },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: async () => {
                    try {
                        const { data } = await axios.get(
                            route("admin.catalog.brands.lookup"),
                            { params: { search: name.trim() } },
                        );
                        const match =
                            data.find((b) => b.name === name.trim()) ?? data[0];
                        if (match) onCreated(match);
                        setName("");
                    } finally {
                        setSaving(false);
                    }
                },
                onError: () => {
                    setError("تعذّر إنشاء العلامة التجارية");
                    setSaving(false);
                },
            },
        );
    };

    return (
        <Modal
            isOpen={isOpen}
            onOpenChange={(o) => !o && onClose()}
            placement="center"
            backdrop="blur"
        >
            <Modal.Backdrop>
                <Modal.Container>
                    <Modal.Dialog>
                        <Modal.Header className="font-black text-lg flex items-center gap-3">
                            <div className="w-9 h-9 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <Award className="w-4 h-4 text-emerald-600" />
                            </div>
                            علامة تجارية جديدة
                        </Modal.Header>
                        <Modal.Body>
                            <div className="p-2">
                                <input
                                    autoFocus
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    onKeyDown={(e) =>
                                        e.key === "Enter" &&
                                        (e.preventDefault(), create())
                                    }
                                    placeholder="اسم العلامة التجارية"
                                    className="w-full h-11 px-4 text-sm rounded-2xl border border-zinc-200 dark:border-zinc-600 outline-none"
                                />
                                {error && (
                                    <p className="text-xs text-red-500 font-bold mt-2">
                                        {error}
                                    </p>
                                )}
                            </div>
                        </Modal.Body>
                        <Modal.Footer>
                            <Button variant="flat" onPress={onClose}>
                                إلغاء
                            </Button>
                            <Button
                                color="primary"
                                isLoading={saving}
                                onPress={create}
                            >
                                إنشاء
                            </Button>
                        </Modal.Footer>
                    </Modal.Dialog>
                </Modal.Container>
            </Modal.Backdrop>
        </Modal>
    );
}

// ===========================================================================
// Variants — has_variants toggle, option builder, category suggestions,
// auto-generated variant rows.
// ===========================================================================
function VariantsSection({ data, setData, errors, input, label }) {
    const cardCls = "bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none";

    // ---- category attribute suggestions ------------------------------------
    // Attributes are scoped to the product's categories. Loads when the variant
    // flow is enabled, refetches whenever the selected categories change.
    const [suggestions, setSuggestions] = useState([]);
    const categoryKey = data.category_ids.join(",");
    useEffect(() => {
        if (!data.has_variants || data.category_ids.length === 0) {
            setSuggestions([]);
            return;
        }
        let cancelled = false;
        axios
            .get(route("admin.catalog.products.option-suggestions"), {
                params: { category_ids: data.category_ids },
            })
            .then(({ data: res }) => !cancelled && setSuggestions(res))
            .catch(() => !cancelled && setSuggestions([]));
        return () => {
            cancelled = true;
        };
    }, [data.has_variants, categoryKey]);

    // ---- option builder -----------------------------------------------------
    // `values` may be plain strings or library {value,color} rows.
    const addOption = (name = "", values = [], isColor = false) => {
        if (data.options.some((o) => o.name === name && name)) return;
        setData("options", [
            ...data.options,
            {
                name,
                is_color: isColor,
                values: values.length
                    ? values.map((v) =>
                          typeof v === "string"
                              ? { value: v, color: "" }
                              : { value: v.value, color: v.color || "" },
                      )
                    : [{ value: "", color: "" }],
            },
        ]);
    };
    const setOptionName = (i, v) =>
        setData(
            "options",
            data.options.map((o, idx) => (idx === i ? { ...o, name: v } : o)),
        );
    const setOptionIsColor = (i, v) =>
        setData(
            "options",
            data.options.map((o, idx) => (idx === i ? { ...o, is_color: v } : o)),
        );
    const removeOption = (i) =>
        setData(
            "options",
            data.options.filter((_, idx) => idx !== i),
        );
    const addValue = (oi) =>
        setData(
            "options",
            data.options.map((o, idx) =>
                idx === oi
                    ? { ...o, values: [...o.values, { value: "", color: "" }] }
                    : o,
            ),
        );
    const setValue = (oi, vi, field, v) =>
        setData(
            "options",
            data.options.map((o, idx) =>
                idx === oi
                    ? {
                          ...o,
                          values: o.values.map((val, vIdx) =>
                              vIdx === vi ? { ...val, [field]: v } : val,
                          ),
                      }
                    : o,
            ),
        );
    const removeValue = (oi, vi) =>
        setData(
            "options",
            data.options.map((o, idx) =>
                idx === oi
                    ? {
                          ...o,
                          values: o.values.filter((_, vIdx) => vIdx !== vi),
                      }
                    : o,
            ),
        );

    // ---- generate variant combinations (preserve existing edits) -----------
    const regenerate = () => {
        const opts = data.options
            .map((o) => ({
                name: o.name.trim(),
                values: o.values.map((v) => v.value.trim()).filter(Boolean),
            }))
            .filter((o) => o.name && o.values.length);

        if (!opts.length) {
            setData("variants", []);
            return;
        }

        const combos = cartesian(opts.map((o) => o.values));
        const existing = Object.fromEntries(
            data.variants.map((v) => [comboKey(v.option_values), v]),
        );

        setData(
            "variants",
            combos.map((combo) => {
                const prev = existing[comboKey(combo)];
                return (
                    prev ?? {
                        option_values: combo,
                        sku: "",
                        price: data.price || "",
                        sale_price: "",
                        is_active: true,
                    }
                );
            }),
        );
    };

    // Auto-generate variant rows whenever the option VALUES change. The option
    // name is excluded so renaming never rebuilds the table. regenerate()
    // preserves existing rows by combo key. The first run is skipped so loading
    // an edit page never fabricates combos the merchant didn't save.
    const optionsKey = JSON.stringify(
        data.options.map((o) =>
            o.values.map((v) => v.value.trim()).filter(Boolean),
        ),
    );
    const skipFirstRegen = useRef(true);
    useEffect(() => {
        if (skipFirstRegen.current) {
            skipFirstRegen.current = false;
            return;
        }
        if (!data.has_variants) return;
        // Debounce so the variant table doesn't churn on every keystroke.
        const t = setTimeout(regenerate, 1000);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [optionsKey, data.has_variants]);

    const setVariant = (i, field, v) =>
        setData(
            "variants",
            data.variants.map((vr, idx) =>
                idx === i ? { ...vr, [field]: v } : vr,
            ),
        );

    return (
        <Card className={cardCls}>
            <CardContent className="p-6 space-y-4">
                <div className="flex items-center justify-between">
                    <h3 className="font-black text-base flex items-center gap-2">
                        <Boxes className="w-4 h-4 text-blue-600" />
                        المتغيّرات
                    </h3>
                    <Checkbox
                        isSelected={data.has_variants}
                        onChange={(v) => setData("has_variants", v)}
                        className="flex items-center gap-2 cursor-pointer"
                    >
                        <CheckboxControl>
                            <CheckboxIndicator />
                        </CheckboxControl>
                        <span className="text-sm font-bold">
                            هذا المنتج له خيارات متعددة
                        </span>
                    </Checkbox>
                </div>

                {data.has_variants ? (
                    <div className="space-y-5">
                        {/* category-scoped attribute suggestions */}
                        {data.category_ids.length === 0 && (
                            <p className="text-xs text-zinc-400">
                                اختر تصنيفاً للمنتج لعرض الخصائص المقترحة لهذا
                                التصنيف.
                            </p>
                        )}
                        {suggestions.length > 0 && (
                            <div className="bg-amber-50 dark:bg-amber-900/10 rounded-2xl p-4 border border-amber-100 dark:border-amber-900/30">
                                <p className="text-xs font-bold text-amber-700 dark:text-amber-400 mb-2 flex items-center gap-1.5">
                                    <Sparkles className="w-3.5 h-3.5" />
                                    خصائص مقترحة لتصنيفات المنتج
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    {suggestions.map((s) => (
                                        <button
                                            key={s.name}
                                            type="button"
                                            onClick={() =>
                                                addOption(
                                                    s.name,
                                                    s.values,
                                                    !!s.is_color,
                                                )
                                            }
                                            className="text-xs font-bold px-3 py-1.5 rounded-xl bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-900/40 hover:border-amber-400"
                                        >
                                            + {s.name}
                                            {s.values.length > 0 && (
                                                <span className="text-zinc-400 font-normal">
                                                    {" "}
                                                    ({s.values
                                                        .slice(0, 3)
                                                        .map((v) => v.value)
                                                        .join("، ")}
                                                    {s.values.length > 3
                                                        ? "…"
                                                        : ""}
                                                    )
                                                </span>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* options builder */}
                        <div className="space-y-3">
                            {data.options.map((opt, oi) => (
                                <div
                                    key={oi}
                                    className="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-3"
                                >
                                    <div className="flex items-center gap-2">
                                        <TagIcon className="w-4 h-4 text-zinc-400 shrink-0" />
                                        <input
                                            className={input}
                                            placeholder="اسم الخيار (مثال: اللون)"
                                            value={opt.name}
                                            onChange={(e) =>
                                                setOptionName(
                                                    oi,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            isIconOnly
                                            className="text-rose-500 shrink-0"
                                            onPress={() => removeOption(oi)}
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </Button>
                                    </div>
                                    <div className="ps-6">
                                        <Checkbox
                                            isSelected={!!opt.is_color}
                                            onChange={(v) =>
                                                setOptionIsColor(oi, v)
                                            }
                                            className="flex items-center gap-2 cursor-pointer w-fit"
                                        >
                                            <CheckboxControl>
                                                <CheckboxIndicator />
                                            </CheckboxControl>
                                            <span className="text-xs font-bold text-zinc-500">
                                                هذا الخيار لون (إظهار عيّنات لونية)
                                            </span>
                                        </Checkbox>
                                    </div>
                                    <div className="flex flex-wrap gap-2 ps-6">
                                        {opt.values.map((val, vi) => (
                                            <div
                                                key={vi}
                                                className="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl px-2 py-1"
                                            >
                                                {opt.is_color && (
                                                    <ColorPickerInput
                                                        value={val.color}
                                                        onChange={(hex) =>
                                                            setValue(
                                                                oi,
                                                                vi,
                                                                "color",
                                                                hex,
                                                            )
                                                        }
                                                    />
                                                )}
                                                <input
                                                    className="bg-transparent text-sm w-24 outline-none"
                                                    placeholder="القيمة"
                                                    value={val.value}
                                                    onChange={(e) =>
                                                        setValue(
                                                            oi,
                                                            vi,
                                                            "value",
                                                            e.target.value,
                                                        )
                                                    }
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        removeValue(oi, vi)
                                                    }
                                                    className="text-zinc-400 hover:text-rose-500"
                                                >
                                                    <X className="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        ))}
                                        <button
                                            type="button"
                                            onClick={() => addValue(oi)}
                                            className="text-xs font-bold px-3 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200"
                                        >
                                            + قيمة
                                        </button>
                                    </div>
                                </div>
                            ))}
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="tertiary"
                                    startContent={<Plus className="w-3.5 h-3.5" />}
                                    onPress={() => addOption()}
                                >
                                    إضافة خيار
                                </Button>
                            </div>
                        </div>

                        {/* variant rows */}
                        {data.variants.length > 0 && (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="text-xs text-zinc-400 text-right">
                                            <th className="font-bold py-2 px-2">
                                                المتغيّر
                                            </th>
                                            <th className="font-bold py-2 px-2">
                                                SKU
                                            </th>
                                            <th className="font-bold py-2 px-2">
                                                السعر
                                            </th>
                                            <th className="font-bold py-2 px-2">
                                                مفعّل
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {data.variants.map((vr, i) => (
                                            <tr
                                                key={i}
                                                className="border-t border-zinc-100 dark:border-zinc-800"
                                            >
                                                <td className="py-2 px-2">
                                                    <div className="flex flex-wrap gap-1">
                                                        {vr.option_values.map(
                                                            (ov, k) => (
                                                                <span
                                                                    key={k}
                                                                    className="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600"
                                                                >
                                                                    {ov}
                                                                </span>
                                                            ),
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="py-2 px-2">
                                                    <input
                                                        className="w-24 h-9 px-2 rounded-xl border border-zinc-200 dark:border-zinc-600 bg-transparent text-xs outline-none"
                                                        dir="ltr"
                                                        value={vr.sku ?? ""}
                                                        onChange={(e) =>
                                                            setVariant(
                                                                i,
                                                                "sku",
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </td>
                                                <td className="py-2 px-2">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        className="w-24 h-9 px-2 rounded-xl border border-zinc-200 dark:border-zinc-600 bg-transparent text-xs outline-none"
                                                        dir="ltr"
                                                        value={vr.price}
                                                        onChange={(e) =>
                                                            setVariant(
                                                                i,
                                                                "price",
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </td>
                                                <td className="py-2 px-2">
                                                    <Checkbox
                                                        isSelected={
                                                            vr.is_active
                                                        }
                                                        onChange={(v) =>
                                                            setVariant(
                                                                i,
                                                                "is_active",
                                                                v,
                                                            )
                                                        }
                                                    >
                                                        <CheckboxControl>
                                                            <CheckboxIndicator />
                                                        </CheckboxControl>
                                                    </Checkbox>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                ) : null}
            </CardContent>
        </Card>
    );
}
