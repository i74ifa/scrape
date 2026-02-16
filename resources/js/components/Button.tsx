
import React from 'react';

interface ButtonProps {
  children: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'outline';
  onClick?: () => void;
  className?: string;
  type?: "button" | "submit";
}

export const Button: React.FC<ButtonProps> = ({ 
  children, 
  variant = 'primary', 
  onClick, 
  className = "",
  type = "button"
}) => {
  const baseStyles = "px-8 py-3 rounded-full font-bold transition-all duration-300 text-center";
  const variants = {
    primary: "bg-blue-600 text-white hover:bg-blue-700",
    secondary: "bg-[#333333] text-white hover:bg-[#444444]",
    outline: "border-2 border-[#333333] text-white hover:bg-[#333333]"
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
