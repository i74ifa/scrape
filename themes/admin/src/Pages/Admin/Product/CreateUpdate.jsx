import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router, usePage } from "@inertiajs/react";
import SubscriptionAlert from "@/Components/SubscriptionAlert";
import Alert from "@/Components/Alert";
import { useState, useRef, useCallback, useMemo, useEffect } from "react";
import axios from "axios";
import {
    Save,
    Plus,
    Trash2,
    Image as ImageIcon,
    Upload,
    Utensils,
    DollarSign,
    Tag,
    Layers,
    ListChecks,
    Search,
    Edit2,
    PanelRightClose,
    PanelRight,
    X,
    AlertCircle,
    ArrowLeft,
    Eye,
    EyeOff,
    Menu as MenuIcon,
} from "lucide-react";
import {
    Card,
    CardContent,
    Button,
    TextField,
    Input,
    TextArea,
    Checkbox,
    CheckboxControl,
    CheckboxIndicator,
    Label,
    Modal,
    Pagination,
    toast,
} from "@heroui/react";

function ProductSidebar({
    products: paginatedItems,
    currentItemId,
    selectedIds,
    onToggleSelect,
    onSelectAllOnPage,
    onClearSelection,
    isMobileOpen,
    onMobileClose,
}) {
    const [sidebarWidth, setSidebarWidth] = useState(320);
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isDesktop, setIsDesktop] = useState(
        typeof window !== "undefined" ? window.innerWidth >= 1024 : true,
    );
    const [searchQuery, setSearchQuery] = useState("");
    const [loading, setLoading] = useState(false);
    const [availabilityOverrides, setAvailabilityOverrides] = useState({});
    const [togglingId, setTogglingId] = useState(null);
    const isResizing = useRef(false);
    const debounceTimer = useRef(null);

    const items = paginatedItems?.data || [];
    const pagination = paginatedItems || {};

    const toggleAvailability = useCallback(async (item) => {
        setTogglingId(item.id);
        const prev =
            availabilityOverrides[item.id] ?? !!item.is_available;
        const next = !prev;
        setAvailabilityOverrides((o) => ({ ...o, [item.id]: next }));
        try {
            const { data } = await axios.patch(
                route("admin.menu-items.toggle-availability", item.id),
            );
            setAvailabilityOverrides((o) => ({
                ...o,
                [item.id]: !!data.is_available,
            }));
        } catch (err) {
            setAvailabilityOverrides((o) => ({ ...o, [item.id]: prev }));
            toast.error(
                err.response?.data?.message || "حدث خطأ أثناء تحديث الحالة",
            );
        } finally {
            setTogglingId(null);
        }
    }, [availabilityOverrides]);

    const handleMouseDown = useCallback(
        (e) => {
            isResizing.current = true;
            const startX = e.clientX;
            const startWidth = sidebarWidth;

            const handleMouseMove = (e) => {
                if (!isResizing.current) return;
                const diff = startX - e.clientX;
                const newWidth = Math.max(
                    260,
                    Math.min(600, startWidth + diff),
                );
                setSidebarWidth(newWidth);
            };

            const handleMouseUp = () => {
                isResizing.current = false;
                document.removeEventListener("mousemove", handleMouseMove);
                document.removeEventListener("mouseup", handleMouseUp);
                document.body.style.cursor = "";
                document.body.style.userSelect = "";
            };

            document.body.style.cursor = "col-resize";
            document.body.style.userSelect = "none";
            document.addEventListener("mousemove", handleMouseMove);
            document.addEventListener("mouseup", handleMouseUp);
        },
        [sidebarWidth],
    );

    const handleSearch = useCallback((value) => {
        setSearchQuery(value);
        clearTimeout(debounceTimer.current);
        debounceTimer.current = setTimeout(() => {
            setLoading(true);
            router.reload({
                data: { search: value, page: 1 },
                only: ["products"],
                onFinish: () => setLoading(false),
            });
        }, 300);
    }, []);

    const goToPage = useCallback(
        (page) => {
            setLoading(true);
            router.reload({
                data: { search: searchQuery, page },
                only: ["products"],
                onFinish: () => setLoading(false),
            });
        },
        [searchQuery],
    );

    useEffect(() => {
        return () => clearTimeout(debounceTimer.current);
    }, []);

    useEffect(() => {
        if (typeof window === "undefined") return;
        const mq = window.matchMedia("(min-width: 1024px)");
        const update = () => setIsDesktop(mq.matches);
        update();
        mq.addEventListener("change", update);
        return () => mq.removeEventListener("change", update);
    }, []);

    useEffect(() => {
        if (typeof window === "undefined" || isDesktop) return;
        document.body.style.overflow = isMobileOpen ? "hidden" : "";
        return () => { document.body.style.overflow = ""; };
    }, [isMobileOpen, isDesktop]);

    const navigateToItem = useCallback((id) => {
        router.get(
            route("admin.menu-items.edit", id),
            {},
            { preserveScroll: true },
        );
        onMobileClose?.();
    }, [onMobileClose]);

    if (isCollapsed && isDesktop) {
        return (
            <div className="hidden lg:flex shrink-0 flex-col items-center py-4 gap-3">
                <Button
                    isIconOnly
                    variant="ghost"
                    onPress={() => setIsCollapsed(false)}
                    className="rounded-full"
                >
                    <PanelRight size={18} />
                </Button>
                <div className="w-px flex-1 bg-zinc-200 dark:bg-zinc-700" />
                <span className="text-xs font-bold text-zinc-400 [writing-mode:vertical-rl] rotate-180">
                    القائمة ({pagination.total || 0})
                </span>
            </div>
        );
    }

    return (
        <>
            {/* Mobile backdrop */}
            <div
                onClick={onMobileClose}
                className={`lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[140] transition-opacity duration-200 ${
                    isMobileOpen ? "opacity-100" : "opacity-0 pointer-events-none"
                }`}
            />
            <div
                className={`shrink-0 flex flex-row-reverse fixed lg:static inset-y-0 right-0 z-[150] lg:z-auto w-[88vw] max-w-md lg:max-w-none transform transition-transform duration-300 lg:transition-none lg:translate-x-0 ${
                    isMobileOpen ? "translate-x-0" : "translate-x-full"
                }`}
                style={isDesktop ? { width: sidebarWidth } : undefined}
            >
                {/* Resize handle (desktop only) */}
                <div
                    onMouseDown={handleMouseDown}
                    className="hidden lg:flex w-4 shrink-0 cursor-col-resize items-center justify-center group hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors rounded-lg"
                >
                    <div className="w-1 h-10 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover:bg-blue-400 transition-colors" />
                </div>

                {/* Sidebar content */}
                <div className="flex-1 flex flex-col min-w-0 bg-white dark:bg-zinc-900 lg:rounded-2xl shadow-2xl lg:shadow-none">
                    {/* Header */}
                    <div className="flex items-center justify-between px-4 py-3">
                        <h3 className="font-black text-sm tracking-tight">
                            القائمة
                            {selectedIds?.length > 0 && (
                                <span className="ml-2 text-xs font-bold text-blue-600 dark:text-blue-400">
                                    ({selectedIds.length} محدد)
                                </span>
                            )}
                        </h3>
                        <div className="flex items-center gap-1">
                            {selectedIds?.length > 0 && (
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    onPress={onClearSelection}
                                    className="rounded-full text-xs font-bold"
                                >
                                    إلغاء التحديد
                                </Button>
                            )}
                            <Button
                                isIconOnly
                                size="sm"
                                variant="ghost"
                                onPress={() => {
                                    router.get(route("admin.menu-items.create"));
                                    onMobileClose?.();
                                }}
                                className="rounded-full"
                            >
                                <Plus size={16} />
                            </Button>
                            <Button
                                isIconOnly
                                size="sm"
                                variant="ghost"
                                onPress={() => setIsCollapsed(true)}
                                className="hidden lg:inline-flex rounded-full"
                            >
                                <PanelRightClose size={16} />
                            </Button>
                            <Button
                                isIconOnly
                                size="sm"
                                variant="ghost"
                                onPress={onMobileClose}
                                className="lg:hidden rounded-full"
                            >
                                <X size={18} />
                            </Button>
                        </div>
                    </div>

                {/* Select all on page */}
                {items.length > 0 && (
                    <div className="px-4 pb-2 flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={
                                items.length > 0 &&
                                items.every((i) =>
                                    selectedIds?.includes(i.id),
                                )
                            }
                            onChange={(e) =>
                                onSelectAllOnPage?.(
                                    items.map((i) => i.id),
                                    e.target.checked,
                                )
                            }
                            className="w-4 h-4 accent-blue-600"
                        />
                        <span className="text-xs font-bold text-zinc-500">
                            تحديد الكل في هذه الصفحة
                        </span>
                    </div>
                )}

                {/* Search */}
                <div className="px-3 pb-3">
                    <div className="relative">
                        <Search
                            size={14}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400"
                        />
                        <input
                            type="text"
                            placeholder="بحث..."
                            value={searchQuery}
                            onChange={(e) => handleSearch(e.target.value)}
                            className="w-full h-9 pr-9 pl-3 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-xs border-none outline-none"
                        />
                    </div>
                </div>

                {/* Items list */}
                <div className="flex-1 overflow-y-auto px-2 pb-4 space-y-1">
                    {loading && (
                        <div className="flex justify-center py-4">
                            <div className="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
                        </div>
                    )}
                    {!loading && items.length === 0 ? (
                        <p className="text-center text-xs text-zinc-400 py-8">
                            لا يوجد نتائج
                        </p>
                    ) : (
                        !loading &&
                        items.map((item) => {
                            const isAvailable =
                                availabilityOverrides[item.id] ??
                                !!item.is_available;
                            const isSelected =
                                selectedIds?.includes(item.id) ?? false;
                            return (
                            <div
                                key={item.id}
                                className={`w-full rounded-2xl transition-all flex items-center gap-2 group pr-2 pl-1 ${
                                    isSelected
                                        ? "bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-700"
                                        : currentItemId === item.id
                                        ? "bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800"
                                        : "hover:bg-zinc-100 dark:hover:bg-zinc-800/50 border border-transparent"
                                }`}
                                style={{ opacity: isAvailable ? 1 : 0.85 }}
                            >
                                <input
                                    type="checkbox"
                                    checked={isSelected}
                                    onChange={() => onToggleSelect?.(item.id)}
                                    onClick={(e) => e.stopPropagation()}
                                    className="w-4 h-4 accent-blue-600 shrink-0 mr-1"
                                />
                                <button
                                    type="button"
                                    onClick={() => {
                                        if ((selectedIds?.length ?? 0) > 0) {
                                            onToggleSelect?.(item.id);
                                            return;
                                        }
                                        navigateToItem(item.id);
                                    }}
                                    className="flex-1 min-w-0 text-right py-3 flex items-center gap-3"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center shrink-0 overflow-hidden">
                                        {item.full_image_url ? (
                                            <img
                                                src={item.full_image_url}
                                                alt={item.name}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <Utensils
                                                size={16}
                                                className="text-zinc-400"
                                            />
                                        )}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p
                                            className={`text-sm font-bold truncate ${
                                                currentItemId === item.id
                                                    ? "text-blue-600 dark:text-blue-400"
                                                    : "text-zinc-800 dark:text-zinc-200"
                                            }`}
                                        >
                                            {item.name}
                                        </p>
                                        <div className="flex items-center gap-2 mt-0.5">
                                            <span className="text-xs text-zinc-400 truncate">
                                                {item.category?.name ||
                                                    "بدون تصنيف"}
                                            </span>
                                            <span className="text-xs font-bold text-zinc-500">
                                                {item.base_price} د.ع
                                            </span>
                                        </div>
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        toggleAvailability(item);
                                    }}
                                    disabled={togglingId === item.id}
                                    title={isAvailable ? "إخفاء" : "إظهار"}
                                    className={`shrink-0 p-2 rounded-full transition-colors ${
                                        isAvailable
                                            ? "text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                                            : "text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700"
                                    } ${togglingId === item.id ? "opacity-50" : ""}`}
                                >
                                    {isAvailable ? (
                                        <Eye size={16} />
                                    ) : (
                                        <EyeOff size={16} />
                                    )}
                                </button>
                            </div>
                            );
                        })
                    )}
                </div>
                {pagination.last_page > 1 && (
                    <Pagination className="justify-center">
                        <Pagination.Content>
                            <Pagination.Item>
                                <Pagination.Previous
                                    isDisabled={pagination.current_page === 1}
                                    onPress={() =>
                                        goToPage(pagination.current_page - 1)
                                    }
                                >
                                    <Pagination.PreviousIcon />
                                    <span>السابق</span>
                                </Pagination.Previous>
                            </Pagination.Item>
                            {Array.from(
                                { length: pagination.last_page },
                                (_, i) => i + 1,
                            ).map((p) => (
                                <Pagination.Item key={p}>
                                    <Pagination.Link
                                        isActive={p === pagination.current_page}
                                        onPress={() => goToPage(p)}
                                    >
                                        {p}
                                    </Pagination.Link>
                                </Pagination.Item>
                            ))}
                            <Pagination.Item>
                                <Pagination.Next
                                    isDisabled={
                                        pagination.current_page ===
                                        pagination.last_page
                                    }
                                    onPress={() =>
                                        goToPage(pagination.current_page + 1)
                                    }
                                >
                                    <span>التالي</span>
                                    <Pagination.NextIcon />
                                </Pagination.Next>
                            </Pagination.Item>
                        </Pagination.Content>
                    </Pagination>
                )}
                </div>
            </div>
        </>
    );
}

function CategoryAutocomplete({
    categories,
    selectedId,
    onSelect,
    onCreateNew,
    error,
}) {
    const [query, setQuery] = useState("");
    const [isOpen, setIsOpen] = useState(false);
    const inputRef = useRef(null);
    const dropdownRef = useRef(null);

    const selectedCategory = categories.find(
        (c) => String(c.id) === String(selectedId),
    );

    const filtered = useMemo(() => {
        if (!query) return categories;
        return categories.filter((c) =>
            c.name.toLowerCase().includes(query.toLowerCase()),
        );
    }, [query, categories]);

    const handleSelect = (category) => {
        onSelect(String(category.id));
        setQuery("");
        setIsOpen(false);
    };

    const handleInputFocus = () => {
        setIsOpen(true);
    };

    const handleInputChange = (e) => {
        setQuery(e.target.value);
        setIsOpen(true);
    };

    // Close dropdown on outside click
    const handleBlur = (e) => {
        setTimeout(() => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(document.activeElement)
            ) {
                setIsOpen(false);
                setQuery("");
            }
        }, 150);
    };

    return (
        <div className="relative">
            <label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                التصنيف
            </label>
            <div className="flex gap-2">
                <div className="relative flex-1" ref={dropdownRef}>
                    <Search
                        size={16}
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"
                    />
                    <input
                        ref={inputRef}
                        type="text"
                        placeholder={
                            selectedCategory
                                ? selectedCategory.name
                                : "ابحث عن تصنيف..."
                        }
                        value={query}
                        onChange={handleInputChange}
                        onFocus={handleInputFocus}
                        onBlur={handleBlur}
                        className={`w-full bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-12 pr-10 pl-4 shadow-none text-sm border-none outline-none ${
                            selectedCategory && !query
                                ? "text-zinc-800 dark:text-zinc-200 font-bold"
                                : ""
                        } ${error ? "ring-2 ring-red-500" : ""}`}
                    />
                    {selectedCategory && !query && !isOpen && (
                        <button
                            type="button"
                            onClick={() => {
                                onSelect("");
                                inputRef.current?.focus();
                            }}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                        >
                            <X size={14} />
                        </button>
                    )}

                    {isOpen && (
                        <div className="absolute z-50 top-full mt-2 w-full bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 max-h-60 overflow-y-auto">
                            {filtered.length > 0 ? (
                                filtered.map((category) => (
                                    <button
                                        key={category.id}
                                        type="button"
                                        onMouseDown={(e) => e.preventDefault()}
                                        onClick={() => handleSelect(category)}
                                        className={`w-full text-right px-4 py-3 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors first:rounded-t-2xl last:rounded-b-2xl flex items-center justify-between ${
                                            String(category.id) ===
                                            String(selectedId)
                                                ? "bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-bold"
                                                : ""
                                        }`}
                                    >
                                        <span>{category.name}</span>
                                        {String(category.id) ===
                                            String(selectedId) && (
                                            <span className="text-xs text-blue-500">
                                                محدد
                                            </span>
                                        )}
                                    </button>
                                ))
                            ) : (
                                <div className="px-4 py-6 text-center">
                                    <p className="text-sm text-zinc-400 mb-3">
                                        لا يوجد تصنيف "{query}"
                                    </p>
                                    <Button
                                        size="sm"
                                        variant="flat"
                                        onPress={() => {
                                            onCreateNew(query);
                                            setIsOpen(false);
                                            setQuery("");
                                        }}
                                        className="rounded-full text-xs font-bold px-4 bg-emerald-100/50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                                    >
                                        <Plus size={14} /> إنشاء "{query}"
                                    </Button>
                                </div>
                            )}
                        </div>
                    )}
                </div>
                <Button
                    type="button"
                    isIconOnly
                    variant="flat"
                    onPress={() => onCreateNew("")}
                    className="rounded-full h-12 w-12 bg-emerald-100/50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 shrink-0"
                >
                    <Plus size={18} />
                </Button>
            </div>
            {error && <p className="text-red-500 text-xs mt-1 pl-2">{error}</p>}
        </div>
    );
}

function CreateCategoryModal({
    isOpen,
    onClose,
    onCreated,
    initialName = "",
    csrfToken,
}) {
    const [name, setName] = useState(initialName);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");

    // Sync initialName when modal opens
    const prevOpen = useRef(false);
    if (isOpen && !prevOpen.current) {
        prevOpen.current = true;
        if (initialName !== name) setName(initialName);
    }
    if (!isOpen && prevOpen.current) {
        prevOpen.current = false;
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!name.trim()) {
            setError("اسم التصنيف مطلوب");
            return;
        }
        setSaving(true);
        setError("");
        try {
            const response = await axios.post(route("admin.categories.store"), {
                _token: csrfToken,
                name: name.trim(),
            });

            onCreated(response.data);
            setName("");
            onClose();
        } catch (err) {
            setError(err.response?.data?.message || "حدث خطأ في الاتصال");
        } finally {
            setSaving(false);
        }
    };

    return (
        <Modal
            isOpen={isOpen}
            onOpenChange={(open) => !open && onClose()}
            placement="center"
        >
            <Modal.Container className="fixed inset-0 z-50 flex items-center justify-center p-4 w-full">
                <Modal.Backdrop className="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                <Modal.Dialog className="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl w-full max-w-md p-0 outline-none">
                    <form onSubmit={handleSubmit}>
                        <Modal.Header className="flex items-center justify-between p-6 pb-0">
                            <Modal.Heading className="text-lg font-black tracking-tight">
                                تصنيف جديد
                            </Modal.Heading>
                            <Modal.CloseTrigger asChild>
                                <Button
                                    type="button"
                                    isIconOnly
                                    variant="ghost"
                                    className="rounded-full"
                                >
                                    <X size={18} />
                                </Button>
                            </Modal.CloseTrigger>
                        </Modal.Header>

                        <Modal.Body className="p-6 space-y-6">
                            <TextField
                                isRequired
                                isInvalid={!!error}
                                value={name}
                                onChange={setName}
                                autoFocus
                            >
                                <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                    اسم التصنيف
                                </Label>
                                <Input
                                    placeholder="ادخل اسم التصنيف"
                                    className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                />
                                {error && (
                                    <p className="text-red-500 text-xs mt-1 pl-2">
                                        {error}
                                    </p>
                                )}
                            </TextField>
                        </Modal.Body>

                        <Modal.Footer className="flex gap-3 justify-end p-6 pt-0">
                            <Modal.CloseTrigger asChild>
                                <Button
                                    type="button"
                                    variant="flat"
                                    className="rounded-full font-bold px-6"
                                    onPress={() => {
                                        onClose();
                                    }}
                                >
                                    إلغاء
                                </Button>
                            </Modal.CloseTrigger>
                            <Button
                                type="submit"
                                isDisabled={saving}
                                className="rounded-full font-black px-6 bg-emerald-600 text-white"
                            >
                                {saving ? "جاري الحفظ..." : "إنشاء"}
                            </Button>
                        </Modal.Footer>
                    </form>
                </Modal.Dialog>
            </Modal.Container>
        </Modal>
    );
}

function BulkEditForm({ selectedIds, categories, onCreateCategory, onDone, onOpenSidebar }) {
    const [categoryId, setCategoryId] = useState("");
    const [availability, setAvailability] = useState("unchanged");
    const [modifiers, setModifiers] = useState([]);
    const [optionGroups, setOptionGroups] = useState([]);
    const [saving, setSaving] = useState(false);

    const addModifier = () =>
        setModifiers((m) => [
            ...m,
            { name: "", extra_price: 0, max_selection: 1 },
        ]);
    const removeModifier = (i) =>
        setModifiers((m) => m.filter((_, idx) => idx !== i));
    const updateModifier = (i, field, value) =>
        setModifiers((m) =>
            m.map((row, idx) =>
                idx === i ? { ...row, [field]: value } : row,
            ),
        );

    const addOptionGroup = () =>
        setOptionGroups((g) => [
            ...g,
            { name: "", options: [{ name: "", price: 0 }] },
        ]);
    const removeOptionGroup = (i) =>
        setOptionGroups((g) => g.filter((_, idx) => idx !== i));
    const updateOptionGroup = (i, field, value) =>
        setOptionGroups((g) =>
            g.map((row, idx) =>
                idx === i ? { ...row, [field]: value } : row,
            ),
        );
    const addOption = (gi) =>
        setOptionGroups((g) =>
            g.map((row, idx) =>
                idx === gi
                    ? {
                          ...row,
                          options: [...row.options, { name: "", price: 0 }],
                      }
                    : row,
            ),
        );
    const removeOption = (gi, oi) =>
        setOptionGroups((g) =>
            g.map((row, idx) =>
                idx === gi
                    ? {
                          ...row,
                          options: row.options.filter((_, j) => j !== oi),
                      }
                    : row,
            ),
        );
    const updateOption = (gi, oi, field, value) =>
        setOptionGroups((g) =>
            g.map((row, idx) =>
                idx === gi
                    ? {
                          ...row,
                          options: row.options.map((opt, j) =>
                              j === oi ? { ...opt, [field]: value } : opt,
                          ),
                      }
                    : row,
            ),
        );

    const submit = (e) => {
        e.preventDefault();
        const payload = { ids: selectedIds };
        if (categoryId) payload.category_id = categoryId;
        if (availability !== "unchanged")
            payload.is_available = availability === "true";
        if (modifiers.length) payload.modifiers = modifiers;
        if (optionGroups.length) payload.option_groups = optionGroups;

        const hasChanges =
            payload.category_id !== undefined ||
            payload.is_available !== undefined ||
            payload.modifiers ||
            payload.option_groups;
        if (!hasChanges) {
            toast.error("لا يوجد تغييرات للتطبيق");
            return;
        }

        setSaving(true);
        router.post(route("admin.menu-items.bulk-update"), payload, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    `تم تطبيق التعديلات على ${selectedIds.length} عنصر`,
                );
                onDone?.();
            },
            onError: () => toast.error("حدث خطأ أثناء التعديل الجماعي"),
            onFinish: () => setSaving(false),
        });
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-8 pb-20 px-4">
            <div className="flex flex-wrap justify-between items-center gap-3 px-2 pt-2">
                <div className="flex items-center gap-2 min-w-0">
                    <Button
                        type="button"
                        isIconOnly
                        variant="flat"
                        onPress={onOpenSidebar}
                        className="lg:hidden rounded-full h-10 w-10 shrink-0"
                        aria-label="فتح القائمة"
                    >
                        <MenuIcon size={18} />
                    </Button>
                    <h4 className="text-sm sm:text-base font-bold text-zinc-500 dark:text-zinc-400 flex items-center gap-2 min-w-0">
                        <Edit2 size={16} className="shrink-0" />
                        <span className="truncate">تعديل {selectedIds.length} عنصر</span>
                    </h4>
                </div>
                <div className="flex items-center gap-2 sm:gap-3 ml-auto">
                    <Button
                        type="button"
                        variant="flat"
                        onPress={onDone}
                        className="rounded-full font-bold sm:px-5 h-11"
                        aria-label="إلغاء"
                    >
                        <X size={16} />
                        <span className="hidden sm:inline">إلغاء</span>
                    </Button>
                    <Button
                        type="submit"
                        isDisabled={saving}
                        className="rounded-full font-black h-11 px-3 sm:px-4 bg-blue-600 text-white flex items-center justify-center rtl:flex-row-reverse"
                    >
                        <span className="hidden sm:inline">
                            {saving ? "جاري التطبيق..." : "تطبيق"}
                        </span>
                        <span className="sm:hidden">{saving ? "..." : "تطبيق"}</span>
                        <div className="rtl:-mr-2 sm:rtl:-mr-3 ltr:-ml-2 sm:ltr:-ml-3 h-9 w-9 rounded-2xl bg-blue-800 flex items-center justify-center shrink-0">
                            <Save size={20} />
                        </div>
                    </Button>
                </div>
            </div>

            <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                <CardContent className="p-5 space-y-8">
                    <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                        <Tag className="text-emerald-500" /> الحقول القابلة
                        للتعديل الجماعي
                    </h3>

                    <CategoryAutocomplete
                        categories={categories}
                        selectedId={categoryId}
                        onSelect={setCategoryId}
                        onCreateNew={onCreateCategory}
                    />

                    <div>
                        <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                            الإتاحة
                        </Label>
                        <div className="flex gap-2">
                            {[
                                { value: "unchanged", label: "بدون تغيير" },
                                { value: "true", label: "متاح" },
                                { value: "false", label: "غير متاح" },
                            ].map((opt) => (
                                <Button
                                    key={opt.value}
                                    type="button"
                                    variant={
                                        availability === opt.value
                                            ? "solid"
                                            : "flat"
                                    }
                                    color={
                                        availability === opt.value
                                            ? "primary"
                                            : "default"
                                    }
                                    onPress={() => setAvailability(opt.value)}
                                    className="rounded-full font-bold"
                                >
                                    {opt.label}
                                </Button>
                            ))}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                <CardContent className="p-5">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                            <ListChecks className="text-amber-500" /> خيارات
                            (ستُضاف لكل العناصر المحددة)
                        </h3>
                        <Button
                            type="button"
                            onPress={addOptionGroup}
                            variant="flat"
                            className="rounded-full font-bold px-6 bg-amber-100/50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                        >
                            <Plus size={18} /> مجموعة جديدة
                        </Button>
                    </div>
                    <div className="space-y-6">
                        {optionGroups.length === 0 ? (
                            <p className="text-xs text-zinc-400 text-center py-6">
                                لن تتم إضافة خيارات
                            </p>
                        ) : (
                            optionGroups.map((group, gi) => (
                                <div
                                    key={gi}
                                    className="bg-zinc-50/50 dark:bg-zinc-800/20 p-6 rounded-4xl space-y-4"
                                >
                                    <div className="flex gap-4 items-end">
                                        <div className="flex-1">
                                            <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                اسم المجموعة
                                            </label>
                                            <input
                                                placeholder="مثال: الحجم"
                                                value={group.name}
                                                onChange={(e) =>
                                                    updateOptionGroup(
                                                        gi,
                                                        "name",
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none"
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            isIconOnly
                                            variant="ghost"
                                            onPress={() =>
                                                removeOptionGroup(gi)
                                            }
                                            className="rounded-full text-red-500"
                                        >
                                            <Trash2 size={20} />
                                        </Button>
                                    </div>
                                    <div className="pr-4 space-y-3">
                                        <div className="flex justify-between items-center">
                                            <label className="text-xs font-bold text-amber-600 dark:text-amber-400">
                                                الخيارات
                                            </label>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="flat"
                                                onPress={() => addOption(gi)}
                                                className="rounded-full text-xs font-bold px-4 bg-amber-100/50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                            >
                                                <Plus size={14} /> خيار
                                            </Button>
                                        </div>
                                        {group.options.map((option, oi) => (
                                            <div
                                                key={oi}
                                                className="flex gap-3 items-center"
                                            >
                                                <div className="w-3 h-3 rounded-full border-2 border-amber-400 shrink-0" />
                                                <input
                                                    placeholder="اسم الخيار"
                                                    value={option.name}
                                                    onChange={(e) =>
                                                        updateOption(
                                                            gi,
                                                            oi,
                                                            "name",
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="flex-1 bg-white dark:bg-zinc-800 rounded-xl h-10 px-4 text-sm border-none outline-none"
                                                />
                                                <div className="w-28">
                                                    <input
                                                        placeholder="0.00"
                                                        type="number"
                                                        value={option.price}
                                                        onChange={(e) =>
                                                            updateOption(
                                                                gi,
                                                                oi,
                                                                "price",
                                                                e.target.value,
                                                            )
                                                        }
                                                        className="w-full bg-white dark:bg-zinc-800 rounded-xl h-10 px-3 text-sm border-none outline-none"
                                                    />
                                                </div>
                                                {group.options.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        isIconOnly
                                                        size="sm"
                                                        variant="ghost"
                                                        onPress={() =>
                                                            removeOption(
                                                                gi,
                                                                oi,
                                                            )
                                                        }
                                                        className="rounded-full text-red-400"
                                                    >
                                                        <Trash2 size={16} />
                                                    </Button>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </CardContent>
            </Card>

            <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                <CardContent className="p-5">
                    <div className="flex justify-between items-center mb-6">
                        <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                            <Layers className="text-purple-500" /> إضافات
                            (ستُضاف لكل العناصر المحددة)
                        </h3>
                        <Button
                            type="button"
                            onPress={addModifier}
                            variant="flat"
                            className="rounded-full font-bold px-6 bg-purple-100/50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                        >
                            <Plus size={18} /> إضافة جديدة
                        </Button>
                    </div>
                    <div className="space-y-6">
                        {modifiers.length === 0 ? (
                            <p className="text-xs text-zinc-400 text-center py-6">
                                لن تتم إضافة اضافات
                            </p>
                        ) : (
                            modifiers.map((m, i) => (
                                <div
                                    key={i}
                                    className="bg-zinc-50/50 dark:bg-zinc-800/20 p-6 rounded-4xl"
                                >
                                    <div className="flex gap-4 items-end">
                                        <div className="flex-1">
                                            <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                اسم الإضافة
                                            </label>
                                            <input
                                                value={m.name}
                                                onChange={(e) =>
                                                    updateModifier(
                                                        i,
                                                        "name",
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none"
                                            />
                                        </div>
                                        <div className="w-32">
                                            <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                سعر إضافي
                                            </label>
                                            <input
                                                type="number"
                                                value={m.extra_price}
                                                onChange={(e) =>
                                                    updateModifier(
                                                        i,
                                                        "extra_price",
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none"
                                            />
                                        </div>
                                        <div className="w-24">
                                            <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                الحد الأقصى
                                            </label>
                                            <input
                                                type="number"
                                                value={m.max_selection}
                                                onChange={(e) =>
                                                    updateModifier(
                                                        i,
                                                        "max_selection",
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none"
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            isIconOnly
                                            variant="ghost"
                                            onPress={() => removeModifier(i)}
                                            className="rounded-full text-red-500"
                                        >
                                            <Trash2 size={20} />
                                        </Button>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}

export default function CreateUpdate({
    product,
    products = { data: [] },
    categories: initialCategories,
    tenants,
    csrf_token,
}) {
    const isEdit = !!product;
    const [categories, setCategories] = useState(initialCategories);
    const [showCategoryModal, setShowCategoryModal] = useState(false);
    const [newCategoryName, setNewCategoryName] = useState("");
    const [selectedIds, setSelectedIds] = useState([]);
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const isBulkMode = selectedIds.length > 0;

    const toggleSelect = (id) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );
    };
    const selectAllOnPage = (ids, checked) => {
        setSelectedIds((prev) => {
            const set = new Set(prev);
            if (checked) ids.forEach((id) => set.add(id));
            else ids.forEach((id) => set.delete(id));
            return Array.from(set);
        });
    };
    const clearSelection = () => setSelectedIds([]);

    const [imagePreview, setImagePreview] = useState(
        product?.full_image_url
    );
    const [isDragging, setIsDragging] = useState(false);
    const fileInputRef = useRef(null);

    const { data, setData, post, processing, errors } = useForm({
        category_id: product?.category_id || "",
        name: product?.name || "",
        description: product?.description || "",
        base_price: product?.base_price || "",
        sku: product?.sku || "",
        is_available: product?.is_available ?? true,
        image: null,
        modifiers: product?.modifiers || [],
        option_groups: product?.option_groups || [],
    });

    const handleImageSelect = (file) => {
        if (!file || !file.type.startsWith("image/")) return;
        setData("image", file);
        setImagePreview(URL.createObjectURL(file));
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setIsDragging(false);
        const file = e.dataTransfer.files[0];
        handleImageSelect(file);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (e) => {
        e.preventDefault();
        setIsDragging(false);
    };

    const removeImage = () => {
        setData("image", null);
        setImagePreview(null);
        if (fileInputRef.current) fileInputRef.current.value = "";
    };

    const addModifier = () => {
        setData("modifiers", [
            ...data.modifiers,
            { name: "", extra_price: 0, max_selection: 1 },
        ]);
    };

    const removeModifier = (index) => {
        const newModifiers = [...data.modifiers];
        newModifiers.splice(index, 1);
        setData("modifiers", newModifiers);
    };

    const updateModifier = (index, field, value) => {
        const newModifiers = [...data.modifiers];
        newModifiers[index][field] = value;
        setData("modifiers", newModifiers);
    };

    const addOptionGroup = () => {
        setData("option_groups", [
            ...data.option_groups,
            { name: "", options: [{ name: "", price: 0 }] },
        ]);
    };

    const removeOptionGroup = (index) => {
        const newGroups = [...data.option_groups];
        newGroups.splice(index, 1);
        setData("option_groups", newGroups);
    };

    const updateOptionGroup = (index, field, value) => {
        const newGroups = [...data.option_groups];
        newGroups[index][field] = value;
        setData("option_groups", newGroups);
    };

    const addOption = (groupIndex) => {
        const newGroups = [...data.option_groups];
        newGroups[groupIndex].options = [
            ...newGroups[groupIndex].options,
            { name: "", price: 0 },
        ];
        setData("option_groups", newGroups);
    };

    const removeOption = (groupIndex, optionIndex) => {
        const newGroups = [...data.option_groups];
        newGroups[groupIndex].options.splice(optionIndex, 1);
        setData("option_groups", newGroups);
    };

    const updateOption = (groupIndex, optionIndex, field, value) => {
        const newGroups = [...data.option_groups];
        newGroups[groupIndex].options[optionIndex][field] = value;
        setData("option_groups", newGroups);
    };

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            router.post(
                route("admin.menu-items.update", product.id),
                {
                    _method: "put",
                    ...data,
                },
                {
                    forceFormData: true,
                    onSuccess: () =>
                        toast.success("تم حفظ التعديلات بنجاح!"),
                    onError: () =>
                        toast.error("حدث خطأ أثناء حفظ التعديلات"),
                },
            );
        } else {
            post(route("admin.menu-items.store"), {
                forceFormData: true,
                onSuccess: () =>
                    toast.success("تم إنشاء العنصر بنجاح!"),
                onError: () =>
                    toast.error("حدث خطأ أثناء إنشاء العنصر"),
             });
        }
    };

    const handleDelete = () => {
        if (confirm("هل أنت متأكد من حذف هذا العنصر؟")) {
            router.delete(route("admin.menu-items.destroy", product.id));
        }
    };

    return (
        <AdminLayout title="قائمة الطعام">
            <div className="flex gap-0 min-h-[calc(100vh-250px)] relative">
                {/* Sidebar - Product List */}
                <ProductSidebar
                    products={products}
                    currentItemId={product?.id}
                    selectedIds={selectedIds}
                    onToggleSelect={toggleSelect}
                    onSelectAllOnPage={selectAllOnPage}
                    onClearSelection={clearSelection}
                    isMobileOpen={sidebarOpen}
                    onMobileClose={() => setSidebarOpen(false)}
                />

                {/* Main Form Area */}
                <div className="flex-1 min-w-0">
                    {isBulkMode ? (
                        <BulkEditForm
                            selectedIds={selectedIds}
                            categories={categories}
                            onCreateCategory={(prefill) => {
                                setNewCategoryName(prefill);
                                setShowCategoryModal(true);
                            }}
                            onDone={clearSelection}
                            onOpenSidebar={() => setSidebarOpen(true)}
                        />
                    ) : (
                    <form
                        onSubmit={submit}
                        className="flex flex-col gap-10 pb-20 md:px-4"
                    >
                        {usePage().props.needSubscription && (
                            <SubscriptionAlert />
                        )}

                        {errors.message && (
                            <Alert
                                title="اكو مشكلة!"
                                message={errors?.message}
                                buttonText="ترقية اشتراكك"
                                buttonLink={route("admin.subscription.index")}
                                icon={AlertCircle}
                                color="red"
                            />
                        )}

                        {/* Top bar */}
                        <div className="flex flex-wrap justify-between items-center gap-3 md:px-2 pt-2">
                            <div className="flex items-center gap-2 min-w-0">
                                <Button
                                    type="button"
                                    isIconOnly
                                    variant="flat"
                                    onPress={() => setSidebarOpen(true)}
                                    className="lg:hidden rounded-full h-10 w-10 shrink-0"
                                    aria-label="فتح القائمة"
                                >
                                    <MenuIcon size={18} />
                                </Button>
                                <h4 className="text-base font-bold text-zinc-500 dark:text-zinc-400 truncate">
                                    {isEdit ? (
                                        <span className="flex items-center gap-2 min-w-0">
                                            <Edit2 size={16} className="shrink-0" />
                                            <span className="truncate">تعديل: {product.name}</span>
                                        </span>
                                    ) : (
                                        <span className="flex items-center gap-2">
                                            <Plus size={16} />
                                            عنصر جديد
                                        </span>
                                    )}
                                </h4>
                            </div>
                            <div className="flex items-center gap-2 sm:gap-3 ml-auto">
                                {isEdit && (
                                    <Button
                                        variant="flat"
                                        onPress={handleDelete}
                                        className="rounded-full font-bold sm:px-5 h-11 bg-red-100/50 text-red-600 dark:bg-red-900/30 dark:text-red-400"
                                        aria-label="حذف"
                                    >
                                        <Trash2 size={16} />
                                        <span className="hidden sm:inline">حذف</span>
                                    </Button>
                                )}
                                <Button
                                    type="submit"
                                    isDisabled={
                                        processing ||
                                        usePage().props.needSubscription
                                    }
                                    className="rounded-full font-black h-11 px-3 sm:px-4 bg-blue-600 text-white flex items-center justify-center rtl:flex-row-reverse"
                                >
                                    <span className="hidden sm:inline">
                                        {isEdit ? "حفظ التعديلات" : "إنشاء عنصر"}
                                    </span>
                                    <span className="sm:hidden">
                                        {isEdit ? "حفظ" : "إنشاء"}
                                    </span>
                                    <div className="rtl:-mr-2 sm:rtl:-mr-3 ltr:-ml-2 sm:ltr:-ml-3 h-9 w-9 rounded-2xl bg-blue-800 flex items-center justify-center shrink-0">
                                        <Save size={20} />
                                    </div>
                                </Button>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                            {/* Primary Info */}
                            <div className="lg:col-span-2 space-y-8">
                                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                                    <CardContent className="md:p-5 p-2">
                                        <h3 className="text-xl font-black mb-10 tracking-tight flex items-center gap-3">
                                            <Utensils className="text-blue-500" />{" "}
                                            تفاصيل العنصر
                                        </h3>

                                        <div className="space-y-8">
                                            <TextField
                                                isRequired
                                                isInvalid={!!errors.name}
                                                value={data.name}
                                                onChange={(v) =>
                                                    setData("name", v)
                                                }
                                            >
                                                <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                                    اسم العنصر
                                                </Label>
                                                <Input
                                                    placeholder="ادخل اسم العنصر"
                                                    className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                                />
                                                {errors.name && (
                                                    <p className="text-red-500 text-xs mt-1 pl-2">
                                                        {errors.name}
                                                    </p>
                                                )}
                                            </TextField>

                                            <TextField
                                                isInvalid={!!errors.description}
                                                value={data.description}
                                                onChange={(v) =>
                                                    setData("description", v)
                                                }
                                            >
                                                <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                                    الوصف
                                                </Label>
                                                <TextArea
                                                    placeholder="ادخل وصف العنصر"
                                                    className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 py-4 shadow-none"
                                                />
                                            </TextField>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Option Groups Section */}
                                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                                    <CardContent className="md:p-5 p-2">
                                        <div className="flex justify-between items-center mb-10">
                                            <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                                                <ListChecks className="text-amber-500" />{" "}
                                                الخيارات
                                            </h3>
                                            <Button
                                                onPress={addOptionGroup}
                                                variant="flat"
                                                className="rounded-full font-bold px-6 bg-amber-100/50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                            >
                                                <Plus size={18} /> مجموعة جديدة
                                            </Button>
                                        </div>

                                        <div className="space-y-6">
                                            {data.option_groups.length === 0 ? (
                                                <div className="bg-zinc-50 dark:bg-zinc-800/30 rounded-4xl p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                                    <ListChecks className="mx-auto w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-4" />
                                                    <p className="font-bold text-zinc-400">
                                                        لا يوجد خيارات
                                                    </p>
                                                    <p className="text-xs text-zinc-400 mt-1">
                                                        مثال: الحجم (نص دجاجة،
                                                        ربع دجاجة، دجاجة كاملة)
                                                    </p>
                                                </div>
                                            ) : (
                                                data.option_groups.map(
                                                    (group, groupIndex) => (
                                                        <div
                                                            key={groupIndex}
                                                            className="bg-zinc-50/50 dark:bg-zinc-800/20 p-6 rounded-4xl space-y-4"
                                                        >
                                                            <div className="flex gap-4 items-end">
                                                                <div className="flex-1">
                                                                    <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                                        اسم
                                                                        المجموعة
                                                                    </label>
                                                                    <input
                                                                        placeholder="مثال: الحجم"
                                                                        value={
                                                                            group.name
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateOptionGroup(
                                                                                groupIndex,
                                                                                "name",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none shadow-none"
                                                                    />
                                                                </div>
                                                                <Button
                                                                    isIconOnly
                                                                    variant="ghost"
                                                                    onPress={() =>
                                                                        removeOptionGroup(
                                                                            groupIndex,
                                                                        )
                                                                    }
                                                                    className="rounded-full text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30"
                                                                >
                                                                    <Trash2
                                                                        size={
                                                                            20
                                                                        }
                                                                    />
                                                                </Button>
                                                            </div>

                                                            <div className="pr-4 space-y-3">
                                                                <div className="flex justify-between items-center">
                                                                    <label className="text-xs font-bold text-amber-600 dark:text-amber-400">
                                                                        الخيارات
                                                                        (اختيار
                                                                        واحد
                                                                        فقط)
                                                                    </label>
                                                                    <Button
                                                                        size="sm"
                                                                        variant="flat"
                                                                        onPress={() =>
                                                                            addOption(
                                                                                groupIndex,
                                                                            )
                                                                        }
                                                                        className="rounded-full text-xs font-bold px-4 bg-amber-100/50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                                                    >
                                                                        <Plus
                                                                            size={
                                                                                14
                                                                            }
                                                                        />{" "}
                                                                        خيار
                                                                    </Button>
                                                                </div>

                                                                {group.options.map(
                                                                    (
                                                                        option,
                                                                        optionIndex,
                                                                    ) => (
                                                                        <div
                                                                            key={
                                                                                optionIndex
                                                                            }
                                                                            className="flex gap-3 items-center"
                                                                        >
                                                                            <div className="w-3 h-3 rounded-full border-2 border-amber-400 shrink-0" />
                                                                            <input
                                                                                placeholder="اسم الخيار"
                                                                                value={
                                                                                    option.name
                                                                                }
                                                                                onChange={(
                                                                                    e,
                                                                                ) =>
                                                                                    updateOption(
                                                                                        groupIndex,
                                                                                        optionIndex,
                                                                                        "name",
                                                                                        e
                                                                                            .target
                                                                                            .value,
                                                                                    )
                                                                                }
                                                                                className="flex-1 bg-white dark:bg-zinc-800 rounded-xl h-10 px-4 text-sm border-none outline-none shadow-none"
                                                                            />
                                                                            <div className="w-28">
                                                                                <input
                                                                                    placeholder="0.00"
                                                                                    type="number"
                                                                                    value={
                                                                                        option.price
                                                                                    }
                                                                                    onChange={(
                                                                                        e,
                                                                                    ) =>
                                                                                        updateOption(
                                                                                            groupIndex,
                                                                                            optionIndex,
                                                                                            "price",
                                                                                            e
                                                                                                .target
                                                                                                .value,
                                                                                        )
                                                                                    }
                                                                                    className="w-full bg-white dark:bg-zinc-800 rounded-xl h-10 px-3 text-sm border-none outline-none shadow-none"
                                                                                />
                                                                            </div>
                                                                            {group
                                                                                .options
                                                                                .length >
                                                                                1 && (
                                                                                <Button
                                                                                    isIconOnly
                                                                                    size="sm"
                                                                                    variant="ghost"
                                                                                    onPress={() =>
                                                                                        removeOption(
                                                                                            groupIndex,
                                                                                            optionIndex,
                                                                                        )
                                                                                    }
                                                                                    className="rounded-full text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30"
                                                                                >
                                                                                    <Trash2
                                                                                        size={
                                                                                            16
                                                                                        }
                                                                                    />
                                                                                </Button>
                                                                            )}
                                                                        </div>
                                                                    ),
                                                                )}
                                                            </div>
                                                        </div>
                                                    ),
                                                )
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Modifiers Section */}
                                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                                    <CardContent className="md:p-5 p-2">
                                        <div className="flex justify-between items-center mb-10">
                                            <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                                                <Layers className="text-purple-500" />{" "}
                                                اضافات
                                            </h3>
                                            <Button
                                                onPress={addModifier}
                                                variant="flat"
                                                className="rounded-full font-bold px-6 bg-purple-100/50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                            >
                                                <Plus size={18} /> اضافة جديدة
                                            </Button>
                                        </div>

                                        <div className="space-y-6">
                                            {data.modifiers.length === 0 ? (
                                                <div className="bg-zinc-50 dark:bg-zinc-800/30 rounded-4xl p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                                    <Layers className="mx-auto w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-4" />
                                                    <p className="font-bold text-zinc-400">
                                                        لا يوجد اضافات
                                                    </p>
                                                    <p className="text-xs text-zinc-400 mt-1">
                                                        مثال: صوص حار، كوكاكولا،
                                                        اضافي حار
                                                    </p>
                                                </div>
                                            ) : (
                                                data.modifiers.map(
                                                    (modifier, index) => (
                                                        <div
                                                            key={index}
                                                            className="bg-zinc-50/50 dark:bg-zinc-800/20 p-6 rounded-4xl space-y-4"
                                                        >
                                                            <div className="flex gap-4 items-end">
                                                                <div className="flex-1">
                                                                    <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                                        اسم
                                                                        الاضافة
                                                                    </label>
                                                                    <input
                                                                        placeholder="ادخل اسم الاضافة"
                                                                        value={
                                                                            modifier.name
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateModifier(
                                                                                index,
                                                                                "name",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none shadow-none"
                                                                    />
                                                                </div>
                                                                <div className="w-32">
                                                                    <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                                        سعر
                                                                        اضافي
                                                                    </label>
                                                                    <input
                                                                        placeholder="0.00"
                                                                        type="number"
                                                                        value={
                                                                            modifier.extra_price
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateModifier(
                                                                                index,
                                                                                "extra_price",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none shadow-none"
                                                                    />
                                                                </div>
                                                                <div className="w-24">
                                                                    <label className="text-xs font-bold text-zinc-500 mb-1 block">
                                                                        الحد
                                                                        الاقصى
                                                                    </label>
                                                                    <input
                                                                        placeholder="1"
                                                                        type="number"
                                                                        value={
                                                                            modifier.max_selection
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateModifier(
                                                                                index,
                                                                                "max_selection",
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        className="w-full bg-white dark:bg-zinc-800 rounded-2xl h-12 px-4 text-sm border-none outline-none shadow-none"
                                                                    />
                                                                </div>
                                                                <Button
                                                                    isIconOnly
                                                                    variant="ghost"
                                                                    onPress={() =>
                                                                        removeModifier(
                                                                            index,
                                                                        )
                                                                    }
                                                                    className="rounded-full text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30"
                                                                >
                                                                    <Trash2
                                                                        size={
                                                                            20
                                                                        }
                                                                    />
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    ),
                                                )
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <div className="space-y-8">
                                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[1.5rem] shadow-none">
                                    <CardContent className="md:p-5 p-2 space-y-10">
                                        <h3 className="text-xl font-black tracking-tight flex items-center gap-3">
                                            <Tag className="text-emerald-500" />{" "}
                                            الاعدادات الاساسية
                                        </h3>

                                        <CategoryAutocomplete
                                            categories={categories}
                                            selectedId={data.category_id}
                                            onSelect={(id) =>
                                                setData("category_id", id)
                                            }
                                            onCreateNew={(prefill) => {
                                                setNewCategoryName(prefill);
                                                setShowCategoryModal(true);
                                            }}
                                            error={errors.category_id}
                                        />

                                        <TextField
                                            isRequired
                                            isInvalid={!!errors.base_price}
                                            value={String(data.base_price)}
                                            onChange={(v) =>
                                                setData("base_price", v)
                                            }
                                        >
                                            <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                                السعر الاساسي
                                            </Label>
                                            <div className="flex items-center bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-12 px-4">
                                                <DollarSign
                                                    size={20}
                                                    className="text-zinc-400 mr-2"
                                                />
                                                <Input
                                                    placeholder="0.00"
                                                    type="number"
                                                    className="bg-transparent h-full flex-1 shadow-none"
                                                />
                                            </div>
                                            {errors.base_price && (
                                                <p className="text-red-500 text-xs mt-1 pl-2">
                                                    {errors.base_price}
                                                </p>
                                            )}
                                        </TextField>

                                        <TextField
                                            value={data.sku}
                                            onChange={(v) => setData("sku", v)}
                                        >
                                            <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                                الباركود / SKU
                                            </Label>
                                            <Input
                                                placeholder="اختياري"
                                                className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                            />
                                        </TextField>

                                        <div className="p-2">
                                            <Checkbox
                                                isSelected={data.is_available}
                                                onChange={(v) =>
                                                    setData("is_available", v)
                                                }
                                            >
                                                <CheckboxControl>
                                                    <CheckboxIndicator />
                                                </CheckboxControl>
                                                <span className="font-black text-zinc-800 dark:text-zinc-200 uppercase tracking-widest text-xs">
                                                    متاح
                                                </span>
                                            </Checkbox>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Image Upload */}
                                <div
                                    onDrop={handleDrop}
                                    onDragOver={handleDragOver}
                                    onDragLeave={handleDragLeave}
                                    onClick={() =>
                                        fileInputRef.current?.click()
                                    }
                                    className={`relative h-64 rounded-[1.5rem] border-2 border-dashed flex flex-col items-center justify-center gap-4 text-center p-8 group overflow-hidden cursor-pointer transition-all ${
                                        isDragging
                                            ? "border-blue-400 bg-blue-50 dark:bg-blue-900/20"
                                            : "border-zinc-200 dark:border-zinc-800 bg-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                    }`}
                                >
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept="image/*"
                                        className="hidden"
                                        onChange={(e) =>
                                            handleImageSelect(e.target.files[0])
                                        }
                                    />
                                    {imagePreview ? (
                                        <>
                                            <img
                                                src={imagePreview}
                                                alt="معاينة"
                                                className="absolute inset-0 w-full h-full object-cover rounded-[1.3rem]"
                                            />
                                            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-[1.3rem] flex items-center justify-center">
                                                <div className="opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                                    <Button
                                                        isIconOnly
                                                        variant="flat"
                                                        className="rounded-full bg-white/90 text-zinc-700"
                                                        onPress={(e) => {
                                                            e.stopPropagation?.();
                                                            fileInputRef.current?.click();
                                                        }}
                                                    >
                                                        <Upload size={18} />
                                                    </Button>
                                                    <Button
                                                        isIconOnly
                                                        variant="flat"
                                                        className="rounded-full bg-red-500/90 text-white"
                                                        onPress={(e) => {
                                                            e.stopPropagation?.();
                                                            removeImage();
                                                        }}
                                                    >
                                                        <Trash2 size={18} />
                                                    </Button>
                                                </div>
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <div className="w-16 h-16 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                <ImageIcon className="text-zinc-400 group-hover:text-zinc-600 transition-colors" />
                                            </div>
                                            <div>
                                                <p className="font-black text-sm uppercase tracking-widest text-zinc-500">
                                                    صورة العنصر
                                                </p>
                                                <p className="text-xs text-zinc-400 mt-1 font-bold">
                                                    اسحب وأفلت الصورة هنا أو
                                                    انقر للاختيار
                                                </p>
                                            </div>
                                        </>
                                    )}
                                </div>
                                {errors.image && (
                                    <p className="text-red-500 text-xs mt-1 pl-2">
                                        {errors.image}
                                    </p>
                                )}
                            </div>
                        </div>
                    </form>
                    )}
                </div>
            </div>
            <CreateCategoryModal
                isOpen={showCategoryModal}
                onClose={() => setShowCategoryModal(false)}
                onCreated={(category) => {
                    setCategories((prev) => [...prev, category]);
                    setData("category_id", String(category.id));
                }}
                initialName={newCategoryName}
                csrfToken={csrf_token}
            />
        </AdminLayout>
    );
}
