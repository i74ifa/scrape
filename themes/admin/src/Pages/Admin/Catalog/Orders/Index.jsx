import { useEffect, useRef, useState } from "react";
import { router, Link } from "@inertiajs/react";
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
} from "@heroui/react";
import { Search, Receipt, ChevronLeft } from "lucide-react";

export default function Index({ orders, filters = {}, statuses = {} }) {
    const rows = orders.data ?? [];
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");

    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        const t = setTimeout(() => {
            router.get(
                route("admin.catalog.orders.index"),
                { search: search || undefined, status: status || undefined },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    only: ["orders", "filters"],
                },
            );
        }, 350);
        return () => clearTimeout(t);
    }, [search, status]);

    const statusEntries = [["", "الكل"], ...Object.entries(statuses)];

    return (
        <AdminLayout title="طلبات الكتالوج">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                        {orders.total ?? rows.length} طلب
                    </h2>
                    <div className="flex items-center gap-3">
                        <div className="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-full p-1 flex-wrap">
                            {statusEntries.map(([key, labelText]) => (
                                <button
                                    key={key || "all"}
                                    type="button"
                                    onClick={() => setStatus(key)}
                                    className={`text-xs font-bold px-3 h-9 rounded-full transition-colors ${
                                        status === key
                                            ? "bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white"
                                            : "text-zinc-500"
                                    }`}
                                >
                                    {labelText}
                                </button>
                            ))}
                        </div>
                        <div className="relative">
                            <Search className="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="بحث بالرقم أو العميل..."
                                className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-11 pr-10 pl-4 text-sm border-none outline-none w-60"
                            />
                        </div>
                    </div>
                </div>

                {/* Table */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Catalog Orders Table">
                                <TableHeader className="text-right">
                                    <TableColumn className="rtl:text-right" isRowHeader>
                                        رقم الطلب
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">العميل</TableColumn>
                                    <TableColumn className="rtl:text-right">العناصر</TableColumn>
                                    <TableColumn className="rtl:text-right">الإجمالي</TableColumn>
                                    <TableColumn className="rtl:text-right">الحالة</TableColumn>
                                    <TableColumn className="rtl:text-right">التاريخ</TableColumn>
                                    <TableColumn className="rtl:text-right"> </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={rows}
                                    renderEmptyState={() => (
                                        <div className="text-center py-16">
                                            <Receipt className="mx-auto w-14 h-14 text-zinc-300 mb-3" />
                                            <p className="font-black text-lg text-zinc-400">
                                                لا توجد طلبات بعد.
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
                                                <span className="font-mono text-xs font-bold" dir="ltr">
                                                    {item.code}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-semibold">
                                                        {item.customer ?? "—"}
                                                    </span>
                                                    {item.phone && (
                                                        <span className="text-xs text-zinc-400" dir="ltr">
                                                            {item.phone}
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-sm text-zinc-500">
                                                    {item.items_count} × {item.total_quantity} قطعة
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <span className="font-bold text-sm">{item.total}</span>
                                            </TableCell>
                                            <TableCell>
                                                <Chip size="sm" variant="flat" color={item.status_color}>
                                                    {item.status_label}
                                                </Chip>
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-xs text-zinc-400" dir="ltr">
                                                    {item.created_at}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <Link href={route("admin.catalog.orders.show", item.id)}>
                                                    <Button size="sm" variant="flat" color="primary"
                                                        endContent={<ChevronLeft className="w-3.5 h-3.5" />}>
                                                        تفاصيل
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </TableContent>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {orders.links && orders.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {orders.links.map((link, i) => (
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
                                        only: ["orders", "filters"],
                                    })
                                }
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
