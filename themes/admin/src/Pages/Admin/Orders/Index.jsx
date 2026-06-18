import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { useCallback, useMemo, useRef, useState } from "react";
import {
    Search,
    ShoppingCart,
    Package,
    Clock,
    XCircle,
    PackageCheck,
    PackageOpen,
    Plane,
    ShieldCheck,
    RotateCcw,
    Hourglass,
    Eye,
    ArrowLeftCircle,
    Loader2,
} from "lucide-react";
import {
    Card,
    CardContent,
    Pagination,
    Modal,
    Button,
    useOverlayState,
} from "@heroui/react";

const STATUS_MAP = {
    pending: { label: "قيد الانتظار", color: "amber", icon: Clock },
    approved: { label: "تمت الموافقة", color: "blue", icon: ShieldCheck },
    purchasing: { label: "قيد الشراء", color: "indigo", icon: Hourglass },
    purchased: { label: "تم الشراء", color: "violet", icon: PackageCheck },
    ready_to_ship: { label: "جاهز للشحن", color: "sky", icon: PackageOpen },
    customs_clearance: { label: "الجمارك", color: "amber", icon: ShieldCheck },
    shipped: { label: "تم الشحن", color: "blue", icon: Plane },
    delivered: { label: "تم التسليم", color: "emerald", icon: Package },
    cancelled: { label: "ملغي", color: "red", icon: XCircle },
    returned: { label: "مرتجع", color: "rose", icon: RotateCcw },
};

const formatMoney = (n, symbol = "") =>
    `${new Intl.NumberFormat("ar-IQ", { maximumFractionDigits: 2 }).format(Number(n) || 0)} ${symbol}`.trim();

function StatusBadge({ status }) {
    const meta = STATUS_MAP[status] || STATUS_MAP.pending;
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

function OrderRow({ order, nextByStatus, onViewProducts, onAdvance, advancingId }) {
    const symbol = order.platform?.currency_symbol || "";
    const nextValue = nextByStatus[order.status] || null;
    const nextMeta = nextValue ? STATUS_MAP[nextValue] : null;
    const isAdvancing = advancingId === order.id;

    return (
        <tr className="border-b border-zinc-100 dark:border-zinc-800 last:border-b-0">
            <td className="px-4 py-3">
                <div className="font-bold text-sm text-zinc-900 dark:text-white">
                    {order.code}
                </div>
                {order.checkout_order?.code && (
                    <div className="text-xs text-zinc-400 mt-0.5">
                        {order.checkout_order.code}
                    </div>
                )}
            </td>
            <td className="px-4 py-3">
                {order.user ? (
                    <div>
                        <div className="font-bold text-sm">{order.user.name || "—"}</div>
                        <div className="text-xs text-zinc-400">
                            {order.user.phone || order.user.email}
                        </div>
                    </div>
                ) : (
                    <span className="text-xs text-zinc-400">—</span>
                )}
            </td>
            <td className="px-4 py-3">
                {order.platform ? (
                    <div className="flex items-center gap-2">
                        {order.platform.logo && (
                            <img
                                src={order.platform.logo}
                                alt={order.platform.name}
                                className="w-6 h-6 rounded-lg object-contain"
                            />
                        )}
                        <span className="text-sm font-bold">
                            {order.platform.name}
                        </span>
                    </div>
                ) : (
                    <span className="text-xs text-zinc-400">—</span>
                )}
            </td>
            <td className="px-4 py-3 text-sm font-bold text-zinc-500 text-center">
                {order.total_quantity ?? order.items?.length ?? 0}
            </td>
            <td className="px-4 py-3 text-sm font-black">
                {formatMoney(order.grand_total, symbol)}
            </td>
            <td className="px-4 py-3">
                <StatusBadge status={order.status} />
            </td>
            <td className="px-4 py-3 text-xs text-zinc-400 whitespace-nowrap">
                {order.created_at
                    ? new Date(order.created_at).toLocaleDateString("ar-IQ")
                    : ""}
            </td>
            <td className="px-4 py-3">
                <div className="flex items-center gap-2 justify-end">
                    <Button
                        size="sm"
                        variant="flat"
                        className="rounded-full font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300"
                        startContent={<Eye size={14} />}
                        onPress={() => onViewProducts(order)}
                    >
                        المنتجات
                    </Button>
                    <Button
                        size="sm"
                        variant="flat"
                        isDisabled={!nextValue || isAdvancing}
                        className={`rounded-full font-bold ${nextValue ? "bg-blue-600 text-white" : "bg-zinc-100 dark:bg-zinc-800 text-zinc-400"}`}
                        startContent={
                            isAdvancing ? (
                                <Loader2 size={14} className="animate-spin" />
                            ) : (
                                <ArrowLeftCircle size={14} />
                            )
                        }
                        onPress={() => onAdvance(order)}
                    >
                        {nextMeta ? `← ${nextMeta.label}` : "نهاية المسار"}
                    </Button>
                </div>
            </td>
        </tr>
    );
}

function ProductsModal({ state, loading, order }) {
    const symbol = order?.currency_symbol || "";
    const items = order?.items || [];

    return (
        <Modal isOpen={state.isOpen} onOpenChange={state.onOpenChange}>
            <Modal.Backdrop>
                <Modal.Container size="lg" scroll="inside">
                    <Modal.Dialog>
                        <Modal.CloseTrigger />
                        <Modal.Header className="flex flex-col gap-1">
                            <Modal.Heading className="font-black">
                                منتجات الطلب
                            </Modal.Heading>
                            {order?.code && (
                                <span className="text-xs text-zinc-400 font-bold">
                                    {order.code}
                                </span>
                            )}
                        </Modal.Header>
                        <Modal.Body>
                            {loading ? (
                                <div className="flex justify-center py-10">
                                    <Loader2 className="animate-spin text-zinc-400" />
                                </div>
                            ) : items.length === 0 ? (
                                <div className="text-center text-zinc-400 py-10 font-bold">
                                    لا توجد منتجات
                                </div>
                            ) : (
                                <ul className="flex flex-col gap-3">
                                    {items.map((item) => (
                                        <li
                                            key={item.id}
                                            className="flex items-center gap-3 p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50"
                                        >
                                            {item.product?.image ? (
                                                <img
                                                    src={item.product.image}
                                                    alt={item.product.name}
                                                    className="w-14 h-14 rounded-xl object-cover"
                                                />
                                            ) : (
                                                <div className="w-14 h-14 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                                                    <Package size={20} />
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <div className="font-bold text-sm truncate">
                                                    {item.product?.name ||
                                                        `#${item.product?.id ?? "—"}`}
                                                </div>
                                                <div className="text-xs text-zinc-400 mt-0.5">
                                                    {formatMoney(item.price, symbol)} ×{" "}
                                                    {item.quantity}
                                                </div>
                                            </div>
                                            <div className="text-sm font-black">
                                                {formatMoney(item.total, symbol)}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Modal.Body>
                        <Modal.Footer>
                            <Button
                                variant="flat"
                                onPress={state.close}
                                className="rounded-full font-bold"
                            >
                                إغلاق
                            </Button>
                        </Modal.Footer>
                    </Modal.Dialog>
                </Modal.Container>
            </Modal.Backdrop>
        </Modal>
    );
}

export default function Index({ orders, statuses = [], filters = {} }) {
    const [searchQuery, setSearchQuery] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(filters.status || "");
    const [advancingId, setAdvancingId] = useState(null);
    const [modalLoading, setModalLoading] = useState(false);
    const [modalOrder, setModalOrder] = useState(null);
    const productsModal = useOverlayState();
    const debounceTimer = useRef(null);

    const ordersList = orders?.data || [];

    const nextByStatus = useMemo(
        () =>
            statuses.reduce((acc, s) => {
                acc[s.value] = s.next || null;
                return acc;
            }, {}),
        [statuses],
    );

    const handleSearch = useCallback(
        (value) => {
            setSearchQuery(value);
            clearTimeout(debounceTimer.current);
            debounceTimer.current = setTimeout(() => {
                router.reload({
                    data: { search: value, status: statusFilter, page: 1 },
                    only: ["orders"],
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
            only: ["orders"],
            preserveScroll: true,
        });
    };

    const goToPage = (page) => {
        router.reload({
            data: { search: searchQuery, status: statusFilter, page },
            only: ["orders"],
            preserveScroll: true,
        });
    };

    const handleViewProducts = async (order) => {
        setModalOrder({ id: order.id, code: order.code, items: [] });
        setModalLoading(true);
        productsModal.open();
        try {
            const res = await fetch(route("admin.orders.products", order.id), {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });
            const data = await res.json();
            setModalOrder(data.order);
        } catch (e) {
            setModalOrder({ id: order.id, code: order.code, items: [] });
        } finally {
            setModalLoading(false);
        }
    };

    const handleAdvance = (order) => {
        const next = nextByStatus[order.status];
        if (!next) return;

        setAdvancingId(order.id);
        router.post(
            route("admin.orders.next-status", order.id),
            {},
            {
                preserveScroll: true,
                only: ["orders", "flash"],
                onFinish: () => setAdvancingId(null),
            },
        );
    };

    return (
        <AdminLayout title="الطلبات">
            <div className="flex flex-col gap-6 pb-10">
                <div className="flex justify-between items-center px-2">
                    <h4 className="text-base font-bold text-zinc-500">
                        إجمالي {orders?.total ?? ordersList.length} طلب
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

                        {ordersList.length === 0 ? (
                            <div className="bg-zinc-50 dark:bg-zinc-900/50 rounded-4xl p-20 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                <ShoppingCart className="mx-auto w-16 h-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                                <p className="font-black text-xl text-zinc-400">
                                    لا توجد طلبات
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-right">
                                    <thead>
                                        <tr className="border-b border-zinc-200 dark:border-zinc-800">
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                رقم الطلب
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                العميل
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                المنصة
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide text-center">
                                                الكمية
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
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide text-end">
                                                إجراءات
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {ordersList.map((order) => (
                                            <OrderRow
                                                key={order.id}
                                                order={order}
                                                nextByStatus={nextByStatus}
                                                onViewProducts={handleViewProducts}
                                                onAdvance={handleAdvance}
                                                advancingId={advancingId}
                                            />
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {orders?.last_page > 1 && (
                            <div className="mt-6 flex justify-center">
                                <Pagination>
                                    <Pagination.Content>
                                        <Pagination.Item>
                                            <Pagination.Previous
                                                onPress={() =>
                                                    goToPage(
                                                        Math.max(
                                                            1,
                                                            orders.current_page - 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={orders.current_page === 1}
                                                className="rounded-full font-bold"
                                            >
                                                <Pagination.PreviousIcon />
                                                <span>السابق</span>
                                            </Pagination.Previous>
                                        </Pagination.Item>
                                        {Array.from(
                                            { length: orders.last_page },
                                            (_, i) => i + 1,
                                        ).map((p) => (
                                            <Pagination.Item key={p}>
                                                <Pagination.Link
                                                    onPress={() => goToPage(p)}
                                                    isCurrent={p === orders.current_page}
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
                                                            orders.last_page,
                                                            orders.current_page + 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={
                                                    orders.current_page === orders.last_page
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

            <ProductsModal
                state={productsModal}
                loading={modalLoading}
                order={modalOrder}
            />
        </AdminLayout>
    );
}
