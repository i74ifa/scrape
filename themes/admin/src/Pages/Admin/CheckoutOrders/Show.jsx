import AdminLayout from "@/Layouts/AdminLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import {
    ArrowRight,
    Clock,
    CheckCircle2,
    XCircle,
    RotateCcw,
    Banknote,
    ArrowLeftCircle,
    Loader2,
    Package,
    PackageCheck,
    PackageOpen,
    Plane,
    ShieldCheck,
    Hourglass,
    ExternalLink,
    User,
    MapPin,
    Phone,
    Hash,
    CalendarDays,
} from "lucide-react";
import { Card, CardContent, Button } from "@heroui/react";

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

function Badge({ map, status }) {
    const meta = map[status] || Object.values(map)[0];
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

function InfoRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3">
            <div className="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 shrink-0">
                <Icon size={16} />
            </div>
            <div className="min-w-0">
                <div className="text-xs text-zinc-400 font-bold">{label}</div>
                <div className="text-sm font-bold truncate">{value || "—"}</div>
            </div>
        </div>
    );
}

function TotalRow({ label, value, strong }) {
    return (
        <div className="flex items-center justify-between">
            <span
                className={`text-sm ${strong ? "font-black" : "font-bold text-zinc-400"}`}
            >
                {label}
            </span>
            <span className={`text-sm ${strong ? "font-black" : "font-bold"}`}>
                {value}
            </span>
        </div>
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
            <div className="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-zinc-100 dark:border-zinc-800">
                <div className="flex items-center gap-3 min-w-0">
                    {platform?.logo ? (
                        <img
                            src={platform.logo}
                            alt={platform.name}
                            className="w-10 h-10 rounded-xl object-contain bg-white dark:bg-zinc-800 p-1"
                        />
                    ) : (
                        <div className="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                            <Package size={18} />
                        </div>
                    )}
                    <div className="min-w-0">
                        <div className="font-bold text-sm truncate">
                            {platform?.name || "—"}
                        </div>
                        <div className="text-xs text-zinc-400 truncate">
                            {order.code} · {formatMoney(order.grand_total, symbol)}
                        </div>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Badge map={ORDER_STATUS_MAP} status={order.status} />
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

export default function Show({ checkoutOrder }) {
    const [advancingId, setAdvancingId] = useState(null);
    const [advancingOrderId, setAdvancingOrderId] = useState(null);

    const co = checkoutOrder || {};
    const orders = co.orders || [];
    const nextMeta = co.status_next ? STATUS_MAP[co.status_next] : null;

    const address =
        co.address && typeof co.address === "object"
            ? co.address
            : null;

    const handleAdvanceCheckout = () => {
        if (!co.status_next) return;
        setAdvancingId(co.id);
        router.post(
            route("admin.checkout-orders.next-status", co.id),
            {},
            {
                preserveScroll: true,
                only: ["checkoutOrder", "flash"],
                onFinish: () => setAdvancingId(null),
            },
        );
    };

    const handleAdvanceOrder = (order) => {
        if (!order.status_next) return;
        setAdvancingOrderId(order.id);
        router.post(
            route("admin.orders.next-status", order.id),
            {},
            {
                preserveScroll: true,
                only: ["checkoutOrder", "flash"],
                onFinish: () => setAdvancingOrderId(null),
            },
        );
    };

    return (
        <AdminLayout title={`طلب الدفع ${co.code || ""}`}>
            <div className="flex flex-col gap-6 pb-10">
                {/* header */}
                <div className="flex flex-wrap items-center justify-between gap-3 px-2">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route("admin.checkout-orders.index")}
                            className="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition-colors"
                        >
                            <ArrowRight size={18} />
                        </Link>
                        <div>
                            <h4 className="text-lg font-black">{co.code}</h4>
                            <div className="text-xs text-zinc-400 font-bold">
                                {orders.length} طلب
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge map={STATUS_MAP} status={co.status} />
                        <Button
                            size="sm"
                            variant="flat"
                            isDisabled={!co.status_next || advancingId === co.id}
                            className={`rounded-full font-bold ${co.status_next ? "bg-blue-600 text-white" : "bg-zinc-100 dark:bg-zinc-800 text-zinc-400"}`}
                            startContent={
                                advancingId === co.id ? (
                                    <Loader2 size={14} className="animate-spin" />
                                ) : (
                                    <ArrowLeftCircle size={14} />
                                )
                            }
                            onPress={handleAdvanceCheckout}
                        >
                            {nextMeta ? `← ${nextMeta.label}` : "نهاية المسار"}
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* details */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none lg:col-span-2">
                        <CardContent className="p-6 flex flex-col gap-5">
                            <h5 className="font-black text-sm text-zinc-500">
                                تفاصيل الطلب
                            </h5>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <InfoRow
                                    icon={User}
                                    label="العميل"
                                    value={co.user?.name}
                                />
                                <InfoRow
                                    icon={Phone}
                                    label="الهاتف"
                                    value={co.user?.phone || co.user?.email}
                                />
                                <InfoRow
                                    icon={Banknote}
                                    label="طريقة الدفع"
                                    value={co.payment_method}
                                />
                                <InfoRow
                                    icon={CalendarDays}
                                    label="التاريخ"
                                    value={
                                        co.created_at
                                            ? new Date(co.created_at).toLocaleString(
                                                  "ar-IQ",
                                              )
                                            : "—"
                                    }
                                />
                                <InfoRow
                                    icon={MapPin}
                                    label="العنوان"
                                    value={address?.address_one}
                                />
                                <InfoRow
                                    icon={Hash}
                                    label="عدد القطع"
                                    value={co.total_quantity}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* totals */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                        <CardContent className="p-6 flex flex-col gap-4">
                            <h5 className="font-black text-sm text-zinc-500">
                                الملخص المالي
                            </h5>
                            <div className="flex flex-col gap-3">
                                <TotalRow
                                    label="المجموع الفرعي"
                                    value={formatMoney(co.sub_total)}
                                />
                                <TotalRow label="الضريبة" value={formatMoney(co.tax)} />
                                <TotalRow
                                    label="شحن محلي"
                                    value={formatMoney(co.local_shipping)}
                                />
                                <TotalRow
                                    label="الشحن"
                                    value={formatMoney(co.shipping)}
                                />
                                <TotalRow
                                    label="الخصم"
                                    value={`- ${formatMoney(co.discount)}`}
                                />
                                <div className="h-px bg-zinc-100 dark:bg-zinc-800 my-1" />
                                <TotalRow
                                    label="الإجمالي"
                                    value={formatMoney(co.grand_total)}
                                    strong
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* orders grouped by scraper */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-6 flex flex-col gap-5">
                        <h5 className="font-black text-sm text-zinc-500">
                            الطلبات حسب المتجر
                        </h5>
                        {orders.length === 0 ? (
                            <div className="text-center text-zinc-400 py-10 font-bold">
                                لا توجد طلبات
                            </div>
                        ) : (
                            <div className="flex flex-col gap-5">
                                {orders.map((order) => (
                                    <OrderGroup
                                        key={order.id}
                                        order={order}
                                        onAdvanceOrder={handleAdvanceOrder}
                                        advancingOrderId={advancingOrderId}
                                    />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
