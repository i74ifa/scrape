import { Card } from "@heroui/react";
import { AlertCircle, Calendar, Shield, Clock, RefreshCw } from "lucide-react";
import StatusBadge from "./StatusBadge";
export default function CurrentSubscriptionCard({ subscription, tenant }) {
    if (!subscription) {
        return (
            <Card className="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 shadow-none">
                <Card.Content className="p-6">
                    <div className="flex items-center gap-4">
                        <div className="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center shrink-0">
                            <AlertCircle className="text-red-500" size={28} />
                        </div>
                        <div>
                            <h3 className="font-bold text-xl text-red-800 dark:text-red-300">
                                لا يوجد اشتراك فعال
                            </h3>
                            <p className="text-sm text-red-600/80 dark:text-red-400/80 mt-1">
                                اشترك الآن للوصول إلى جميع المميزات
                            </p>
                        </div>
                    </div>
                </Card.Content>
            </Card>
        );
    }

    const isTrial =
        subscription?.trial_ends_at &&
        new Date(subscription.trial_ends_at) > new Date();
    const planName = subscription?.plan?.name || "—";
    const status = subscription?.status || "expired";
    const periodEnd = subscription?.current_period_end;
    const periodStart = subscription?.current_period_start;
    const renewDate = periodEnd
        ? new Date(new Date(periodEnd).getTime() + 30 * 24 * 60 * 60 * 1000)
        : null;

    const isActive = status === "active";

    return (
        <Card
            className={`shadow-none border ${
                isActive
                    ? "bg-emerald-50 dark:bg-emerald-900/10 py-0 border-emerald-100 dark:border-emerald-900/50"
                    : "bg-red-50 dark:bg-red-900/10 py-0 border-red-100 dark:border-red-900/50"
            }`}
        >
            <Card.Content className="py-3 ">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <div
                            className={`w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 ${
                                isActive
                                    ? "bg-emerald-100 dark:bg-emerald-900/40"
                                    : "bg-red-100 dark:bg-red-900/40"
                            }`}
                        >
                            <Shield
                                className={
                                    isActive
                                        ? "text-emerald-500"
                                        : "text-red-500"
                                }
                                size={28}
                            />
                        </div>
                        <div>
                            <div className="flex items-center gap-2 mb-1 flex-wrap">
                                <h3 className="font-black text-xl text-zinc-800 dark:text-zinc-100">
                                    {planName}
                                </h3>
                                <StatusBadge
                                    status={status}
                                    isTrial={isTrial}
                                    trialEndsAt={subscription?.trial_ends_at}
                                />
                            </div>
                            <div className="flex flex-wrap gap-4 mt-2">
                                {periodStart && (
                                    <div className="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <Calendar size={12} />
                                        <span>
                                            بدأ:{" "}
                                            {new Date(
                                                periodStart,
                                            ).toLocaleDateString("ar-IQ")}
                                        </span>
                                    </div>
                                )}
                                {periodEnd && (
                                    <div className="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <Clock size={12} />
                                        <span>
                                            ينتهي:{" "}
                                            {new Date(
                                                periodEnd,
                                            ).toLocaleDateString("ar-IQ")}
                                        </span>
                                    </div>
                                )}
                                {renewDate && isActive && (
                                    <div className="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                        <RefreshCw size={12} />
                                        <span>
                                            تجديد:{" "}
                                            {renewDate.toLocaleDateString(
                                                "ar-IQ",
                                            )}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {isTrial && (
                        <div className="bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-2xl px-4 py-2 text-center shrink-0">
                            <p className="text-xs text-amber-600 dark:text-amber-400/80 font-bold">
                                الفترة التجريبية
                            </p>
                            <p className="text-xs text-amber-700 dark:text-amber-300/80 mt-0.5">
                                تنتهي:{" "}
                                {new Date(
                                    subscription.trial_ends_at,
                                ).toLocaleDateString("ar-IQ")}
                            </p>
                        </div>
                    )}
                </div>
            </Card.Content>
        </Card>
    );
}
