import {
    CheckCircle,
    Shield,
    Sparkles,
    Star,
    Zap,
    Infinity,
} from "lucide-react";

export default function PlanCard({
    plan,
    currentPlanSlug,
    onSelect,
    isSelected,
    billingCycle,
}) {
    const colors = {
        blue: {
            border: isSelected
                ? "border-blue-500"
                : "border-blue-100 dark:border-blue-900/50",
            icon: "bg-blue-100 dark:bg-blue-900/40 text-blue-500",
            badge: "bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300",
            ring: "ring-blue-500/30",
            glow: isSelected ? "shadow-blue-500/20 shadow-lg" : "",
        },
        purple: {
            border: isSelected
                ? "border-purple-500"
                : "border-purple-100 dark:border-purple-900/50",
            icon: "bg-purple-100 dark:bg-purple-900/40 text-purple-500",
            badge: "bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300",
            ring: "ring-purple-500/30",
            glow: isSelected ? "shadow-purple-500/20 shadow-lg" : "",
        },
        amber: {
            border: isSelected
                ? "border-amber-500"
                : "border-amber-100 dark:border-amber-900/50",
            icon: "bg-amber-100 dark:bg-amber-900/40 text-amber-500",
            badge: "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300",
            ring: "ring-amber-500/30",
            glow: isSelected ? "shadow-amber-500/20 shadow-lg" : "",
        },
        emerald: {
            border: isSelected
                ? "border-emerald-500"
                : "border-emerald-100 dark:border-emerald-900/50",
            icon: "bg-emerald-100 dark:bg-emerald-900/40 text-emerald-500",
            badge: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300",
            ring: "ring-emerald-500/30",
            glow: isSelected ? "shadow-emerald-500/20 shadow-lg" : "",
        },
    };

    const c = colors[plan.color] || colors.blue;
    const isCurrent = currentPlanSlug === plan.slug;

    const priceObj = plan.prices?.[billingCycle];
    const price = priceObj?.amount ?? null;
    const currency = priceObj?.currency ?? "IQD";
    const periodLabel = billingCycle === "yearly" ? "سنوياً" : "شهرياً";

    // Yearly savings badge
    const monthlyPrice = plan.prices?.["monthly"]?.amount;
    const yearlyPrice = plan.prices?.["yearly"]?.amount;
    const yearlySaving =
        billingCycle === "yearly" && monthlyPrice && yearlyPrice
            ? Math.round(
                  ((monthlyPrice * 12 - yearlyPrice) / (monthlyPrice * 12)) *
                      100,
              )
            : null;

    return (
        <div
            className={`relative rounded-3xl border-2 bg-white dark:bg-zinc-900 p-6 transition-all cursor-pointer ${c.border} ${c.glow} ${isSelected ? `ring-2 ${c.ring} ring-offset-2 ring-offset-zinc-50 dark:ring-offset-zinc-950` : "hover:border-opacity-80"}`}
            onClick={() =>
                onSelect({ ...plan, selectedPrice: priceObj, billingCycle })
            }
        >
            {plan.popular && (
                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-purple-600 text-white shadow-lg">
                        <Sparkles size={10} />
                        الأكثر شيوعاً
                    </span>
                </div>
            )}

            {yearlySaving && (
                <div className="absolute top-3 left-3">
                    <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white shadow">
                        وفّر %{yearlySaving}
                    </span>
                </div>
            )}

            <div className="flex items-start justify-between mb-4">
                <div
                    className={`w-12 h-12 rounded-2xl flex items-center justify-center ${c.icon}`}
                >
                    {plan.color === "amber" ? (
                        <Star size={22} />
                    ) : plan.color === "purple" ? (
                        <Zap size={22} />
                    ) : plan.color === "emerald" ? (
                        <Infinity size={22} />
                    ) : (
                        <Shield size={22} />
                    )}
                </div>
                {isCurrent && (
                    <span
                        className={`text-xs font-bold px-2.5 py-1 rounded-full ${c.badge}`}
                    >
                        خطتك الحالية
                    </span>
                )}
                {isSelected && !isCurrent && (
                    <div className="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                        <CheckCircle size={14} className="text-white" />
                    </div>
                )}
            </div>

            <h3 className="font-black text-lg text-zinc-800 dark:text-zinc-100 mb-1">
                {plan.name}
            </h3>

            {price !== null ? (
                <div className="flex items-baseline gap-1 mb-4">
                    <span className="text-3xl font-black text-zinc-900 dark:text-zinc-50">
                        {price.toLocaleString()}
                    </span>
                    <span className="text-xs text-zinc-400 dark:text-zinc-500 mr-0.5">
                        {currency}
                    </span>
                    <span className="text-sm text-zinc-500 dark:text-zinc-400">
                        / {periodLabel}
                    </span>
                </div>
            ) : (
                <div className="flex items-baseline gap-1 mb-4">
                    <span className="text-sm text-zinc-400 dark:text-zinc-500">
                        لا يوجد سعر متاح
                    </span>
                </div>
            )}

            <ul className="space-y-2">
                {plan.features.map((feature, i) => (
                    <li
                        key={i}
                        className="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400"
                    >
                        <CheckCircle
                            size={14}
                            className={`${feature.value ? "text-emerald-500" : "text-red-500"} shrink-0`}
                        />
                        {feature.label}
                    </li>
                ))}
            </ul>
        </div>
    );
}
