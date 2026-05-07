export default function PaymentGatewayCard({ gateway, isSelected, onSelect }) {
    return (
        <button
            onClick={() => onSelect(gateway.id)}
            className={`w-full flex items-center gap-4 p-4 rounded-2xl border-2 transition-all text-right ${
                isSelected
                    ? "border-blue-500 bg-blue-50 dark:bg-blue-900/10"
                    : "border-zinc-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-700 bg-white dark:bg-zinc-900"
            }`}
        >
            <div className="w-16 h-10 rounded-xl overflow-hidden bg-white border border-zinc-100 dark:border-zinc-700 flex items-center justify-center shrink-0">
                <img
                    src={gateway.logo}
                    alt={gateway.name}
                    className="w-full h-full object-contain"
                />
            </div>
            <div className="flex-1 text-right">
                <p className="font-bold text-sm text-zinc-800 dark:text-zinc-200">
                    {gateway.name}
                </p>
                <p className="text-xs text-zinc-500 dark:text-zinc-400">
                    {gateway.description}
                </p>
            </div>
            <div
                className={`w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center transition-all ${
                    isSelected
                        ? "border-blue-500 bg-blue-500"
                        : "border-zinc-300 dark:border-zinc-600"
                }`}
            >
                {isSelected && (
                    <div className="w-2 h-2 rounded-full bg-white" />
                )}
            </div>
        </button>
    );
}
