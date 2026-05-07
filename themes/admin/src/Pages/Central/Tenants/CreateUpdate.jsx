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

export default function CreateUpdate({ tenant }) {
    const isEditing = !!tenant;

    const [form, setForm] = useState({
        name: tenant?.name || "",
        slug: tenant?.slug || "",
        phone: tenant?.phone || "",
        location: tenant?.location || "",
        short_description: tenant?.short_description || "",
        is_active: tenant?.is_active ?? true,
    });

    const [errors, setErrors] = useState({});

    const handleSubmit = () => {
        if (isEditing) {
            router.put(route("central.tenants.update", tenant.id), form, {
                onError: (errs) => setErrors(errs),
            });
        } else {
            router.post(route("central.tenants.store"), form, {
                onError: (errs) => setErrors(errs),
            });
        }
    };

    return (
        <CentralAdminLayout title={isEditing ? "تعديل العميل" : "إضافة عميل جديد"}>
            <div className="flex flex-col gap-8 pb-10 max-w-2xl mx-auto">
                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                    <CardContent className="p-6">
                        <div className="flex flex-col gap-6">
                            <Input
                                label="اسم العميل"
                                placeholder="اسم المطعم أو النشاط"
                                value={form.name}
                                onValueChange={(v) => setForm({ ...form, name: v })}
                                isInvalid={!!errors.name}
                                errorMessage={errors.name}
                            />

                            <Input
                                label="النطاق (Slug)"
                                placeholder="restaurant-name"
                                value={form.slug}
                                onValueChange={(v) => setForm({ ...form, slug: v })}
                                isInvalid={!!errors.slug}
                                errorMessage={errors.slug}
                                description={`سيكون النطاق: ${form.slug}.${typeof window !== "undefined" ? window.location.hostname : "menu.test"}`}
                            />

                            <Input
                                label="الهاتف"
                                placeholder="+966 5xxxxxxxxx"
                                value={form.phone}
                                onValueChange={(v) => setForm({ ...form, phone: v })}
                                isInvalid={!!errors.phone}
                                errorMessage={errors.phone}
                            />

                            <Input
                                label="الموقع"
                                placeholder="البصرة، العراق"
                                value={form.location}
                                onValueChange={(v) => setForm({ ...form, location: v })}
                                isInvalid={!!errors.location}
                                errorMessage={errors.location}
                            />

                            <textarea
                                className="w-full h-24 px-3 py-2 text-sm rounded-xl bg-zinc-100 dark:bg-zinc-800 border-none outline-none text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 resize-none"
                                placeholder="وصف مختصر للعميل"
                                value={form.short_description}
                                onChange={(e) => setForm({ ...form, short_description: e.target.value })}
                            />
                            {errors.short_description && (
                                <p className="text-red-500 text-xs">{errors.short_description}</p>
                            )}

                            {isEditing && (
                                <Switch
                                    isSelected={form.is_active}
                                    onValueChange={(v) => setForm({ ...form, is_active: v })}
                                >
                                    نشط
                                </Switch>
                            )}

                            <div className="flex gap-3 justify-end mt-4">
                                <Button
                                    variant="flat"
                                    onPress={() => router.get(route("central.tenants.index"))}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    color="primary"
                                    onPress={handleSubmit}
                                >
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
