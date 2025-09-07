import React from 'react';
import { Link } from '@inertiajs/react';

interface SecondaryButtonProps {
    href?: string;
    onClick?: () => void;
    disabled?: boolean;
    type?: 'button' | 'submit' | 'reset';
    className?: string;
    children: React.ReactNode;
}

export default function SecondaryButton({
    href,
    onClick,
    disabled = false,
    type = 'button',
    className = '',
    children,
    ...props
}: SecondaryButtonProps) {
    const baseClasses = 'inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150';
    
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