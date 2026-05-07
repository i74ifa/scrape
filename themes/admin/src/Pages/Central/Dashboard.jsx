import CentralAdminLayout from "../../Layouts/CentralAdminLayout";
import {
    Building2,
    CreditCard,
    DollarSign,
    TrendingUp,
    Clock,
    ChevronRight,
    Plus,
    Edit,
    Trash,
    SquarePen,
    ArrowUpRight,
    ArrowDownRight,
} from "lucide-react";
import { Card, CardContent, Button } from "@heroui/react";

const DashboardCard = ({ title, value, change, icon: Icon, trend, color }) => (
    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-4 shadow-none">
        <CardContent className="overflow-visible py-2">
            <div className="flex justify-between items-start">
                <div className={`p-4 rounded-3xl ${color}`}>
                    <Icon className="w-7 h-7 text-white" />
                </div>
                <div
                    className={`flex items-center gap-1 text-sm font-bold ${
                        trend === "up" ? "text-emerald-500" : "text-rose-500"
                    }`}
                >
                    {change && change + "%"}
                    {trend && trend === "up" ? (
                        <ArrowUpRight className="w-4 h-4" />
                    ) : (
                        <ArrowDownRight className="w-4 h-4" />
                    )}
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

export default function Dashboard({ stats, recentTenants, tenantsByPlan }) {
    const icons = {
        created: Plus,
        updated: Edit,
        deleted: Trash,
    };

    const colors = {
        created: "text-emerald-500 dark:text-emerald-500",
        updated: "text-blue-500 dark:text-blue-500",
        deleted: "text-rose-500 dark:text-rose-500",
    };

    const planColors = [
        "bg-blue-500",
        "bg-purple-500",
        "bg-emerald-500",
        "bg-orange-500",
        "bg-indigo-500",
    ];

    return (
        <CentralAdminLayout title="نظرة عامة">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <DashboardCard
                    title="إجمالي العملاء"
                    value={stats.totalTenants}
                    change={null}
                    icon={Building2}
                    trend={null}
                    color="bg-blue-500"
                />
                <DashboardCard
                    title="العملاء النشطين"
                    value={stats.activeTenants}
                    change={null}
                    icon={Building2}
                    trend="up"
                    color="bg-emerald-500"
                />
                <DashboardCard
                    title="الاشتراكات النشطة"
                    value={stats.activeSubscriptions}
                    change={null}
                    icon={CreditCard}
                    trend="up"
                    color="bg-orange-500"
                />
                <DashboardCard
                    title="إيرادات الشهر"
                    value={`${stats.monthlyRevenue} د.ع`}
                    change={null}
                    icon={DollarSign}
                    trend="up"
                    color="bg-indigo-500"
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
                {/* توزيع العملاء حسب الخطة */}
                <Card className="lg:col-span-2 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-5">
                        <div className="flex justify-between items-start mb-12">
                            <div>
                                <h3 className="text-2xl font-black text-black dark:text-white tracking-tight leading-none">
                                    توزيع العملاء حسب الخطة
                                </h3>
                                <p className="text-zinc-500 dark:text-zinc-400 font-medium mt-2">
                                    الاشتراكات النشطة
                                </p>
                            </div>
                            <Button
                                variant="flat"
                                className="rounded-full font-bold bg-zinc-100 dark:bg-zinc-800"
                                href="/central/reports"
                            >
                                عرض التقرير
                            </Button>
                        </div>

                        <div className="space-y-10">
                            {tenantsByPlan.map((item, index) => (
                                <div key={item.plan_price?.plan?.name || index}>
                                    <div className="flex justify-between mb-3 px-1">
                                        <span className="text-sm font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-widest leading-none">
                                            {item.plan_price?.plan?.name || "بدون خطة"}
                                        </span>
                                        <span className="text-sm font-black text-black dark:text-white leading-none">
                                            {item.count}
                                        </span>
                                    </div>
                                    <div className="w-full h-4 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div
                                            className={`h-full ${planColors[index % planColors.length]} rounded-full transition-all duration-500`}
                                            style={{
                                                width: `${stats.activeSubscriptions ? (item.count / stats.activeSubscriptions) * 100 : 0}%`,
                                            }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* آخر العملاء المضافين */}
                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-4">
                        <div className="flex justify-between items-center mb-8">
                            <h3 className="text-2xl font-black tracking-tight leading-none">
                                آخر العملاء
                            </h3>
                            <div className="w-10 h-10 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
                                <Clock size={20} />
                            </div>
                        </div>

                        <div className="space-y-4">
                            {recentTenants.map((tenant, i) => (
                                <div
                                    key={tenant.id}
                                    className="flex items-center justify-between group cursor-pointer p-4 hover:bg-zinc-900/5 dark:hover:bg-zinc-800 rounded-3xl transition-all"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                            <Building2
                                                size={18}
                                                className="text-blue-600 dark:text-blue-400"
                                            />
                                        </div>
                                        <div>
                                            <p className="font-bold text-sm tracking-tight">
                                                {tenant.name}
                                            </p>
                                            <p className="text-xs text-zinc-500 font-medium mt-0.5">
                                                {tenant.domain?.domain || "—"}
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

                        <Button
                            className="w-full mt-6 bg-white text-black font-black h-14 rounded-3xl"
                            href="/central/tenants"
                        >
                            عرض كل العملاء
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </CentralAdminLayout>
    );
}
