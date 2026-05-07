import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { useCallback, useRef, useState } from "react";
import {
    Search,
    Receipt,
    Clock,
    CheckCircle2,
    XCircle,
    RotateCcw,
    Banknote,
} from "lucide-react";
import { Card, CardContent, Pagination } from "@heroui/react";

const STATUS_MAP = {
    pending_payment: { label: "بانتظار الدفع", color: "amber", icon: Clock },
    paid: { label: "مدفوع", color: "emerald", icon: CheckCircle2 },
    partially_refunded: { label: "مسترد جزئيًا", color: "indigo", icon: RotateCcw },
    refunded: { label: "مسترد", color: "rose", icon: RotateCcw },
    failed: { label: "فشل", color: "red", icon: XCircle },
};

const formatMoney = (n) =>
    new Intl.NumberFormat("ar-IQ", { maximumFractionDigits: 2 }).format(
        Number(n) || 0,
    );

function StatusBadge({ status }) {
    const meta = STATUS_MAP[status] || STATUS_MAP.pending_payment;
    const Icon = meta.icon;
    return (
        <span
            className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-${meta.color}-100 dark:bg-${meta.color}-900/30 text-${meta.color}-600 dark:text-${meta.color}-400`}
        >
            <Icon size={12} />
            {meta.label}
        </span>
    );
}

function CheckoutRow({ checkout }) {
    return (
        <tr className="border-b border-zinc-100 dark:border-zinc-800 last:border-b-0">
            <td className="px-4 py-3">
                <div className="font-bold text-sm">{checkout.code}</div>
            </td>
            <td className="px-4 py-3">
                {checkout.user ? (
                    <div>
                        <div className="font-bold text-sm">
                            {checkout.user.name || "—"}
                        </div>
                        <div className="text-xs text-zinc-400">
                            {checkout.user.phone || checkout.user.email}
                        </div>
                    </div>
                ) : (
                    <span className="text-xs text-zinc-400">—</span>
                )}
            </td>
            <td className="px-4 py-3">
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    <Banknote size={12} />
                    {checkout.payment_method || "—"}
                </span>
            </td>
            <td className="px-4 py-3 text-sm font-bold text-zinc-500 text-center">
                {checkout.orders_count ?? checkout.orders?.length ?? 0}
            </td>
            <td className="px-4 py-3 text-sm font-black">
                {formatMoney(checkout.grand_total)}
            </td>
            <td className="px-4 py-3">
                <StatusBadge status={checkout.status} />
            </td>
            <td className="px-4 py-3 text-xs text-zinc-400 whitespace-nowrap">
                {checkout.created_at
                    ? new Date(checkout.created_at).toLocaleDateString("ar-IQ")
                    : ""}
            </td>
        </tr>
    );
}

export default function Index({ checkoutOrders, statuses = [], filters = {} }) {
    const [searchQuery, setSearchQuery] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(filters.status || "");
    const debounceTimer = useRef(null);

    const list = checkoutOrders?.data || [];

    const handleSearch = useCallback(
        (value) => {
            setSearchQuery(value);
            clearTimeout(debounceTimer.current);
            debounceTimer.current = setTimeout(() => {
                router.reload({
                    data: { search: value, status: statusFilter, page: 1 },
                    only: ["checkoutOrders"],
                    preserveScroll: true,
                });
            }, 300);
        },
        [statusFilter],
    );

    const handleStatusFilter = (value) => {
        setStatusFilter(value);
        router.reload({
            data: { search: searchQuery, status: value, page: 1 },
            only: ["checkoutOrders"],
            preserveScroll: true,
        });
    };

    const goToPage = (page) => {
        router.reload({
            data: { search: searchQuery, status: statusFilter, page },
            only: ["checkoutOrders"],
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="طلبات الدفع">
            <div className="flex flex-col gap-6 pb-10">
                <div className="flex justify-between items-center px-2">
                    <h4 className="text-base font-bold text-zinc-500">
                        إجمالي {checkoutOrders?.total ?? list.length} طلب دفع
                    </h4>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-5">
                        <div className="flex flex-wrap gap-3 items-center mb-5">
                            <div className="relative flex-1 max-w-sm">
                                <Search
                                    size={16}
                                    className="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"
                                />
                                <input
                                    type="text"
                                    placeholder="بحث برقم الطلب أو العميل..."
                                    value={searchQuery}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-12 pr-10 pl-4 text-sm border-none outline-none shadow-none"
                                />
                            </div>
                            <div className="flex flex-wrap gap-1">
                                <button
                                    onClick={() => handleStatusFilter("")}
                                    className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                        !statusFilter
                                            ? "bg-blue-600 text-white"
                                            : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400"
                                    }`}
                                >
                                    الكل
                                </button>
                                {statuses.map((s) => (
                                    <button
                                        key={s.value}
                                        onClick={() => handleStatusFilter(s.value)}
                                        className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                            statusFilter === s.value
                                                ? "bg-blue-600 text-white"
                                                : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400"
                                        }`}
                                    >
                                        {STATUS_MAP[s.value]?.label || s.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {list.length === 0 ? (
                            <div className="bg-zinc-50 dark:bg-zinc-900/50 rounded-4xl p-20 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                <Receipt className="mx-auto w-16 h-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                                <p className="font-black text-xl text-zinc-400">
                                    لا توجد طلبات دفع
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-right">
                                    <thead>
                                        <tr className="border-b border-zinc-200 dark:border-zinc-800">
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                الرقم
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                العميل
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                طريقة الدفع
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide text-center">
                                                عدد الطلبات
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                الإجمالي
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                الحالة
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                التاريخ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {list.map((checkout) => (
                                            <CheckoutRow
                                                key={checkout.id}
                                                checkout={checkout}
                                            />
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {checkoutOrders?.last_page > 1 && (
                            <div className="mt-6 flex justify-center">
                                <Pagination>
                                    <Pagination.Content>
                                        <Pagination.Item>
                                            <Pagination.Previous
                                                onPress={() =>
                                                    goToPage(
                                                        Math.max(
                                                            1,
                                                            checkoutOrders.current_page - 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={
                                                    checkoutOrders.current_page === 1
                                                }
                                                className="rounded-full font-bold"
                                            >
                                                <Pagination.PreviousIcon />
                                                <span>السابق</span>
                                            </Pagination.Previous>
                                        </Pagination.Item>
                                        {Array.from(
                                            { length: checkoutOrders.last_page },
                                            (_, i) => i + 1,
                                        ).map((p) => (
                                            <Pagination.Item key={p}>
                                                <Pagination.Link
                                                    onPress={() => goToPage(p)}
                                                    isCurrent={
                                                        p === checkoutOrders.current_page
                                                    }
                                                    className="rounded-full font-bold"
                                                >
                                                    {p}
                                                </Pagination.Link>
                                            </Pagination.Item>
                                        ))}
                                        <Pagination.Item>
                                            <Pagination.Next
                                                onPress={() =>
                                                    goToPage(
                                                        Math.min(
                                                            checkoutOrders.last_page,
                                                            checkoutOrders.current_page + 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={
                                                    checkoutOrders.current_page ===
                                                    checkoutOrders.last_page
                                                }
                                                className="rounded-full font-bold"
                                            >
                                                <span>التالي</span>
                                                <Pagination.NextIcon />
                                            </Pagination.Next>
                                        </Pagination.Item>
                                    </Pagination.Content>
                                </Pagination>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
