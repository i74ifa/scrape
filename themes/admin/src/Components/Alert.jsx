import React from "react";
import { Card, Button } from "@heroui/react";
import { AlertCircle, ArrowLeft } from "lucide-react";
import { Link } from "@inertiajs/react";

export default function SubscriptionAlert({
    title,
    message,
    buttonText,
    buttonLink,
    icon,
    color,
}) {
    const colors = {
        red: {
            bg: "bg-red-100 dark:bg-red-900/40",
            border: "border-red-200 dark:border-red-800/50",
            icon: "text-red-600 dark:text-red-400",
            text: "text-red-700/80 dark:text-red-400/80",
            button: "bg-red-600 hover:bg-red-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        orange: {
            bg: "bg-orange-100 dark:bg-orange-900/40",
            border: "border-orange-200 dark:border-orange-800/50",
            icon: "text-orange-600 dark:text-orange-400",
            text: "text-orange-700/80 dark:text-orange-400/80",
            button: "bg-orange-600 hover:bg-orange-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        yellow: {
            bg: "bg-yellow-100 dark:bg-yellow-900/40",
            border: "border-yellow-200 dark:border-yellow-800/50",
            icon: "text-yellow-600 dark:text-yellow-400",
            text: "text-yellow-700/80 dark:text-yellow-400/80",
            button: "bg-yellow-600 hover:bg-yellow-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        green: {
            bg: "bg-green-100 dark:bg-green-900/40",
            border: "border-green-200 dark:border-green-800/50",
            icon: "text-green-600 dark:text-green-400",
            text: "text-green-700/80 dark:text-green-400/80",
            button: "bg-green-600 hover:bg-green-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        blue: {
            bg: "bg-blue-100 dark:bg-blue-900/40",
            border: "border-blue-200 dark:border-blue-800/50",
            icon: "text-blue-600 dark:text-blue-400",
            text: "text-blue-700/80 dark:text-blue-400/80",
            button: "bg-blue-600 hover:bg-blue-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        purple: {
            bg: "bg-purple-100 dark:bg-purple-900/40",
            border: "border-purple-200 dark:border-purple-800/50",
            icon: "text-purple-600 dark:text-purple-400",
            text: "text-purple-700/80 dark:text-purple-400/80",
            button: "bg-purple-600 hover:bg-purple-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        pink: {
            bg: "bg-pink-100 dark:bg-pink-900/40",
            border: "border-pink-200 dark:border-pink-800/50",
            icon: "text-pink-600 dark:text-pink-400",
            text: "text-pink-700/80 dark:text-pink-400/80",
            button: "bg-pink-600 hover:bg-pink-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
        gray: {
            bg: "bg-gray-100 dark:bg-gray-900/40",
            border: "border-gray-200 dark:border-gray-800/50",
            icon: "text-gray-600 dark:text-gray-400",
            text: "text-gray-700/80 dark:text-gray-400/80",
            button: "bg-gray-600 hover:bg-gray-700 text-white font-black px-6 h-11 flex items-center gap-2 group transition-all",
        },
    };

    const colorClass = colors[color] || colors.red;

    const Icon = icon;

    return (
        <Card
            className={`mb-6 bg-linear-to-r ${colorClass.bg} ${colorClass.border} shadow-none overflow-hidden p-0 sticky top-0 z-50`}
        >
            <div className="flex flex-col sm:flex-row items-center justify-between p-4 gap-4">
                <div className="flex items-center gap-4">
                    <div
                        className={`w-12 h-12 rounded-2xl ${colorClass.bg} ${colorClass.border} flex items-center justify-center shrink-0`}
                    >
                        <Icon className={`w-6 h-6 ${colorClass.icon}`} />
                    </div>
                    <div>
                        <h4 className={`font-bold ${colorClass.text} text-sm`}>
                            {title}
                        </h4>
                        <p className={`text-xs ${colorClass.text} mt-0.5`}>
                            {message}
                        </p>
                    </div>
                </div>
                <Link href={buttonLink} target="_blank">
                    <Button className={`rounded-full ${colorClass.button}`}>
                        {buttonText}
                        <ArrowLeft
                            size={18}
                            className="group-hover:-translate-x-1 transition-transform"
                        />
                    </Button>
                </Link>
            </div>
        </Card>
    );
}
