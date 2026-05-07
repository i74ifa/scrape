import CentralAdminLayout from "../../../Layouts/CentralAdminLayout";
import {
    Card,
    CardContent,
    Chip,
} from "@heroui/react";
import { DollarSign, Building2, TrendingUp, BarChart3 } from "lucide-react";

const StatCard = ({ title, value, icon: Icon, color }) => (
    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-4 shadow-none">
        <CardContent className="overflow-visible py-2">
            <div className={`p-4 rounded-3xl ${color} w-fit`}>
                <Icon className="w-7 h-7 text-white" />
            </div>
            <div className="mt-4">
                <p className="text-zinc-500 dark:text-zinc-400 font-bold text-sm uppercase tracking-widest">
                    {title}
                </p>
                <h3 className="text-3xl font-black text-black dark:text-white mt-1 tracking-tighter">
                    {value}
                </h3>
            </div>
        </CardContent>
    </Card>
);

export default function Index({ stats }) {
    const statusLabels = {
        active: "نشط",
        expired: "منتهي",
        cancelled: "ملغي",
        trial: "تجريبي",
    };

    return (
        <CentralAdminLayout title="التقارير">
            <div className="flex flex-col gap-8 pb-10">
                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <StatCard
                        title="إجمالي الإيرادات"
                        value={`${stats.totalRevenue} د.ع`}
                        icon={DollarSign}
                        color="bg-emerald-500"
                    />
                    <StatCard
                        title="الخطط النشطة"
                        value={stats.tenantsByPlan.length}
                        icon={Building2}
                        color="bg-blue-500"
                    />
                    <StatCard
                        title="نمو العملاء (6 أشهر)"
                        value={stats.tenantGrowth.reduce((sum, m) => sum + m.count, 0)}
                        icon={TrendingUp}
                        color="bg-indigo-500"
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* الإيرادات الشهرية */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                        <CardContent className="p-5">
                            <h3 className="text-xl font-black text-black dark:text-white mb-6">
                                الإيرادات الشهرية
                            </h3>
                            <div className="space-y-4">
                                {stats.revenueByMonth.map((month) => (
                                    <div
                                        key={month.month}
                                        className="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                    >
                                        <span className="text-sm font-medium">
                                            {month.month}
                                        </span>
                                        <span className="font-bold">
                                            {month.total} د.ع
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* توزيع العملاء حسب الخطة */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                        <CardContent className="p-5">
                            <h3 className="text-xl font-black text-black dark:text-white mb-6">
                                توزيع العملاء حسب الخطة
                            </h3>
                            <div className="space-y-4">
                                {stats.tenantsByPlan.map((plan) => (
                                    <div
                                        key={plan.plan_slug}
                                        className="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                    >
                                        <span className="text-sm font-medium">
                                            {plan.plan_name}
                                        </span>
                                        <Chip size="sm" variant="flat" color="primary">
                                            {plan.count} عميل
                                        </Chip>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* نمو العملاء */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                        <CardContent className="p-5">
                            <h3 className="text-xl font-black text-black dark:text-white mb-6">
                                نمو العملاء (آخر 6 أشهر)
                            </h3>
                            <div className="space-y-4">
                                {stats.tenantGrowth.map((month) => (
                                    <div
                                        key={month.month}
                                        className="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                    >
                                        <span className="text-sm font-medium">
                                            {month.month}
                                        </span>
                                        <span className="font-bold">
                                            +{month.count} عميل
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* حالة الاشتراكات */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                        <CardContent className="p-5">
                            <h3 className="text-xl font-black text-black dark:text-white mb-6">
                                حالة الاشتراكات
                            </h3>
                            <div className="space-y-4">
                                {stats.subscriptionStatusDistribution.map((item) => (
                                    <div
                                        key={item.status}
                                        className="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                    >
                                        <span className="text-sm font-medium">
                                            {statusLabels[item.status] || item.status}
                                        </span>
                                        <Chip size="sm" variant="flat" color="primary">
                                            {item.count}
                                        </Chip>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* أفضل العملاء */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-5">
                        <h3 className="text-xl font-black text-black dark:text-white mb-6">
                            أفضل العملاء من حيث الإنفاق
                        </h3>
                        <div className="space-y-3">
                            {stats.topTenants.map((tenant, index) => (
                                <div
                                    key={tenant.id}
                                    className="flex justify-between items-center p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center font-bold text-sm text-blue-600 dark:text-blue-400">
                                            {index + 1}
                                        </div>
                                        <span className="font-semibold">
                                            {tenant.name}
                                        </span>
                                    </div>
                                    <span className="font-bold text-emerald-600">
                                        {tenant.total_spent} د.ع
                                    </span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </CentralAdminLayout>
    );
}
