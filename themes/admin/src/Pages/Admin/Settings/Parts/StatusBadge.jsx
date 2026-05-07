import {
    Image as ImageIcon,
    CheckCircle,
    Gift,
    AlertCircle,
} from "lucide-react";

export default function StatusBadge({ status, isTrial, trialEndsAt }) {
    console.log(trialEndsAt);
    if (isTrial) {
        const daysLeft = trialEndsAt
            ? Math.ceil(
                  (new Date(trialEndsAt) - new Date()) / (1000 * 60 * 60 * 24),
              )
            : 0;
        return (
            <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                <Gift size={12} />
                تجريبية · {daysLeft > 0 ? `${daysLeft} يوم متبقي` : "منتهية"}
            </span>
        );
    }

    const map = {
        active: {
            label: "نشط",
            icon: CheckCircle,
            cls: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800",
        },
        expired: {
            label: "منتهي",
            icon: AlertCircle,
            cls: "bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border-red-200 dark:border-red-800",
        },
        canceled: {
            label: "ملغي",
            icon: AlertCircle,
            cls: "bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700",
        },
    };

    const cfg = map[status] || map["expired"];
    const Icon = cfg.icon;

    return (
        <span
            className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border ${cfg.cls}`}
        >
            <Icon size={12} />
            {cfg.label}
        </span>
    );
}
