import CentralAdminLayout from "../../../Layouts/CentralAdminLayout";
import {
    Card,
    CardContent,
    Input,
    Button,
    Switch,
} from "@heroui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Plus, Trash2 } from "lucide-react";

export default function CreateUpdate({ plan, features }) {
    const isEditing = !!plan;

    const [form, setForm] = useState({
        name: plan?.name || "",
        slug: plan?.slug || "",
        tier: plan?.tier || "basic",
        is_active: plan?.is_active ?? true,
        monthly_amount: plan?.prices?.find((p) => p.billing_cycle === "monthly")?.amount || "",
        yearly_amount: plan?.prices?.find((p) => p.billing_cycle === "yearly")?.amount || "",
        monthly_trial_days: plan?.prices?.find((p) => p.billing_cycle === "monthly")?.trial_days || 0,
        yearly_trial_days: plan?.prices?.find((p) => p.billing_cycle === "yearly")?.trial_days || 0,
        features: plan?.features?.map((f) => ({
            feature_id: f.feature_id,
            value: f.value || "",
            is_unlimited: f.is_unlimited || false,
        })) || [],
    });

    const [errors, setErrors] = useState({});

    const addFeature = () => {
        setForm({
            ...form,
            features: [...form.features, { feature_id: "", value: "", is_unlimited: false }],
        });
    };

    const removeFeature = (index) => {
        setForm({
            ...form,
            features: form.features.filter((_, i) => i !== index),
        });
    };

    const updateFeature = (index, field, value) => {
        const updated = [...form.features];
        updated[index] = { ...updated[index], [field]: value };
        setForm({ ...form, features: updated });
    };

    const handleSubmit = () => {
        if (isEditing) {
            router.put(route("central.plans.update", plan.id), form, {
                onError: (errs) => setErrors(errs),
            });
        } else {
            router.post(route("central.plans.store"), form, {
                onError: (errs) => setErrors(errs),
            });
        }
    };

    return (
        <CentralAdminLayout title={isEditing ? "تعديل الخطة" : "إضافة خطة جديدة"}>
            <div className="flex flex-col gap-8 pb-10 max-w-3xl mx-auto">
                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-6">
                        <div className="flex flex-col gap-6">
                            <Input
                                label="اسم الخطة"
                                placeholder="مثال: الخطة الأساسية"
                                value={form.name}
                                onValueChange={(v) => setForm({ ...form, name: v })}
                                isInvalid={!!errors.name}
                                errorMessage={errors.name}
                            />

                            <Input
                                label="المعرف (Slug)"
                                placeholder="basic"
                                value={form.slug}
                                onValueChange={(v) => setForm({ ...form, slug: v })}
                                isInvalid={!!errors.slug}
                                errorMessage={errors.slug}
                            />

                            <select
                                className="w-full h-12 px-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 border-none outline-none text-zinc-700 dark:text-zinc-300 text-sm"
                                value={form.tier}
                                onChange={(e) => setForm({ ...form, tier: e.target.value })}
                            >
                                <option value="free">مجاني</option>
                                <option value="basic">أساسي</option>
                                <option value="pro">احترافي</option>
                                <option value="enterprise">مؤسسات</option>
                            </select>

                            <Switch
                                isSelected={form.is_active}
                                onValueChange={(v) => setForm({ ...form, is_active: v })}
                            >
                                نشط
                            </Switch>

                            <hr className="border-zinc-200 dark:border-zinc-700" />

                            <h3 className="text-lg font-bold">الأسعار</h3>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    label="السعر الشهري (د.ع)"
                                    type="number"
                                    value={form.monthly_amount}
                                    onValueChange={(v) => setForm({ ...form, monthly_amount: v })}
                                    isInvalid={!!errors.monthly_amount}
                                    errorMessage={errors.monthly_amount}
                                />
                                <Input
                                    label="أيام التجربة (شهري)"
                                    type="number"
                                    value={form.monthly_trial_days}
                                    onValueChange={(v) => setForm({ ...form, monthly_trial_days: v })}
                                />
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    label="السعر السنوي (د.ع)"
                                    type="number"
                                    value={form.yearly_amount}
                                    onValueChange={(v) => setForm({ ...form, yearly_amount: v })}
                                    isInvalid={!!errors.yearly_amount}
                                    errorMessage={errors.yearly_amount}
                                />
                                <Input
                                    label="أيام التجربة (سنوي)"
                                    type="number"
                                    value={form.yearly_trial_days}
                                    onValueChange={(v) => setForm({ ...form, yearly_trial_days: v })}
                                />
                            </div>

                            <hr className="border-zinc-200 dark:border-zinc-700" />

                            <div className="flex justify-between items-center">
                                <h3 className="text-lg font-bold">المميزات</h3>
                                <Button size="sm" onPress={addFeature}>
                                    <Plus size={16} />
                                    إضافة ميزة
                                </Button>
                            </div>

                            <div className="space-y-4">
                                {form.features.map((feature, index) => (
                                    <div
                                        key={index}
                                        className="flex flex-col md:flex-row gap-3 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl"
                                    >
                                        <select
                                            className="flex-1 h-12 px-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 border-none outline-none text-zinc-700 dark:text-zinc-300 text-sm"
                                            value={feature.feature_id}
                                            onChange={(e) =>
                                                updateFeature(index, "feature_id", e.target.value)
                                            }
                                        >
                                            <option value="">اختر الميزة</option>
                                            {features?.map((f) => (
                                                <option key={f.id} value={f.id}>{f.name}</option>
                                            ))}
                                        </select>
                                        <Input
                                            placeholder="القيمة"
                                            value={feature.value}
                                            onValueChange={(v) => updateFeature(index, "value", v)}
                                            className="flex-1"
                                        />
                                        <Switch
                                            isSelected={feature.is_unlimited}
                                            onValueChange={(v) => updateFeature(index, "is_unlimited", v)}
                                        >
                                            غير محدود
                                        </Switch>
                                        <Button
                                            isIconOnly
                                            size="sm"
                                            color="danger"
                                            variant="light"
                                            onPress={() => removeFeature(index)}
                                        >
                                            <Trash2 size={16} />
                                        </Button>
                                    </div>
                                ))}
                            </div>

                            <div className="flex gap-3 justify-end mt-4">
                                <Button
                                    variant="flat"
                                    onPress={() => router.get(route("central.plans.index"))}
                                >
                                    إلغاء
                                </Button>
                                <Button color="primary" onPress={handleSubmit}>
                                    {isEditing ? "تحديث" : "إنشاء"}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </CentralAdminLayout>
    );
}
