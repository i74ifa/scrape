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
    Label,
    FieldError,
    Checkbox,
} from "@heroui/react";
import {
    Search,
    Send,
    Smartphone,
    Mail,
    Phone,
    Bell,
    ImagePlus,
    X,
} from "lucide-react";

export default function Index({ users, filters = {} }) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [withDeviceOnly, setWithDeviceOnly] = useState(
        filters.with_device_only === "1" || filters.with_device_only === true,
    );

    const [target, setTarget] = useState(null); // user being messaged
    const [preview, setPreview] = useState(null); // object-URL for picked file
    const form = useForm({
        title: "",
        body: "",
        image: null,
        url: "",
        mutable_content: true,
    });

    const rows = users.data ?? [];

    // ---- debounced async table search --------------------------------------
    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route("admin.users.index"),
                {
                    search: search || undefined,
                    with_device_only: withDeviceOnly ? 1 : undefined,
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ["users", "filters"],
                },
            );
        }, 350);
        return () => clearTimeout(t);
    }, [search, withDeviceOnly]);

    // ---- send-notification modal -------------------------------------------
    const openModal = (user) => {
        setTarget(user);
        form.clearErrors();
        form.setData({
            title: "",
            body: "",
            image: null,
            url: "",
            mutable_content: true,
        });
        setPreview(null);
    };

    const closeModal = () => {
        setTarget(null);
        form.reset();
        form.clearErrors();
        if (preview) URL.revokeObjectURL(preview);
        setPreview(null);
    };

    const pickImage = (file) => {
        if (!file) return;
        form.setData("image", file);
        if (preview) URL.revokeObjectURL(preview);
        setPreview(URL.createObjectURL(file));
    };

    const clearImage = () => {
        form.setData("image", null);
        if (preview) URL.revokeObjectURL(preview);
        setPreview(null);
    };

    const submit = (e) => {
        e?.preventDefault();
        // image is a File — submit as multipart FormData so PHP receives it.
        form.post(route("admin.users.send-fcm", target.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    };

    return (
        <AdminLayout title="المستخدمين">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                        عرض {users.total ?? rows.length} مستخدم
                    </h2>

                    <div className="flex items-center gap-3 flex-wrap">
                        <button
                            type="button"
                            onClick={() => setWithDeviceOnly((v) => !v)}
                            className={`text-xs font-bold px-3 py-2.5 rounded-2xl transition-colors ${
                                withDeviceOnly
                                    ? "bg-blue-600 text-white"
                                    : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300"
                            }`}
                        >
                            <Smartphone className="w-3.5 h-3.5 inline-block ml-1" />
                            أصحاب أجهزة فقط
                        </button>
                        <div className="relative">
                            <Search className="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="بحث بالاسم أو البريد أو الهاتف..."
                                className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-11 pr-10 pl-4 text-sm border-none outline-none w-64"
                            />
                        </div>
                    </div>
                </div>

                {/* Table */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] overflow-hidden">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Users Table">
                                <TableHeader className="text-right">
                                    <TableColumn
                                        className="rtl:text-right"
                                        isRowHeader
                                    >
                                        الاسم
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        التواصل
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الجهاز
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        تاريخ الإضافة
                                    </TableColumn>
                                    <TableColumn
                                        className="rtl:text-right text-left"
                                        align="end"
                                    >
                                        الإجراءات
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={rows}
                                    renderEmptyState={() => (
                                        <p className="text-center py-10 text-zinc-400">
                                            لا يوجد مستخدمون مطابقون.
                                        </p>
                                    )}
                                >
                                    {(item) => (
                                        <TableRow
                                            id={item.id}
                                            className="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="w-9 h-9 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-sm shrink-0">
                                                        {(
                                                            item.name ||
                                                            item.email ||
                                                            "?"
                                                        )
                                                            .charAt(0)
                                                            .toUpperCase()}
                                                    </div>
                                                    <p className="font-semibold text-sm">
                                                        {item.name || "—"}
                                                    </p>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col gap-1 text-xs text-zinc-500">
                                                    {item.email && (
                                                        <span className="flex items-center gap-1.5">
                                                            <Mail className="w-3 h-3" />
                                                            {item.email}
                                                        </span>
                                                    )}
                                                    {item.phone && (
                                                        <span className="flex items-center gap-1.5">
                                                            <Phone className="w-3 h-3" />
                                                            {item.phone}
                                                        </span>
                                                    )}
                                                    {!item.email &&
                                                        !item.phone && (
                                                            <span className="text-zinc-300">
                                                                —
                                                            </span>
                                                        )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {item.has_device ? (
                                                    <Chip
                                                        size="sm"
                                                        variant="flat"
                                                        color="success"
                                                        startContent={
                                                            <Smartphone className="w-3 h-3" />
                                                        }
                                                    >
                                                        {item.device_mask}
                                                    </Chip>
                                                ) : (
                                                    <span className="text-xs text-zinc-400">
                                                        غير متاح
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-400">
                                                    {item.created_at
                                                        ? new Date(
                                                              item.created_at,
                                                          ).toLocaleDateString()
                                                        : "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 justify-end">
                                                    <Button
                                                        size="sm"
                                                        variant="flat"
                                                        color="primary"
                                                        isDisabled={
                                                            !item.has_device
                                                        }
                                                        startContent={
                                                            <Send className="w-3.5 h-3.5" />
                                                        }
                                                        onPress={() =>
                                                            openModal(item)
                                                        }
                                                    >
                                                        إشعار
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
                {users.links && users.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {users.links.map((link, i) => (
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
                                        only: ["users", "filters"],
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

            {/* Send notification modal */}
            <Modal
                isOpen={!!target}
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
                                    <Bell className="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                إرسال إشعار
                            </Modal.Header>
                            <form onSubmit={submit}>
                                <Modal.Body>
                                    <div className="space-y-4 p-2">
                                        {target && (
                                            <div className="rounded-2xl bg-zinc-100 dark:bg-zinc-800/60 p-3 text-xs text-zinc-600 dark:text-zinc-300 flex items-center gap-2">
                                                <Smartphone className="w-3.5 h-3.5 text-emerald-500" />
                                                إلى:{" "}
                                                <span className="font-bold">
                                                    {target.name ||
                                                        target.email ||
                                                        `#${target.id}`}
                                                </span>
                                            </div>
                                        )}

                                        <Label className="text-xs font-bold text-zinc-500">
                                            العنوان
                                        </Label>
                                        <input
                                            type="text"
                                            value={form.data.title}
                                            onChange={(e) =>
                                                form.setData(
                                                    "title",
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="مثال: عرض جديد!"
                                            className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-2xl h-11 px-4 text-sm border-none outline-none"
                                        />
                                        {form.errors.title && (
                                            <FieldError className="text-xs text-red-500 font-bold px-1">
                                                {form.errors.title}
                                            </FieldError>
                                        )}

                                        <Label className="text-xs font-bold text-zinc-500">
                                            النص
                                        </Label>
                                        <textarea
                                            value={form.data.body}
                                            onChange={(e) =>
                                                form.setData(
                                                    "body",
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="نص الإشعار..."
                                            rows={3}
                                            className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-2xl px-4 py-3 text-sm border-none outline-none resize-none"
                                        />
                                        {form.errors.body && (
                                            <FieldError className="text-xs text-red-500 font-bold px-1">
                                                {form.errors.body}
                                            </FieldError>
                                        )}

                                        <Label className="text-xs font-bold text-zinc-500">
                                            صورة الإشعار (اختياري)
                                        </Label>
                                        <div className="mt-1 flex items-center gap-4">
                                            {preview ? (
                                                <div className="relative w-24 h-24 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-600 shrink-0">
                                                    <img
                                                        src={preview}
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
                                                <label className="w-24 h-24 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-600 flex flex-col items-center justify-center gap-1 text-zinc-400 cursor-pointer hover:border-blue-400 transition-colors shrink-0">
                                                    <ImagePlus className="w-5 h-5" />
                                                    <span className="text-[10px] font-bold">
                                                        اختر صورة
                                                    </span>
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
                                            {preview && (
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

                                        <Label className="text-xs font-bold text-zinc-500">
                                            رابط عند الضغط (اختياري)
                                        </Label>
                                        <input
                                            type="text"
                                            value={form.data.url}
                                            onChange={(e) =>
                                                form.setData(
                                                    "url",
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="/offers"
                                            className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-2xl h-11 px-4 text-sm border-none outline-none"
                                        />
                                        {form.errors.url && (
                                            <FieldError className="text-xs text-red-500 font-bold px-1">
                                                {form.errors.url}
                                            </FieldError>
                                        )}

                                        <label className="flex items-center gap-2 cursor-pointer select-none">
                                            <Checkbox
                                                isSelected={
                                                    form.data.mutable_content
                                                }
                                                onValueChange={(val) =>
                                                    form.setData(
                                                        "mutable_content",
                                                        val,
                                                    )
                                                }
                                                size="sm"
                                            />
                                            <span className="text-xs font-bold text-zinc-600 dark:text-zinc-300">
                                                تفعيل المحتوى القابل للتعديل
                                                (مطلوب لعرض الصور على iOS)
                                            </span>
                                        </label>
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
                                        isDisabled={form.processing}
                                        startContent={
                                            !form.processing && (
                                                <Send className="w-4 h-4" />
                                            )
                                        }
                                    >
                                        إرسال الإشعار
                                    </Button>
                                </Modal.Footer>
                            </form>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>
        </AdminLayout>
    );
}
