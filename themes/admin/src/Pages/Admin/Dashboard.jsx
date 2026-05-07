import AdminLayout from "@/Layouts/AdminLayout";
import { Link } from "@inertiajs/react";
import {
    Users,
    ShoppingCart,
    DollarSign,
    Package,
    Plus,
    Edit,
    Trash,
    Clock,
    ChevronRight,
    SquarePen,
} from "lucide-react";
import { Card, CardContent, Button } from "@heroui/react";

const formatNumber = (n) => new Intl.NumberFormat("ar-IQ").format(n ?? 0);
const formatMoney = (n) =>
    `${new Intl.NumberFormat("ar-IQ", { maximumFractionDigits: 0 }).format(n ?? 0)} د.ع`;

const StatCard = ({ title, value, icon: Icon, color }) => (
    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-4 shadow-none">
        <CardContent className="overflow-visible py-2">
            <div className="flex justify-between items-start">
                <div className={`p-4 rounded-3xl ${color}`}>
                    <Icon className="w-7 h-7 text-white" />
                </div>
            </div>
            <div className="mt-8">
                <p className="text-zinc-500 dark:text-zinc-400 font-bold text-sm uppercase tracking-widest">
                    {title}
                </p>
                <h3 className="text-4xl font-black text-black dark:text-white mt-1 tracking-tighter">
                    {value}
                </h3>
            </div>
        </CardContent>
    </Card>
);

export default function Dashboard({ stats = {}, activities = [] }) {
    const icons = { created: Plus, updated: Edit, deleted: Trash };
    const colors = {
        created: "text-emerald-500 dark:text-emerald-500",
        updated: "text-blue-500 dark:text-blue-500",
        deleted: "text-rose-500 dark:text-rose-500",
    };

    const formattedActivities = activities.map((a) => ({
        title: a.description,
        icon: icons[a.event] || SquarePen,
        time: a.created_at,
        color: colors[a.event] || "text-zinc-500 dark:text-zinc-500",
    }));

    return (
        <AdminLayout title="نظرة عامة">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <StatCard
                    title="إجمالي المنتجات"
                    value={formatNumber(stats.products_count)}
                    icon={Package}
                    color="bg-blue-500"
                />
                <StatCard
                    title="طلبات هذا الشهر"
                    value={formatNumber(stats.orders_count)}
                    icon={ShoppingCart}
                    color="bg-orange-500"
                />
                <StatCard
                    title="إيرادات هذا الشهر"
                    value={formatMoney(stats.revenue)}
                    icon={DollarSign}
                    color="bg-emerald-500"
                />
                <StatCard
                    title="إجمالي المستخدمين"
                    value={formatNumber(stats.users_count)}
                    icon={Users}
                    color="bg-indigo-500"
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
                <Card className="lg:col-span-2 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-5">
                        <div className="flex justify-between items-start mb-8">
                            <div>
                                <h3 className="text-2xl font-black text-black dark:text-white tracking-tight leading-none">
                                    روابط سريعة
                                </h3>
                                <p className="text-zinc-500 dark:text-zinc-400 font-medium mt-2">
                                    اختصارات للمهام الشائعة
                                </p>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <Link
                                href="/panel/products"
                                className="bg-zinc-50 dark:bg-zinc-800/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-2xl p-4 flex flex-col items-center text-center transition-colors"
                            >
                                <Package className="w-8 h-8 text-blue-500 mb-2" />
                                <p className="font-bold text-sm">المنتجات</p>
                            </Link>
                            <Link
                                href="/panel/orders"
                                className="bg-zinc-50 dark:bg-zinc-800/50 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-2xl p-4 flex flex-col items-center text-center transition-colors"
                            >
                                <ShoppingCart className="w-8 h-8 text-orange-500 mb-2" />
                                <p className="font-bold text-sm">الطلبات</p>
                            </Link>
                            <Link
                                href="/panel/checkout-orders"
                                className="bg-zinc-50 dark:bg-zinc-800/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-2xl p-4 flex flex-col items-center text-center transition-colors"
                            >
                                <DollarSign className="w-8 h-8 text-emerald-500 mb-2" />
                                <p className="font-bold text-sm">طلبات الدفع</p>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-4">
                        <div className="flex justify-between items-center mb-8">
                            <h3 className="text-2xl font-black tracking-tight leading-none">
                                النشاط الأخير
                            </h3>
                            <div className="w-10 h-10 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
                                <Clock size={20} />
                            </div>
                        </div>

                        {formattedActivities.length === 0 ? (
                            <p className="text-sm text-zinc-400 text-center py-8">
                                لا يوجد نشاط حديث
                            </p>
                        ) : (
                            <div className="space-y-6">
                                {formattedActivities.map((activity, i) => (
                                    <div
                                        key={i}
                                        className="flex items-center justify-between group p-4 hover:bg-zinc-900/5 dark:hover:bg-zinc-800 rounded-3xl transition-all"
                                    >
                                        <div className="flex items-center gap-4">
                                            <activity.icon
                                                className={`w-5 h-5 ${activity.color}`}
                                            />
                                            <div>
                                                <p className="font-bold text-sm tracking-tight">
                                                    {activity.title}
                                                </p>
                                                <p className="text-xs text-zinc-500 font-medium mt-0.5">
                                                    {activity.time}
                                                </p>
                                            </div>
                                        </div>
                                        <ChevronRight
                                            size={16}
                                            className="text-zinc-700 opacity-0 group-hover:opacity-100 transition-all"
                                        />
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
