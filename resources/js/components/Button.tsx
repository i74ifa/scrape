import React from "react";

interface ButtonProps {
    children: React.ReactNode;
    variant?: "primary" | "secondary" | "outline";
    onClick?: () => void;
    className?: string;
    type?: "button" | "submit";
}

export const Button: React.FC<ButtonProps> = ({
    children,
    variant = "primary",
    onClick,
    className = "",
    type = "button",
}) => {
    const baseStyles =
        "px-8 py-3 rounded-full font-bold transition-all duration-300 text-center";
    const variants = {
        primary: "bg-brand-primary text-white hover:opacity-90",
        secondary: "bg-brand-secondary text-text-main hover:opacity-80",
        outline:
            "border-2 border-border-subtle text-text-main hover:bg-surface",
    };

    return (
        <button
            type={type}
            onClick={onClick}
            className={`${baseStyles} ${variants[variant]} ${className}`}
        >
            {children}
        </button>
    );
};
