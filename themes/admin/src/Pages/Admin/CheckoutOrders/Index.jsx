import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { useCallback, useMemo, useRef, useState } from "react";
import {
    Search,
    Receipt,
    Clock,
    CheckCircle2,
    XCircle,
    RotateCcw,
    Banknote,
    Eye,
    ArrowLeftCircle,
    Loader2,
    Package,
    PackageCheck,
    PackageOpen,
    Plane,
    ShieldCheck,
    Hourglass,
    ExternalLink,
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
    pending_payment: { label: "بانتظار الدفع", color: "amber", icon: Clock },
    paid: { label: "مدفوع", color: "emerald", icon: CheckCircle2 },
    partially_refunded: { label: "مسترد جزئيًا", color: "indigo", icon: RotateCcw },
    refunded: { label: "مسترد", color: "rose", icon: RotateCcw },
    failed: { label: "فشل", color: "red", icon: XCircle },
};

const ORDER_STATUS_MAP = {
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

function OrderStatusBadge({ status }) {
    const meta = ORDER_STATUS_MAP[status] || ORDER_STATUS_MAP.pending;
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

function CheckoutRow({ checkout, nextByStatus, onViewProducts, onAdvance, advancingId }) {
    const nextValue = nextByStatus[checkout.status] || null;
    const nextMeta = nextValue ? STATUS_MAP[nextValue] : null;
    const isAdvancing = advancingId === checkout.id;

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
            <td className="px-4 py-3">
                <div className="flex items-center gap-2 justify-end">
                    <Button
                        size="sm"
                        variant="flat"
                        className="rounded-full font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300"
                        startContent={<Eye size={14} />}
                        onPress={() => onViewProducts(checkout)}
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
                        onPress={() => onAdvance(checkout)}
                    >
                        {nextMeta ? `← ${nextMeta.label}` : "نهاية المسار"}
                    </Button>
                </div>
            </td>
        </tr>
    );
}

function OrderGroup({ order, onAdvanceOrder, advancingOrderId }) {
    const symbol = order.currency_symbol || "";
    const platform = order.platform;
    const nextValue = order.status_next || null;
    const nextMeta = nextValue ? ORDER_STATUS_MAP[nextValue] : null;
    const isAdvancing = advancingOrderId === order.id;
    const items = order.items || [];

    return (
        <div className="rounded-3xl bg-zinc-50 dark:bg-zinc-900/50 overflow-hidden">
            {/* scraper header + actions */}
            <div className="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-zinc-100 dark:border-zinc-800">
                <div className="flex items-center gap-3 min-w-0">
                    {platform?.logo ? (
                        <img
                            src={platform.logo}
                            alt={platform.name}
                            className="w-9 h-9 rounded-xl object-contain bg-white dark:bg-zinc-800 p-1"
                        />
                    ) : (
                        <div className="w-9 h-9 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                            <Package size={18} />
                        </div>
                    )}
                    <div className="min-w-0">
                        <div className="font-bold text-sm truncate">
                            {platform?.name || "—"}
                        </div>
                        <div className="text-xs text-zinc-400 truncate">
                            {order.code}
                        </div>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <OrderStatusBadge status={order.status} />
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
                        onPress={() => onAdvanceOrder(order)}
                    >
                        {nextMeta ? `← ${nextMeta.label}` : "نهاية المسار"}
                    </Button>
                </div>
            </div>

            {/* products table */}
            {items.length === 0 ? (
                <div className="text-center text-zinc-400 py-6 font-bold text-sm">
                    لا توجد منتجات
                </div>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-right">
                        <thead>
                            <tr className="border-b border-zinc-100 dark:border-zinc-800">
                                <th className="px-4 py-2 text-xs font-bold text-zinc-400">
                                    المنتج
                                </th>
                                <th className="px-4 py-2 text-xs font-bold text-zinc-400 text-center">
                                    الكمية
                                </th>
                                <th className="px-4 py-2 text-xs font-bold text-zinc-400">
                                    السعر
                                </th>
                                <th className="px-4 py-2 text-xs font-bold text-zinc-400">
                                    الإجمالي
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map((item) => (
                                <tr
                                    key={item.id}
                                    className="border-b border-zinc-100 dark:border-zinc-800 last:border-b-0"
                                >
                                    <td className="px-4 py-3">
                                        <a
                                            href={item.product?.url || "#"}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className={`flex items-center gap-3 group ${item.product?.url ? "" : "pointer-events-none"}`}
                                        >
                                            {item.product?.image ? (
                                                <img
                                                    src={item.product.image}
                                                    alt={item.product.name}
                                                    className="w-12 h-12 rounded-xl object-cover shrink-0"
                                                />
                                            ) : (
                                                <div className="w-12 h-12 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 shrink-0">
                                                    <Package size={18} />
                                                </div>
                                            )}
                                            <span className="font-bold text-sm group-hover:text-blue-600 transition-colors inline-flex items-center gap-1.5">
                                                {item.product?.name ||
                                                    `#${item.product?.id ?? "—"}`}
                                                {item.product?.url && (
                                                    <ExternalLink
                                                        size={13}
                                                        className="text-zinc-400 shrink-0"
                                                    />
                                                )}
                                            </span>
                                        </a>
                                    </td>
                                    <td className="px-4 py-3 text-sm font-bold text-zinc-500 text-center">
                                        {item.quantity}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-zinc-500 whitespace-nowrap">
                                        {formatMoney(item.price, symbol)}
                                    </td>
                                    <td className="px-4 py-3 text-sm font-black whitespace-nowrap">
                                        {formatMoney(item.total, symbol)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}

function ProductsModal({ state, loading, checkoutOrder, onAdvanceOrder, advancingOrderId }) {
    const orders = checkoutOrder?.orders || [];

    return (
        <Modal isOpen={state.isOpen} onOpenChange={state.onOpenChange}>
            <Modal.Backdrop>
                <Modal.Container size="xl" scroll="inside">
                    <Modal.Dialog>
                        <Modal.CloseTrigger />
                        <Modal.Header className="flex flex-col gap-1">
                            <Modal.Heading className="font-black">
                                طلبات طلب الدفع
                            </Modal.Heading>
                            {checkoutOrder?.code && (
                                <span className="text-xs text-zinc-400 font-bold">
                                    {checkoutOrder.code}
                                </span>
                            )}
                        </Modal.Header>
                        <Modal.Body>
                            {loading ? (
                                <div className="flex justify-center py-10">
                                    <Loader2 className="animate-spin text-zinc-400" />
                                </div>
                            ) : orders.length === 0 ? (
                                <div className="text-center text-zinc-400 py-10 font-bold">
                                    لا توجد طلبات
                                </div>
                            ) : (
                                <div className="flex flex-col gap-5">
                                    {orders.map((order) => (
                                        <OrderGroup
                                            key={order.id}
                                            order={order}
                                            onAdvanceOrder={onAdvanceOrder}
                                            advancingOrderId={advancingOrderId}
                                        />
                                    ))}
                                </div>
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

export default function Index({ checkoutOrders, statuses = [], filters = {} }) {
    const [searchQuery, setSearchQuery] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(filters.status || "");
    const [advancingId, setAdvancingId] = useState(null);
    const [advancingOrderId, setAdvancingOrderId] = useState(null);
    const [modalLoading, setModalLoading] = useState(false);
    const [modalCheckout, setModalCheckout] = useState(null);
    const productsModal = useOverlayState();
    const debounceTimer = useRef(null);

    const list = checkoutOrders?.data || [];

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

    const fetchProducts = useCallback(async (checkoutId) => {
        const res = await fetch(
            route("admin.checkout-orders.products", checkoutId),
            {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            },
        );
        const data = await res.json();
        return data.checkoutOrder;
    }, []);

    const handleViewProducts = async (checkout) => {
        setModalCheckout({ id: checkout.id, code: checkout.code, orders: [] });
        setModalLoading(true);
        productsModal.open();
        try {
            setModalCheckout(await fetchProducts(checkout.id));
        } catch (e) {
            setModalCheckout({ id: checkout.id, code: checkout.code, orders: [] });
        } finally {
            setModalLoading(false);
        }
    };

    const handleAdvanceOrder = (order) => {
        if (!order.status_next) return;

        setAdvancingOrderId(order.id);
        router.post(
            route("admin.orders.next-status", order.id),
            {},
            {
                preserveScroll: true,
                only: ["checkoutOrders", "flash"],
                onSuccess: async () => {
                    const current = modalCheckout?.id;
                    if (current) {
                        try {
                            setModalCheckout(await fetchProducts(current));
                        } catch (e) {
                            /* keep stale modal data */
                        }
                    }
                },
                onFinish: () => setAdvancingOrderId(null),
            },
        );
    };

    const handleAdvance = (checkout) => {
        const next = nextByStatus[checkout.status];
        if (!next) return;

        setAdvancingId(checkout.id);
        router.post(
            route("admin.checkout-orders.next-status", checkout.id),
            {},
            {
                preserveScroll: true,
                only: ["checkoutOrders", "flash"],
                onFinish: () => setAdvancingId(null),
            },
        );
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
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide text-end">
                                                إجراءات
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {list.map((checkout) => (
                                            <CheckoutRow
                                                key={checkout.id}
                                                checkout={checkout}
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

            <ProductsModal
                state={productsModal}
                loading={modalLoading}
                checkoutOrder={modalCheckout}
                onAdvanceOrder={handleAdvanceOrder}
                advancingOrderId={advancingOrderId}
            />
        </AdminLayout>
    );
}
