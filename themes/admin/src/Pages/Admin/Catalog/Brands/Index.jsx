import { useEffect, useRef, useState } from "react";
import { router, useForm } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
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
} from "@heroui/react";
import { Plus, Search, Pencil, Trash2, Award, ImagePlus, X } from "lucide-react";

const fieldInput =
    "h-11 px-4 text-sm shadow-none outline-none border border-zinc-200 dark:border-zinc-600";

const STATUSES = [
    { key: "", label: "الكل" },
    { key: "active", label: "مفعّلة" },
    { key: "inactive", label: "معطّلة" },
];

export default function Index({ brands, filters = {} }) {
    const rows = brands.data ?? [];

    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);

    // Local object-URL preview for a freshly picked file; the existing image
    // url (when editing) is kept in `currentImage`.
    const [preview, setPreview] = useState(null);
    const [currentImage, setCurrentImage] = useState(null);

    const form = useForm({
        name: "",
        slug: "",
        is_active: true,
        image: null,
        remove_image: false,
    });

    // ---- server-side search + status filter (debounced) --------------------
    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route("admin.catalog.brands.index"),
                {
                    search: search || undefined,
                    status: status || undefined,
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ["brands", "filters"],
                },
            );
        }, 350);
        return () => clearTimeout(t);
    }, [search, status]);

    // ---- create / edit ------------------------------------------------------
    const openCreate = () => {
        setEditing(null);
        form.clearErrors();
        form.setData({
            name: "",
            slug: "",
            is_active: true,
            image: null,
            remove_image: false,
        });
        setPreview(null);
        setCurrentImage(null);
        setModalOpen(true);
    };

    const openEdit = (brand) => {
        setEditing(brand);
        form.clearErrors();
        form.setData({
            name: brand.name,
            slug: brand.slug ?? "",
            is_active: brand.is_active,
            image: null,
            remove_image: false,
        });
        setPreview(null);
        setCurrentImage(brand.image ?? null);
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditing(null);
        form.reset();
        form.clearErrors();
        setPreview(null);
        setCurrentImage(null);
    };

    const pickImage = (file) => {
        if (!file) return;
        form.setData((d) => ({ ...d, image: file, remove_image: false }));
        setPreview(URL.createObjectURL(file));
    };

    const clearImage = () => {
        form.setData((d) => ({ ...d, image: null, remove_image: true }));
        setPreview(null);
        setCurrentImage(null);
    };

    const submit = (e) => {
        e?.preventDefault();
        const opts = {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: closeModal,
        };
        if (editing) {
            // Multipart PUT loses files in PHP — spoof the method over POST.
            // transform() returns undefined in @inertiajs/react v3, so it can't
            // be chained — set it, then post.
            form.transform((data) => ({ ...data, _method: "put" }));
            form.post(route("admin.catalog.brands.update", editing.id), opts);
        } else {
            form.transform((data) => data);
            form.post(route("admin.catalog.brands.store"), opts);
        }
    };

    const confirmDelete = () => {
        if (!deleteTarget) return;
        router.delete(route("admin.catalog.brands.destroy", deleteTarget.id), {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <AdminLayout title="العلامات التجارية">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                        {brands.total ?? rows.length} علامة تجارية
                    </h2>
                    <div className="flex items-center gap-3">
                        <div className="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-full p-1">
                            {STATUSES.map((s) => (
                                <button
                                    key={s.key}
                                    type="button"
                                    onClick={() => setStatus(s.key)}
                                    className={`text-xs font-bold px-3 h-9 rounded-full transition-colors ${
                                        status === s.key
                                            ? "bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white"
                                            : "text-zinc-500"
                                    }`}
                                >
                                    {s.label}
                                </button>
                            ))}
                        </div>
                        <div className="relative">
                            <Search className="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="بحث..."
                                className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-11 pr-10 pl-4 text-sm border-none outline-none w-56"
                            />
                        </div>
                        <Button
                            color="primary"
                            className="rounded-full font-black h-11"
                            startContent={<Plus className="w-4 h-4" />}
                            onPress={openCreate}
                        >
                            إضافة علامة تجارية
                        </Button>
                    </div>
                </div>

                {/* Table */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Brands Table">
                                <TableHeader className="text-right">
                                    <TableColumn className="rtl:text-right" isRowHeader>
                                        العلامة التجارية
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الرابط
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الحالة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الإجراءات
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={rows}
                                    renderEmptyState={() => (
                                        <div className="text-center py-16">
                                            <Award className="mx-auto w-14 h-14 text-zinc-300 mb-3" />
                                            <p className="font-black text-lg text-zinc-400">
                                                لا توجد علامات تجارية بعد.
                                            </p>
                                        </div>
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
                                                <span className="text-xs text-zinc-400 font-mono" dir="ltr">
                                                    {item.slug}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <Chip
                                                    size="sm"
                                                    variant="flat"
                                                    color={
                                                        item.is_active
                                                            ? "success"
                                                            : "default"
                                                    }
                                                >
                                                    {item.is_active
                                                        ? "مفعّلة"
                                                        : "معطّلة"}
                                                </Chip>
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
                {brands.links && brands.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {brands.links.map((link, i) => (
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
                                        only: ["brands", "filters"],
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
                                    <Award className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                {editing
                                    ? "تعديل العلامة التجارية"
                                    : "إضافة علامة تجارية"}
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
                                                اسم العلامة التجارية
                                            </Label>
                                            <Input
                                                placeholder="مثال: سامسونج"
                                                className={fieldInput}
                                            />
                                            {form.errors.name && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.name}
                                                </FieldError>
                                            )}
                                        </TextField>

                                        <TextField
                                            value={form.data.slug}
                                            onChange={(v) =>
                                                form.setData("slug", v)
                                            }
                                            isInvalid={!!form.errors.slug}
                                        >
                                            <Label className="text-xs font-bold text-zinc-500">
                                                الرابط (اختياري)
                                            </Label>
                                            <Input
                                                placeholder="يُولّد تلقائياً"
                                                dir="ltr"
                                                className={fieldInput}
                                            />
                                            {form.errors.slug && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.slug}
                                                </FieldError>
                                            )}
                                        </TextField>

                                        {/* Image */}
                                        <div>
                                            <Label className="text-xs font-bold text-zinc-500">
                                                صورة العلامة التجارية (اختياري)
                                            </Label>
                                            <div className="mt-1 flex items-center gap-4">
                                                {preview || currentImage ? (
                                                    <div className="relative w-20 h-20 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-600 shrink-0">
                                                        <img
                                                            src={
                                                                preview ||
                                                                currentImage
                                                            }
                                                            alt=""
                                                            className="w-full h-full object-cover"
                                                        />
                                                        <button
                                                            type="button"
                                                            onClick={clearImage}
                                                            className="absolute top-1 left-1 w-6 h-6 rounded-full bg-black/60 text-white flex items-center justify-center"
                                                        >
                                                            <X className="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <label className="w-20 h-20 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-600 flex flex-col items-center justify-center gap-1 text-zinc-400 cursor-pointer hover:border-blue-400 transition-colors shrink-0">
                                                        <ImagePlus className="w-5 h-5" />
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            className="hidden"
                                                            onChange={(e) =>
                                                                pickImage(
                                                                    e.target
                                                                        .files?.[0],
                                                                )
                                                            }
                                                        />
                                                    </label>
                                                )}
                                                {(preview || currentImage) && (
                                                    <label className="text-xs font-bold text-blue-600 cursor-pointer">
                                                        تغيير الصورة
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            className="hidden"
                                                            onChange={(e) =>
                                                                pickImage(
                                                                    e.target
                                                                        .files?.[0],
                                                                )
                                                            }
                                                        />
                                                    </label>
                                                )}
                                            </div>
                                            {form.errors.image && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.image}
                                                </FieldError>
                                            )}
                                        </div>

                                        <Checkbox
                                            isSelected={form.data.is_active}
                                            onChange={(v) =>
                                                form.setData("is_active", v)
                                            }
                                            className="flex items-center gap-3 cursor-pointer"
                                        >
                                            <CheckboxControl>
                                                <CheckboxIndicator />
                                            </CheckboxControl>
                                            <span className="text-sm font-bold">
                                                مفعّلة (ظاهرة في المتجر)
                                            </span>
                                        </Checkbox>
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
                                حذف العلامة التجارية
                            </Modal.Header>
                            <Modal.Body>
                                {deleteTarget?.products_count > 0 ? (
                                    <div className="p-2 space-y-3">
                                        <div className="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 text-sm text-amber-700 dark:text-amber-300 font-bold">
                                            هذه العلامة التجارية مرتبطة بـ{" "}
                                            {deleteTarget.products_count} منتج.
                                        </div>
                                        <p className="text-sm text-zinc-500">
                                            عند الحذف سيتم فك ارتباط هذه المنتجات
                                            بالعلامة التجارية (بدون حذفها). هل أنت
                                            متأكد من المتابعة؟
                                        </p>
                                    </div>
                                ) : (
                                    <p className="text-sm text-zinc-500 p-2">
                                        هل أنت متأكد من حذف العلامة التجارية{" "}
                                        <span className="font-bold text-zinc-700 dark:text-zinc-200">
                                            {deleteTarget?.name}
                                        </span>
                                        ؟ لن تتأثر المنتجات المرتبطة بها.
                                    </p>
                                )}
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
