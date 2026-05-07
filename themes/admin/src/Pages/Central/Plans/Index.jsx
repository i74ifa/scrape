import CentralAdminLayout from "../../../Layouts/CentralAdminLayout";
import {
    Card,
    CardContent,
    Button,
    Chip,
} from "@heroui/react";
import { Plus, Edit, Trash2, DollarSign, Calendar } from "lucide-react";
import { router } from "@inertiajs/react";

export default function Index({ plans }) {
    const handleDelete = (plan) => {
        if (confirm(`هل أنت متأكد من حذف الخطة "${plan.name}"؟`)) {
            router.delete(route("central.plans.destroy", plan.id), {
                preserveScroll: true,
            });
        }
    };

    const tierColors = {
        free: "success",
        basic: "primary",
        pro: "warning",
        enterprise: "danger",
    };

    const tierLabels = {
        free: "مجاني",
        basic: "أساسي",
        pro: "احترافي",
        enterprise: "مؤسسات",
    };

    return (
        <CentralAdminLayout title="الخطط">
            <div className="flex flex-col gap-8 pb-10">
                {/* Header */}
                <div className="flex justify-between items-center px-2">
                    <h2 className="text-xl font-bold text-zinc-500 dark:text-zinc-400">
                        {plans.length} خطة
                    </h2>
                    <Button
                        color="primary"
                        onPress={() => router.get(route("central.plans.create"))}
                    >
                        <Plus size={18} />
                        إضافة خطة
                    </Button>
                </div>

                {/* Plans Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {plans.map((plan) => (
                        <Card
                            key={plan.id}
                            className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none overflow-hidden"
                        >
                            <CardContent className="p-6">
                                <div className="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 className="text-xl font-black text-black dark:text-white">
                                            {plan.name}
                                        </h3>
                                        <Chip
                                            size="sm"
                                            variant="flat"
                                            color={tierColors[plan.tier]}
                                            className="mt-2"
                                        >
                                            {tierLabels[plan.tier]}
                                        </Chip>
                                    </div>
                                    <Chip
                                        size="sm"
                                        variant="flat"
                                        color={plan.is_active ? "success" : "default"}
                                    >
                                        {plan.is_active ? "نشط" : "غير نشط"}
                                    </Chip>
                                </div>

                                {/* Prices */}
                                <div className="space-y-3 mb-6">
                                    {plan.prices?.map((price) => (
                                        <div
                                            key={price.id}
                                            className="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                        >
                                            <div className="flex items-center gap-2">
                                                <DollarSign size={16} className="text-zinc-400" />
                                                <span className="text-sm font-medium">
                                                    {price.billing_cycle === "monthly" ? "شهري" : "سنوي"}
                                                </span>
                                            </div>
                                            <span className="font-bold">
                                                {price.amount} د.ع
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                {/* Features */}
                                {plan.features?.length > 0 && (
                                    <div className="mb-6">
                                        <h4 className="text-sm font-bold text-zinc-500 mb-3">
                                            المميزات
                                        </h4>
                                        <div className="space-y-2">
                                            {plan.features.slice(0, 5).map((feature) => (
                                                <div
                                                    key={feature.id}
                                                    className="flex justify-between text-sm"
                                                >
                                                    <span className="text-zinc-600 dark:text-zinc-400">
                                                        {feature.feature?.name || "—"}
                                                    </span>
                                                    <span className="font-medium">
                                                        {feature.is_unlimited
                                                            ? "∞"
                                                            : feature.value || "—"}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Actions */}
                                <div className="flex gap-2">
                                    <Button
                                        variant="flat"
                                        className="flex-1"
                                        onPress={() =>
                                            router.get(route("central.plans.edit", plan.id))
                                        }
                                    >
                                        <Edit size={16} />
                                        تعديل
                                    </Button>
                                    <Button
                                        variant="flat"
                                        color="danger"
                                        onPress={() => handleDelete(plan)}
                                    >
                                        <Trash2 size={16} />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </CentralAdminLayout>
    );
}
