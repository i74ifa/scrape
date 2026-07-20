import { useEffect, useRef, useState } from "react";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
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
} from "@heroui/react";
import {
    Plus,
    FolderTree,
    List,
    Pencil,
    Trash2,
    Search,
    FolderPlus,
    ChevronDown,
    ChevronLeft,
    Loader2,
    X,
    ImagePlus,
} from "lucide-react";

export default function Index({ categories, filters = {} }) {
    const [view, setView] = useState("table"); // "table" | "tree"
    const [search, setSearch] = useState(filters.search ?? "");

    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);

    const form = useForm({
        name: "",
        parent_id: null,
        image: null,
        remove_image: false,
    });

    // Local object-URL preview for a freshly picked file; the existing image
    // url (when editing) is kept in `currentImage`.
    const [preview, setPreview] = useState(null);
    const [currentImage, setCurrentImage] = useState(null);

    const rows = categories.data ?? [];

    // ---- debounced async table search --------------------------------------

    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route("admin.catalog.categories.index"),
                { search: search || undefined },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ["categories", "filters"],
                },
            );
        }, 350);
        return () => clearTimeout(t);
    }, [search]);

    // ---- create / edit modal -----------------------------------------------

    const [parentLabel, setParentLabel] = useState("");

    const openCreate = (parentId = null, parentName = "") => {
        setEditing(null);
        form.clearErrors();
        form.setData({
            name: "",
            parent_id: parentId,
            image: null,
            remove_image: false,
        });
        setParentLabel(parentName);
        setPreview(null);
        setCurrentImage(null);
        setModalOpen(true);
    };

    const openEdit = (category) => {
        setEditing(category);
        form.clearErrors();
        form.setData({
            name: category.name,
            parent_id: category.parent_id,
            image: null,
            remove_image: false,
        });
        setParentLabel(category.parent?.name ?? "");
        setPreview(null);
        setCurrentImage(category.image ?? null);
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditing(null);
        form.reset();
        form.clearErrors();
        setParentLabel("");
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

    // keepOpen=true → "save & add another": keep the modal (and chosen parent),
    // clear only the name so the next sibling is quick.
    const submit = (e, keepOpen = false) => {
        e?.preventDefault();
        const opts = {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                refreshTree();
                if (keepOpen && !editing) {
                    form.setData((d) => ({
                        ...d,
                        name: "",
                        image: null,
                        remove_image: false,
                    }));
                    form.clearErrors();
                    setPreview(null);
                    setCurrentImage(null);
                } else {
                    closeModal();
                }
            },
        };
        if (editing) {
            // Multipart PUT loses files in PHP — spoof the method over POST.
            // transform() returns undefined in @inertiajs/react v3, so it can't
            // be chained — set it, then post.
            form.transform((data) => ({ ...data, _method: "put" }));
            form.post(route("admin.catalog.categories.update", editing.id), opts);
        } else {
            form.transform((data) => data);
            form.post(route("admin.catalog.categories.store"), opts);
        }
    };

    const confirmDelete = () => {
        if (!deleteTarget) return;
        router.delete(route("admin.catalog.categories.destroy", deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => refreshTree(),
            onFinish: () => setDeleteTarget(null),
        });
    };

    // ---- async parent picker (combobox) ------------------------------------

    const [parentOpen, setParentOpen] = useState(false);
    const [parentQuery, setParentQuery] = useState("");
    const [parentResults, setParentResults] = useState([]);
    const [parentLoading, setParentLoading] = useState(false);

    useEffect(() => {
        if (!parentOpen) return;
        setParentLoading(true);
        const t = setTimeout(async () => {
            try {
                const { data } = await axios.get(
                    route("admin.catalog.categories.lookup"),
                    {
                        params: {
                            search: parentQuery || undefined,
                            roots: parentQuery ? undefined : 1,
                            exclude: editing?.id,
                        },
                    },
                );
                setParentResults(data);
            } finally {
                setParentLoading(false);
            }
        }, 300);
        return () => clearTimeout(t);
    }, [parentQuery, parentOpen, editing]);

    const pickParent = (cat) => {
        form.setData("parent_id", cat ? cat.id : null);
        setParentLabel(cat ? (cat.path ?? cat.name) : "");
        setParentOpen(false);
        setParentQuery("");
    };

    // ---- lazy tree ----------------------------------------------------------

    const [treeRoots, setTreeRoots] = useState(null); // null = not loaded yet
    const [childrenCache, setChildrenCache] = useState({}); // id -> [] | "loading"
    const [expanded, setExpanded] = useState(() => new Set());

    const loadRoots = async () => {
        const { data } = await axios.get(route("admin.catalog.categories.lookup"), {
            params: { roots: 1 },
        });
        setTreeRoots(data);
    };

    useEffect(() => {
        if (view === "tree" && treeRoots === null) loadRoots();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [view]);

    const toggleNode = async (node) => {
        const next = new Set(expanded);
        if (next.has(node.id)) {
            next.delete(node.id);
            setExpanded(next);
            return;
        }
        next.add(node.id);
        setExpanded(next);
        if (childrenCache[node.id] === undefined) {
            setChildrenCache((c) => ({ ...c, [node.id]: "loading" }));
            const { data } = await axios.get(route("admin.catalog.categories.lookup"), {
                params: { parent_id: node.id },
            });
            setChildrenCache((c) => ({ ...c, [node.id]: data }));
        }
    };

    // After a write, the lazy tree may be stale — drop its cache so it reloads.
    const refreshTree = () => {
        setTreeRoots(null);
        setChildrenCache({});
        setExpanded(new Set());
        if (view === "tree") loadRoots();
    };

    // ---- shared row actions -------------------------------------------------

    const RowActions = ({ category, extra }) => (
        <div className="flex items-center gap-1 justify-end">
            {extra}
            <Button
                size="sm"
                variant="flat"
                color="primary"
                startContent={<Pencil className="w-3.5 h-3.5" />}
                onPress={() => openEdit(category)}
            >
                تعديل
            </Button>
            <Button
                size="sm"
                variant="ghost"
                className="text-rose-500"
                startContent={<Trash2 className="w-3.5 h-3.5" />}
                onPress={() => setDeleteTarget(category)}
            >
                حذف
            </Button>
        </div>
    );

    const TreeNode = ({ node, depth }) => {
        const isOpen = expanded.has(node.id);
        const kids = childrenCache[node.id];
        return (
            <div>
                <div
                    className="flex items-center justify-between gap-3 py-3 border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors rounded-2xl px-3"
                    style={{ marginInlineStart: `${depth * 1.5}rem` }}
                >
                    <div className="flex items-center gap-2 min-w-0">
                        {node.has_children ? (
                            <button
                                type="button"
                                onClick={() => toggleNode(node)}
                                className="w-6 h-6 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 shrink-0"
                            >
                                {isOpen ? (
                                    <ChevronDown className="w-4 h-4" />
                                ) : (
                                    <ChevronLeft className="w-4 h-4" />
                                )}
                            </button>
                        ) : (
                            <span className="w-6 shrink-0" />
                        )}
                        <div className="w-8 h-8 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <FolderTree className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p className="font-bold text-sm truncate">
                            {node.name}
                        </p>
                    </div>
                    <RowActions
                        category={node}
                        extra={
                            <Button
                                size="sm"
                                variant="ghost"
                                className="text-emerald-600"
                                startContent={
                                    <FolderPlus className="w-3.5 h-3.5" />
                                }
                                onPress={() => openCreate(node.id, node.name)}
                            >
                                فرع
                            </Button>
                        }
                    />
                </div>
                {isOpen && kids === "loading" && (
                    <div
                        className="flex items-center gap-2 py-2 text-xs text-zinc-400"
                        style={{ marginInlineStart: `${(depth + 1) * 1.5}rem` }}
                    >
                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                        جاري التحميل...
                    </div>
                )}
                {isOpen &&
                    Array.isArray(kids) &&
                    kids.map((child) => (
                        <TreeNode
                            key={child.id}
                            node={child}
                            depth={depth + 1}
                        />
                    ))}
            </div>
        );
    };

    // ---- page ---------------------------------------------------------------

    return (
        <AdminLayout title="التصنيفات">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                            {categories.total ?? rows.length} تصنيف
                        </h2>
                        <div className="flex gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-2xl p-1">
                            <button
                                type="button"
                                onClick={() => setView("table")}
                                className={`text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 transition-colors ${
                                    view === "table"
                                        ? "bg-blue-600 text-white"
                                        : "text-zinc-500"
                                }`}
                            >
                                <List className="w-3.5 h-3.5" />
                                جدول
                            </button>
                            <button
                                type="button"
                                onClick={() => setView("tree")}
                                className={`text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 transition-colors ${
                                    view === "tree"
                                        ? "bg-blue-600 text-white"
                                        : "text-zinc-500"
                                }`}
                            >
                                <FolderTree className="w-3.5 h-3.5" />
                                شجرة
                            </button>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {view === "table" && (
                            <div className="relative">
                                <Search className="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="بحث..."
                                    className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-11 pr-10 pl-4 text-sm border-none outline-none w-48"
                                />
                            </div>
                        )}
                        <Button
                            color="primary"
                            className="rounded-full font-black h-11"
                            startContent={<Plus className="w-4 h-4" />}
                            onPress={() => openCreate(null)}
                        >
                            إضافة تصنيف
                        </Button>
                    </div>
                </div>

                {/* Content */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-0">
                        {view === "table" ? (
                            <Table className="w-full">
                                <TableContent aria-label="Categories Table">
                                    <TableHeader className="text-right">
                                        <TableColumn
                                            className="rtl:text-right"
                                            isRowHeader
                                        >
                                            #
                                        </TableColumn>
                                        <TableColumn className="rtl:text-right">
                                            الاسم
                                        </TableColumn>
                                        <TableColumn className="rtl:text-right">
                                            التصنيف الأب
                                        </TableColumn>
                                        <TableColumn className="rtl:text-right">
                                            عدد الفروع
                                        </TableColumn>
                                        <TableColumn className="rtl:text-right">
                                            المعرّف
                                        </TableColumn>
                                        <TableColumn className="rtl:text-right">
                                            الإجراءات
                                        </TableColumn>
                                    </TableHeader>
                                    <TableBody
                                        items={rows}
                                        renderEmptyState={() => (
                                            <p className="text-center py-10 text-zinc-400">
                                                لا توجد تصنيفات.
                                            </p>
                                        )}
                                    >
                                        {(item) => (
                                            <TableRow
                                                id={item.id}
                                                className="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                            >
                                                <TableCell>
                                                    <span className="text-xs text-zinc-400 font-mono">
                                                        {item.id}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <p className="font-semibold text-sm">
                                                        {item.name}
                                                    </p>
                                                </TableCell>
                                                <TableCell>
                                                    {item.parent ? (
                                                        <Chip
                                                            size="sm"
                                                            variant="flat"
                                                            color="primary"
                                                        >
                                                            {item.parent.name}
                                                        </Chip>
                                                    ) : (
                                                        <span className="text-xs text-zinc-400">
                                                            تصنيف رئيسي
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <span className="text-sm text-zinc-500">
                                                        {item.children_count ??
                                                            0}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <span className="text-xs text-zinc-400 font-mono">
                                                        {item.slug}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <RowActions
                                                        category={item}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </TableContent>
                            </Table>
                        ) : (
                            <div className="p-4">
                                {treeRoots === null ? (
                                    <div className="flex items-center justify-center gap-2 py-10 text-zinc-400">
                                        <Loader2 className="w-4 h-4 animate-spin" />
                                        جاري التحميل...
                                    </div>
                                ) : treeRoots.length === 0 ? (
                                    <p className="text-center py-10 text-zinc-400">
                                        لا توجد تصنيفات.
                                    </p>
                                ) : (
                                    treeRoots.map((root) => (
                                        <TreeNode
                                            key={root.id}
                                            node={root}
                                            depth={0}
                                        />
                                    ))
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination (table view, server paginator) */}
                {view === "table" &&
                    categories.links &&
                    categories.last_page > 1 && (
                        <div className="flex justify-center gap-1 flex-wrap">
                            {categories.links.map((link, i) => (
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
                                            only: ["categories", "filters"],
                                        })
                                    }
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
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
                                    {editing ? (
                                        <Pencil className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    ) : (
                                        <FolderPlus className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    )}
                                </div>
                                {editing ? "تعديل التصنيف" : "إضافة تصنيف جديد"}
                            </Modal.Header>
                            <form onSubmit={submit}>
                                <Modal.Body>
                                    <div className="space-y-4 p-2">
                                        <TextField
                                            value={form.data.name}
                                            onChange={(v) =>
                                                form.setData("name", v)
                                            }
                                            isInvalid={!!form.errors.name}
                                            isRequired
                                        >
                                            <Label className="text-xs font-bold text-zinc-500">
                                                اسم التصنيف
                                            </Label>
                                            <Input
                                                placeholder="مثال: الهواتف"
                                                className="h-11 px-4 text-sm shadow-none outline-none border border-zinc-200 dark:border-zinc-600"
                                            />
                                            {form.errors.name && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.name}
                                                </FieldError>
                                            )}
                                        </TextField>

                                        {/* Async parent picker */}
                                        <div className="relative">
                                            <Label className="text-xs font-bold text-zinc-500">
                                                التصنيف الأب
                                            </Label>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setParentOpen((o) => !o)
                                                }
                                                className="mt-1 w-full flex items-center justify-between h-11 px-4 text-sm rounded-2xl border border-zinc-200 dark:border-zinc-600 bg-transparent rtl:text-right"
                                            >
                                                <span
                                                    className={
                                                        parentLabel
                                                            ? ""
                                                            : "text-zinc-400"
                                                    }
                                                >
                                                    {parentLabel ||
                                                        "بدون (تصنيف رئيسي)"}
                                                </span>
                                                <ChevronDown className="w-4 h-4 text-zinc-400" />
                                            </button>

                                            {parentOpen && (
                                                <div className="absolute z-[600] mt-1 w-full bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-2xl overflow-hidden">
                                                    <div className="relative p-2 border-b border-zinc-100 dark:border-zinc-800">
                                                        <Search className="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400" />
                                                        <input
                                                            autoFocus
                                                            type="text"
                                                            value={parentQuery}
                                                            onChange={(e) =>
                                                                setParentQuery(
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            placeholder="ابحث عن تصنيف..."
                                                            className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-xl h-10 pr-10 pl-3 text-sm border-none outline-none"
                                                        />
                                                    </div>
                                                    <div className="max-h-56 overflow-y-auto p-1">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                pickParent(null)
                                                            }
                                                            className="w-full text-right px-3 py-2 text-sm rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-2 text-zinc-500"
                                                        >
                                                            <X className="w-3.5 h-3.5" />
                                                            بدون (تصنيف رئيسي)
                                                        </button>
                                                        {parentLoading ? (
                                                            <div className="flex items-center gap-2 px-3 py-3 text-xs text-zinc-400">
                                                                <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                                                جاري البحث...
                                                            </div>
                                                        ) : parentResults.length ===
                                                          0 ? (
                                                            <p className="px-3 py-3 text-xs text-zinc-400">
                                                                لا نتائج.
                                                            </p>
                                                        ) : (
                                                            parentResults.map(
                                                                (c) => (
                                                                    <button
                                                                        key={
                                                                            c.id
                                                                        }
                                                                        type="button"
                                                                        onClick={() =>
                                                                            pickParent(
                                                                                c,
                                                                            )
                                                                        }
                                                                        className="w-full text-right px-3 py-2 text-sm rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                                                    >
                                                                        {c.path ??
                                                                            c.name}
                                                                    </button>
                                                                ),
                                                            )
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                            {form.errors.parent_id && (
                                                <FieldError className="text-xs text-red-500 font-bold px-1">
                                                    {form.errors.parent_id}
                                                </FieldError>
                                            )}
                                        </div>

                                        {/* Image */}
                                        <div>
                                            <Label className="text-xs font-bold text-zinc-500">
                                                صورة التصنيف (اختياري)
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
                                    {!editing && (
                                        <Button
                                            type="button"
                                            variant="flat"
                                            color="primary"
                                            isDisabled={form.processing}
                                            onPress={(e) => submit(e, true)}
                                        >
                                            حفظ وإضافة آخر
                                        </Button>
                                    )}
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
                                حذف التصنيف
                            </Modal.Header>
                            <Modal.Body>
                                {deleteTarget?.products_count > 0 ? (
                                    <div className="p-2 space-y-3">
                                        <div className="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 text-sm text-amber-700 dark:text-amber-300 font-bold">
                                            هذا التصنيف مرتبط بـ{" "}
                                            {deleteTarget.products_count} منتج.
                                        </div>
                                        <p className="text-sm text-zinc-500">
                                            عند الحذف سيتم فك ارتباط هذه المنتجات
                                            بالتصنيف (بدون حذفها). هل أنت متأكد من
                                            المتابعة؟
                                        </p>
                                    </div>
                                ) : (
                                    <p className="text-sm text-zinc-500 p-2">
                                        هل أنت متأكد من حذف التصنيف{" "}
                                        <span className="font-bold text-zinc-700 dark:text-zinc-200">
                                            {deleteTarget?.name}
                                        </span>
                                        ؟ سيتم فك ارتباط الفروع والمنتجات المرتبطة
                                        به.
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
