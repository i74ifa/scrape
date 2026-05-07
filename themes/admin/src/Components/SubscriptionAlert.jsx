import React from "react";
import { Card, Button } from "@heroui/react";
import { AlertCircle, ArrowLeft } from "lucide-react";
import { Link } from "@inertiajs/react";
import { router } from "@inertiajs/react";

export default function SubscriptionAlert({
    title = "إشتراكك منتهي أو غير مفعل",
    message = "يرجى تفعيل اشتراكك لتجنب انقطاع الخدمات والوصول الكامل للمميزات.",
    buttonText = "تجديد الإشتراك",
}) {
    return (
        <Card className="mb-6 bg-linear-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800/50 shadow-none overflow-hidden p-0 sticky top-0 z-50">
            <div className="flex flex-col sm:flex-row items-center justify-between p-4 gap-4">
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                        <AlertCircle className="text-amber-600 dark:text-amber-400 w-6 h-6" />
                    </div>
                    <div>
                        <h4 className="font-bold text-amber-900 dark:text-amber-100 text-sm">
                            {title}
                        </h4>
                        <p className="text-xs text-amber-700/80 dark:text-amber-400/80 mt-0.5">
                            {message}
                        </p>
                    </div>
                </div>
                <Button
                    onClick={() =>
                        router.get(route("admin.subscription.index"))
                    }
                    className="rounded-full bg-amber-600 hover:bg-amber-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all"
                >
                    {buttonText}
                    <ArrowLeft
                        size={18}
                        className="group-hover:-translate-x-1 transition-transform"
                    />
                </Button>
            </div>
        </Card>
    );
}
