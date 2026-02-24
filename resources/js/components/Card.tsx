import React from "react";

interface CardProps {
    children: React.ReactNode;
    className?: string;
}

export const Card: React.FC<CardProps> = ({ children, className = "" }) => {
    return (
        <div className={`bg-surface rounded-[2.5rem] p-8 ${className}`}>
            {children}
        </div>
    );
};
