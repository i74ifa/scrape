import { useEffect, useRef, useState } from "react";
import { router } from "@inertiajs/react";
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
} from "@heroui/react";
import { Plus, Search, Pencil, Trash2, Package, Boxes } from "lucide-react";

const money = (v) =>
    v == null ? "—" : Number(v).toLocaleString("ar", { maximumFractionDigits: 2 });

export default function Index({ products, filters = {} }) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [deleteTarget, setDeleteTarget] = useState(null);

    const rows = products.data ?? [];

    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route("admin.catalog.products.index"),
                { search: search || undefined },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ["products", "filters"],
                },
            );
        }, 350);
        return () => clearTimeout(t);
    }, [search]);

    const confirmDelete = () => {
        if (!deleteTarget) return;
        router.delete(route("admin.catalog.products.destroy", deleteTarget.id), {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <AdminLayout title="المنتجات">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                        {products.total ?? rows.length} منتج
                    </h2>
                    <div className="flex items-center gap-3">
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
                            onPress={() =>
                                router.visit(route("admin.catalog.products.create"))
                            }
                        >
                            إضافة منتج
                        </Button>
                    </div>
                </div>

                {/* Table */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Products Table">
                                <TableHeader className="text-right">
                                    <TableColumn
                                        className="rtl:text-right"
                                        isRowHeader
                                    >
                                        المنتج
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        العلامة التجارية
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        السعر
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        النوع
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
                                            <Package className="mx-auto w-14 h-14 text-zinc-300 mb-3" />
                                            <p className="font-black text-lg text-zinc-400">
                                                لا توجد منتجات بعد.
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
                                                <div className="flex items-center gap-3">
                                                    {item.image ? (
                                                        <img
                                                            src={item.image}
                                                            alt=""
                                                            className="w-10 h-10 rounded-xl object-cover shrink-0 border border-zinc-100 dark:border-zinc-800"
                                                        />
                                                    ) : (
                                                        <div className="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                                            <Package className="w-4 h-4 text-zinc-300" />
                                                        </div>
                                                    )}
                                                    <div className="flex flex-col">
                                                        <p className="font-semibold text-sm">
                                                            {item.name}
                                                        </p>
                                                        {item.sku && (
                                                            <span className="text-xs text-zinc-400 font-mono">
                                                                {item.sku}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-sm text-zinc-500">
                                                    {item.brand?.name ?? "—"}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    {item.sale_price ? (
                                                        <>
                                                            <span className="font-bold text-sm text-emerald-600">
                                                                {money(
                                                                    item.sale_price,
                                                                )}
                                                            </span>
                                                            <span className="text-xs text-zinc-400 line-through">
                                                                {money(
                                                                    item.price,
                                                                )}
                                                            </span>
                                                        </>
                                                    ) : (
                                                        <span className="font-bold text-sm">
                                                            {money(item.price)}
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {item.has_variants ? (
                                                    <Chip
                                                        size="sm"
                                                        variant="flat"
                                                        color="primary"
                                                        startContent={
                                                            <Boxes className="w-3 h-3" />
                                                        }
                                                    >
                                                        متعدد
                                                    </Chip>
                                                ) : (
                                                    <Chip
                                                        size="sm"
                                                        variant="flat"
                                                        color="default"
                                                    >
                                                        بسيط
                                                    </Chip>
                                                )}
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
                                                        ? "مفعّل"
                                                        : "معطّل"}
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
                                                            router.visit(
                                                                route(
                                                                    "admin.catalog.products.edit",
                                                                    item.id,
                                                                ),
                                                            )
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
                {products.links && products.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {products.links.map((link, i) => (
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
                                        only: ["products", "filters"],
                                    })
                                }
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>

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
                                حذف المنتج
                            </Modal.Header>
                            <Modal.Body>
                                <p className="text-sm text-zinc-500 p-2">
                                    هل أنت متأكد من حذف المنتج{" "}
                                    <span className="font-bold text-zinc-700 dark:text-zinc-200">
                                        {deleteTarget?.name}
                                    </span>
                                    ؟ سيتم حذف خياراته ومتغيّراته.
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
