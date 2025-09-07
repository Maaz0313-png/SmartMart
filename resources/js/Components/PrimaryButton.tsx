import React from 'react';
import { Link } from '@inertiajs/react';

interface PrimaryButtonProps {
    href?: string;
    onClick?: () => void;
    disabled?: boolean;
    type?: 'button' | 'submit' | 'reset';
    className?: string;
    children: React.ReactNode;
}

export default function PrimaryButton({
    href,
    onClick,
    disabled = false,
    type = 'button',
    className = '',
    children,
    ...props
}: PrimaryButtonProps) {
    const baseClasses = 'inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150';
    
    const disabledClasses = disabled 
        ? 'opacity-50 cursor-not-allowed' 
        : '';
    
    const finalClasses = `${baseClasses} ${disabledClasses} ${className}`;

    if (href) {
        return (
            <Link
                href={href}
                className={finalClasses}
                {...props}
            >
                {children}
            </Link>
        );
    }

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={disabled}
            className={finalClasses}
            {...props}
        >
            {children}
        </button>
    );
}