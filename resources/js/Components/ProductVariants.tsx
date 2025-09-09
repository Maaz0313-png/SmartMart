import React, { useState } from 'react';

interface ProductVariant {
    id: number;
    name: string;
    value: string;
    type: 'color' | 'size' | 'material' | 'other';
    price_adjustment?: number;
    stock?: number;
}

interface ProductVariantsProps {
    variants: ProductVariant[];
    onVariantSelect: (variants: { [key: string]: any }) => void;
    selectedVariants?: { [key: string]: any };
}

export default function ProductVariants({ variants, onVariantSelect, selectedVariants = {} }: ProductVariantsProps) {
    const [selected, setSelected] = useState<{ [key: string]: any }>(selectedVariants);

    // Group variants by type
    const groupedVariants = variants.reduce((acc, variant) => {
        if (!acc[variant.type]) {
            acc[variant.type] = [];
        }
        acc[variant.type].push(variant);
        return acc;
    }, {} as { [key: string]: ProductVariant[] });

    const handleVariantChange = (type: string, variant: ProductVariant) => {
        const newSelected = {
            ...selected,
            [type]: variant
        };
        setSelected(newSelected);
        onVariantSelect(newSelected);
    };

    const renderColorVariants = (variants: ProductVariant[]) => (
        <div className="flex flex-wrap gap-2">
            {variants.map((variant) => (
                <button
                    key={variant.id}
                    onClick={() => handleVariantChange('color', variant)}
                    className={`w-8 h-8 rounded-full border-2 transition-all ${
                        selected.color?.id === variant.id
                            ? 'border-gray-800 scale-110'
                            : 'border-gray-300 hover:border-gray-500'
                    }`}
                    style={{ backgroundColor: variant.value.toLowerCase() }}
                    title={variant.name}
                    disabled={variant.stock === 0}
                >
                    {variant.stock === 0 && (
                        <div className="w-full h-full rounded-full bg-white bg-opacity-50 flex items-center justify-center">
                            <span className="text-xs text-red-500">✕</span>
                        </div>
                    )}
                </button>
            ))}
        </div>
    );

    const renderSizeVariants = (variants: ProductVariant[]) => (
        <div className="flex flex-wrap gap-2">
            {variants.map((variant) => (
                <button
                    key={variant.id}
                    onClick={() => handleVariantChange('size', variant)}
                    className={`px-4 py-2 border rounded transition-all ${
                        selected.size?.id === variant.id
                            ? 'border-gray-800 bg-gray-800 text-white'
                            : 'border-gray-300 hover:border-gray-500 bg-white text-gray-700'
                    } ${variant.stock === 0 ? 'opacity-50 cursor-not-allowed' : ''}`}
                    disabled={variant.stock === 0}
                >
                    {variant.name}
                    {variant.stock === 0 && <span className="ml-1 text-xs">(Out of Stock)</span>}
                </button>
            ))}
        </div>
    );

    const renderGenericVariants = (variants: ProductVariant[], type: string) => (
        <div className="space-y-2">
            {variants.map((variant) => (
                <label key={variant.id} className="flex items-center space-x-2">
                    <input
                        type="radio"
                        name={type}
                        checked={selected[type]?.id === variant.id}
                        onChange={() => handleVariantChange(type, variant)}
                        disabled={variant.stock === 0}
                        className="text-blue-600"
                    />
                    <span className={variant.stock === 0 ? 'text-gray-400 line-through' : ''}>
                        {variant.name}
                        {variant.price_adjustment && variant.price_adjustment !== 0 && (
                            <span className="text-sm text-gray-600 ml-1">
                                ({variant.price_adjustment > 0 ? '+' : ''}${Math.abs(variant.price_adjustment)})
                            </span>
                        )}
                        {variant.stock === 0 && <span className="ml-1 text-xs text-red-500">(Out of Stock)</span>}
                    </span>
                </label>
            ))}
        </div>
    );

    if (Object.keys(groupedVariants).length === 0) {
        return null;
    }

    return (
        <div className="space-y-6">
            {Object.entries(groupedVariants).map(([type, variants]) => (
                <div key={type} className="space-y-3">
                    <h3 className="text-sm font-medium text-gray-900 capitalize">
                        {type}
                        {selected[type] && (
                            <span className="ml-2 text-gray-600 font-normal">
                                - {selected[type].name}
                            </span>
                        )}
                    </h3>
                    
                    {type === 'color' ? renderColorVariants(variants) :
                     type === 'size' ? renderSizeVariants(variants) :
                     renderGenericVariants(variants, type)}
                </div>
            ))}
        </div>
    );
}