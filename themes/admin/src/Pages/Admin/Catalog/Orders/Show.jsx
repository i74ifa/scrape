import { router, Link } from "@inertiajs/react";
import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Card, CardContent, Button, Chip } from "@heroui/react";
import {
    ArrowRight,
    User,
    MapPin,
    Phone,
    StickyNote,
    ArrowLeftCircle,
    XCircle,
} from "lucide-react";

export default function Show({ order }) {
    const [processing, setProcessing] = useState(false);

    const setStatus = (status) => {
        setProcessing(true);
        router.post(
            route("admin.catalog.orders.status", order.id),
            { status },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <AdminLayout title={`الطلب ${order.code}`}>
            <div className="flex flex-col gap-6 pb-10">
                {/* Header */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link href={route("admin.catalog.orders.index")}>
                            <Button variant="flat" isIconOnly className="rounded-full">
                                <ArrowRight className="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <h2 className="text-xl font-black font-mono" dir="ltr">
                                {order.code}
                            </h2>
                            <p className="text-xs text-zinc-400" dir="ltr">
                                {order.created_at}
                            </p>
                        </div>
                        <Chip size="sm" variant="flat" color={order.status_color}>
                            {order.status_label}
                        </Chip>
                    </div>

                    {/* Status actions */}
                    <div className="flex items-center gap-2">
                        {order.next_status && (
                            <Button
                                color="primary"
                                className="rounded-full font-bold"
                                isLoading={processing}
                                startContent={<ArrowLeftCircle className="w-4 h-4" />}
                                onPress={() => setStatus(order.next_status)}
                            >
                                نقل إلى: {order.next_status_label}
                            </Button>
                        )}
                        {order.can_cancel && (
                            <Button
                                variant="flat"
                                className="rounded-full font-bold text-rose-500"
                                isDisabled={processing}
                                startContent={<XCircle className="w-4 h-4" />}
                                onPress={() => setStatus("cancelled")}
                            >
                                إلغاء الطلب
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Items */}
                    <div className="lg:col-span-2">
                        <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
                            <CardContent className="p-6">
                                <h3 className="font-black text-base mb-4">العناصر</h3>
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm text-right">
                                        <thead>
                                            <tr className="text-xs text-zinc-400 border-b border-zinc-100 dark:border-zinc-800">
                                                <th className="font-bold py-2 px-2">المنتج</th>
                                                <th className="font-bold py-2 px-2">السعر</th>
                                                <th className="font-bold py-2 px-2">الكمية</th>
                                                <th className="font-bold py-2 px-2">الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {order.items.map((it) => (
                                                <tr key={it.id} className="border-b border-zinc-50 dark:border-zinc-800/50">
                                                    <td className="py-3 px-2">
                                                        <div className="font-semibold">{it.name}</div>
                                                        {it.variant_label && (
                                                            <div className="text-xs text-blue-600">{it.variant_label}</div>
                                                        )}
                                                    </td>
                                                    <td className="py-3 px-2 text-zinc-500">{it.unit_price}</td>
                                                    <td className="py-3 px-2 text-zinc-500">×{it.quantity}</td>
                                                    <td className="py-3 px-2 font-bold">{it.total}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="mt-5 flex flex-col gap-1 items-end">
                                    <div className="flex justify-between w-56 text-sm text-zinc-500">
                                        <span>المجموع الفرعي</span>
                                        <span>{order.subtotal}</span>
                                    </div>
                                    <div className="flex justify-between w-56 text-base font-black">
                                        <span>الإجمالي</span>
                                        <span>{order.total}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Customer + address + note */}
                    <div className="flex flex-col gap-6">
                        <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
                            <CardContent className="p-6 space-y-3">
                                <h3 className="font-black text-base flex items-center gap-2">
                                    <User className="w-4 h-4 text-zinc-400" /> العميل
                                </h3>
                                <p className="text-sm font-semibold">{order.customer.name ?? "—"}</p>
                                {order.customer.phone && (
                                    <p className="text-sm text-zinc-500 flex items-center gap-2" dir="ltr">
                                        <Phone className="w-3.5 h-3.5" /> {order.customer.phone}
                                    </p>
                                )}
                                {order.customer.email && (
                                    <p className="text-xs text-zinc-400" dir="ltr">{order.customer.email}</p>
                                )}
                            </CardContent>
                        </Card>

                        {order.address && (
                            <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
                                <CardContent className="p-6 space-y-2">
                                    <h3 className="font-black text-base flex items-center gap-2">
                                        <MapPin className="w-4 h-4 text-zinc-400" /> عنوان الشحن
                                    </h3>
                                    <p className="text-sm text-zinc-600 dark:text-zinc-300">
                                        {order.address.address_one}
                                    </p>
                                    {order.address.phone && (
                                        <p className="text-sm text-zinc-500" dir="ltr">{order.address.phone}</p>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {order.note && (
                            <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] shadow-none">
                                <CardContent className="p-6 space-y-2">
                                    <h3 className="font-black text-base flex items-center gap-2">
                                        <StickyNote className="w-4 h-4 text-zinc-400" /> ملاحظة العميل
                                    </h3>
                                    <p className="text-sm text-zinc-600 dark:text-zinc-300">{order.note}</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
