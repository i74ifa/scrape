import { useState } from "react";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout";
import ColorPickerInput from "@/Components/ColorPickerInput";
import {
    Table,
    TableContent,
    TableHeader,
    TableColumn,
    TableBody,
    TableRow,
    TableCell,
    Chip,
    Card,
    CardContent,
    Button,
    Modal,
    TextField,
    Label,
    Input,
    FieldError,
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
import { Plus, Tags, Pencil, Trash2, X } from "lucide-react";

const fieldInput =
    "h-11 px-4 text-sm shadow-none outline-none border border-zinc-200 dark:border-zinc-600";

export default function Index({ attributes }) {
    const rows = attributes.data ?? [];

    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);

    // category_ids mirrors selectedCats ({id,name}) for tag labels.
    const [selectedCats, setSelectedCats] = useState([]);

    const form = useForm({
        name: "",
        position: 0,
        is_color: false,
        values: [{ id: null, value: "", color: "" }],
        category_ids: [],
    });

    const openCreate = () => {
        setEditing(null);
        form.clearErrors();
        form.setData({
            name: "",
            position: 0,
            is_color: false,
            values: [{ id: null, value: "", color: "" }],
            category_ids: [],
        });
        setSelectedCats([]);
        setModalOpen(true);
    };

    const openEdit = (attr) => {
        setEditing(attr);
        form.clearErrors();
        form.setData({
            name: attr.name,
            position: attr.position ?? 0,
            is_color: !!attr.is_color,
            values: attr.values.length
                ? attr.values.map((v) => ({
                      id: v.id,
                      value: v.value,
                      color: v.color ?? "",
                  }))
                : [{ id: null, value: "", color: "" }],
            category_ids: attr.categories.map((c) => c.id),
        });
        setSelectedCats(attr.categories.map((c) => ({ id: c.id, name: c.name })));
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditing(null);
        form.reset();
        form.clearErrors();
        setSelectedCats([]);
    };

    // ---- value rows ---------------------------------------------------------
    const addValue = () =>
        form.setData("values", [
            ...form.data.values,
            { id: null, value: "", color: "" },
        ]);
    const setValue = (i, field, v) =>
        form.setData(
            "values",
            form.data.values.map((row, idx) =>
                idx === i ? { ...row, [field]: v } : row,
            ),
        );
    const removeValue = (i) =>
        form.setData(
            "values",
            form.data.values.filter((_, idx) => idx !== i),
        );

    const submit = (e) => {
        e?.preventDefault();
        form.transform((d) => ({
            ...d,
            values: d.values.filter((v) => v.value.trim() !== ""),
        }));
        const opts = { preserveScroll: true, onSuccess: closeModal };
        if (editing) {
            form.put(route("admin.catalog.attributes.update", editing.id), opts);
        } else {
            form.post(route("admin.catalog.attributes.store"), opts);
        }
    };

    const confirmDelete = () => {
        if (!deleteTarget) return;
        router.delete(route("admin.catalog.attributes.destroy", deleteTarget.id), {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <AdminLayout title="الخصائص">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                        {attributes.total ?? rows.length} خاصية
                    </h2>
                    <Button
                        color="primary"
                        className="rounded-full font-black h-11"
                        startContent={<Plus className="w-4 h-4" />}
                        onPress={openCreate}
                    >
                        إضافة خاصية
                    </Button>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Attributes Table">
                                <TableHeader className="text-right">
                                    <TableColumn className="rtl:text-right" isRowHeader>
                                        الخاصية
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        القيم
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        التصنيفات
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الإجراءات
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={rows}
                                    renderEmptyState={() => (
                                        <p className="text-center py-10 text-zinc-400">
                                            لا توجد خصائص. أضف مثل: اللون / المقاس.
                                        </p>
                                    )}
                                >
                                    {(item) => (
                                        <TableRow
                                            id={item.id}
                                            className="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <TableCell>
                                                <p className="font-semibold text-sm">
                                                    {item.name}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1">
                                                    {item.values
                                                        .slice(0, 6)
                                                        .map((v) => (
                                                            <span
                                                                key={v.id}
                                                                className="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800"
                                                            >
                                                                {v.color && (
                                                                    <span
                                                                        className="w-2.5 h-2.5 rounded-full border border-zinc-300"
                                                                        style={{
                                                                            background:
                                                                                v.color,
                                                                        }}
                                                                    />
                                                                )}
                                                                {v.value}
                                                            </span>
                                                        ))}
                                                    {item.values.length > 6 && (
                                                        <span className="text-[11px] text-zinc-400">
                                                            +
                                                            {item.values.length -
                                                                6}
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {item.categories.length === 0 ? (
                                                    <span className="text-xs text-zinc-400">
                                                        غير مرتبطة
                                                    </span>
                                                ) : (
                                                    <div className="flex flex-wrap gap-1">
                                                        {item.categories.map(
                                                            (c) => (
                                                                <Chip
                                                                    key={c.id}
                                                                    size="sm"
                                                                    variant="flat"
                                                                    color="primary"
                                                                >
                                                                    {c.name}
                                                                </Chip>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 justify-end">
                                                    <Button
                                                        size="sm"
                                                        variant="flat"
                                                        color="primary"
                                                        startContent={
                                                            <Pencil className="w-3.5 h-3.5" />
                                                        }
                                                        onPress={() =>
                                                            openEdit(item)
                                                        }
                                                    >
                                                        تعديل
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        className="text-rose-500"
                                                        startContent={
                                                            <Trash2 className="w-3.5 h-3.5" />
                                                        }
                                                        onPress={() =>
                                                            setDeleteTarget(item)
                                                        }
                                                    >
                                                        حذف
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </TableContent>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {attributes.links && attributes.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {attributes.links.map((link, i) => (
                            <Button
                                key={i}
                                size="sm"
                                variant={link.active ? "solid" : "flat"}
                                color={link.active ? "primary" : "default"}
                                isDisabled={!link.url}
                                className="rounded-full font-bold min-w-9"
                                onPress={() =>
                                    link.url &&
                                    router.visit(link.url, {
                                        preserveState: true,
                                        preserveScroll: true,
                                        only: ["attributes"],
                                    })
                                }
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>

            {/* Create / Edit modal */}
            <Modal
                isOpen={modalOpen}
                onOpenChange={(open) => !open && closeModal()}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog>
                            <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <Modal.Header className="font-black text-xl flex items-center gap-3">
                                <div className="w-9 h-9 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <Tags className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                {editing ? "تعديل الخاصية" : "إضافة خاصية جديدة"}
                            </Modal.Header>
                            <form onSubmit={submit}>
                                <Modal.Body>
                                    <div className="space-y-5 p-2">
                                        <TextField
                                            value={form.data.name}
                                            onChange={(v) =>
                                                form.setData("name", v)
                                            }
                                            isInvalid={!!form.errors.name}
                                            isRequired
                                        >
                                            <Label className="text-xs font-bold text-zinc-500">
                                                اسم الخاصية
                                            </Label>
                                            <Input
                                                placeholder="مثال: اللون"
                                                className={fieldInput}
                                            />
                                            {form.errors.name && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.name}
                                                </FieldError>
                                            )}
                                        </TextField>

                                        <Checkbox
                                            isSelected={form.data.is_color}
                                            onChange={(v) =>
                                                form.setData("is_color", v)
                                            }
                                            className="flex items-center gap-3 cursor-pointer w-fit"
                                        >
                                            <CheckboxControl>
                                                <CheckboxIndicator />
                                            </CheckboxControl>
                                            <span className="text-sm font-bold">
                                                خاصية لون (إظهار عيّنات لونية في
                                                المتجر)
                                            </span>
                                        </Checkbox>

                                        {/* Values editor */}
                                        <div>
                                            <Label className="text-xs font-bold text-zinc-500">
                                                القيم
                                            </Label>
                                            <div className="mt-1.5 flex flex-wrap gap-2">
                                                {form.data.values.map(
                                                    (val, i) => (
                                                        <div
                                                            key={i}
                                                            className="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl px-2 py-1"
                                                        >
                                                            {form.data.is_color && (
                                                                <ColorPickerInput
                                                                    value={
                                                                        val.color
                                                                    }
                                                                    onChange={(
                                                                        hex,
                                                                    ) =>
                                                                        setValue(
                                                                            i,
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
                                                                        i,
                                                                        "value",
                                                                        e.target
                                                                            .value,
                                                                    )
                                                                }
                                                            />
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    removeValue(i)
                                                                }
                                                                className="text-zinc-400 hover:text-rose-500"
                                                            >
                                                                <X className="w-3.5 h-3.5" />
                                                            </button>
                                                        </div>
                                                    ),
                                                )}
                                                <button
                                                    type="button"
                                                    onClick={addValue}
                                                    className="text-xs font-bold px-3 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200"
                                                >
                                                    + قيمة
                                                </button>
                                            </div>
                                            {form.errors.values && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.values}
                                                </FieldError>
                                            )}
                                        </div>

                                        {/* Category links */}
                                        <div>
                                            <Label className="text-xs font-bold text-zinc-500">
                                                التصنيفات التي تظهر فيها
                                            </Label>
                                            <CategoryPicker
                                                categoryIds={form.data.category_ids}
                                                setCategoryIds={(ids) =>
                                                    form.setData(
                                                        "category_ids",
                                                        ids,
                                                    )
                                                }
                                                selectedCats={selectedCats}
                                                setSelectedCats={setSelectedCats}
                                            />
                                            <p className="text-[11px] text-zinc-400 mt-1.5">
                                                تُقترح هذه الخاصية فقط عند إضافة
                                                منتج بمتغيّرات ضمن هذه التصنيفات.
                                            </p>
                                        </div>
                                    </div>
                                </Modal.Body>
                                <Modal.Footer>
                                    <Button
                                        type="button"
                                        variant="flat"
                                        onPress={closeModal}
                                    >
                                        إلغاء
                                    </Button>
                                    <Button
                                        type="submit"
                                        color="primary"
                                        isLoading={form.processing}
                                    >
                                        {editing ? "حفظ التعديلات" : "حفظ"}
                                    </Button>
                                </Modal.Footer>
                            </form>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>

            {/* Delete confirmation */}
            <Modal
                isOpen={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog>
                            <Modal.Header className="font-black text-xl flex items-center gap-3">
                                <div className="w-9 h-9 rounded-2xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                                    <Trash2 className="w-4 h-4 text-rose-600 dark:text-rose-400" />
                                </div>
                                حذف الخاصية
                            </Modal.Header>
                            <Modal.Body>
                                <p className="text-sm text-zinc-500 p-2">
                                    هل أنت متأكد من حذف الخاصية{" "}
                                    <span className="font-bold text-zinc-700 dark:text-zinc-200">
                                        {deleteTarget?.name}
                                    </span>
                                    ؟ سيتم فك ارتباطها بالمنتجات والتصنيفات.
                                </p>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button
                                    variant="flat"
                                    onPress={() => setDeleteTarget(null)}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    className="rounded-full font-bold px-6 bg-red-600 text-white"
                                    onPress={confirmDelete}
                                >
                                    تأكيد الحذف
                                </Button>
                            </Modal.Footer>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>
        </AdminLayout>
    );
}

// ===========================================================================
// Async category multi-select (reuses admin.catalog.categories.lookup)
// ===========================================================================
function CategoryPicker({
    categoryIds,
    setCategoryIds,
    selectedCats,
    setSelectedCats,
}) {
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

    const onSelectionChange = (keys) => {
        const ids = [...(keys ?? [])].map(Number);
        const names = new Map(selectedCats.map((c) => [c.id, c.name]));
        list.items.forEach((it) => names.set(it.id, it.name));
        setCategoryIds(ids);
        setSelectedCats(ids.map((id) => ({ id, name: names.get(id) ?? `#${id}` })));
    };

    const onRemoveTags = (keys) => {
        const removed = new Set([...keys].map(Number));
        setCategoryIds(categoryIds.filter((id) => !removed.has(id)));
        setSelectedCats(selectedCats.filter((c) => !removed.has(c.id)));
    };

    return (
        <Autocomplete
            allowsEmptyCollection
            className="w-full mt-1.5"
            placeholder="اختر تصنيفاً..."
            selectionMode="multiple"
            value={categoryIds}
            onChange={onSelectionChange}
        >
            <Autocomplete.Trigger>
                <Autocomplete.Value>
                    {({ defaultChildren, isPlaceholder, state }) => {
                        if (isPlaceholder || state.selectedItems.length === 0) {
                            return defaultChildren;
                        }
                        return (
                            <TagGroup size="sm" onRemove={onRemoveTags}>
                                <TagGroup.List>
                                    {selectedCats.map((c) => (
                                        <Tag key={c.id} id={c.id}>
                                            {c.name}
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
                                {list.isLoading ? "جاري البحث..." : "لا نتائج."}
                            </EmptyState>
                        )}
                    >
                        {(item) => (
                            <ListBox.Item id={item.id} textValue={item.name}>
                                {item.name}
                                <ListBox.ItemIndicator />
                            </ListBox.Item>
                        )}
                    </ListBox>
                </Autocomplete.Filter>
            </Autocomplete.Popover>
        </Autocomplete>
    );
}
