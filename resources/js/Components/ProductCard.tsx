import React from 'react';
import { Link } from '@inertiajs/react';
import { Product } from '@/Types';
import { StarIcon } from '@heroicons/react/24/solid';
import { StarIcon as StarOutlineIcon } from '@heroicons/react/24/outline';

interface ProductCardProps {
    product: Product;
}

export default function ProductCard({ product }: ProductCardProps) {
    const renderStars = (rating: number) => {
        const stars = [];
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;

        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                stars.push(
                    <StarIcon key={i} className="h-4 w-4 text-yellow-400" />
                );
            } else if (i === fullStars && hasHalfStar) {
                stars.push(
                    <div key={i} className="relative">
                        <StarOutlineIcon className="h-4 w-4 text-gray-300" />
                        <div className="absolute inset-0 overflow-hidden w-1/2">
                            <StarIcon className="h-4 w-4 text-yellow-400" />
                        </div>
                    </div>
                );
            } else {
                stars.push(
                    <StarOutlineIcon key={i} className="h-4 w-4 text-gray-300" />
                );
            }
        }

        return stars;
    };

    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <Link href={route('products.show', product.slug)}>
                <div className="aspect-square overflow-hidden">
                    <img
                        src={product.main_image || '/placeholder-product.png'}
                        alt={product.name}
                        className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    />
                </div>
                
                <div className="p-4">
                    <h3 className="text-lg font-medium text-gray-900 mb-2 line-clamp-2">
                        {product.name}
                    </h3>
                    
                    {product.short_description && (
                        <p className="text-sm text-gray-600 mb-3 line-clamp-2">
                            {product.short_description}
                        </p>
                    )}
                    
                    <div className="flex items-center mb-2">
                        <div className="flex items-center space-x-1">
                            {renderStars(product.average_rating)}
                        </div>
                        <span className="text-sm text-gray-500 ml-2">
                            ({product.reviews_count})
                        </span>
                    </div>
                    
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-2">
                            <span className="text-xl font-bold text-gray-900">
                                {product.formatted_price}
                            </span>
                            {product.compare_price && product.compare_price > product.price && (
                                <>
                                    <span className="text-sm text-gray-500 line-through">
                                        ${product.compare_price.toFixed(2)}
                                    </span>
                                    {product.discount_percentage && (
                                        <span className="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">
                                            -{product.discount_percentage}%
                                        </span>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                    
                    <div className="mt-3">
                        {product.track_quantity && product.quantity > 0 ? (
                            <span className="text-sm text-green-600">
                                ✓ In Stock
                            </span>
                        ) : product.track_quantity ? (
                            <span className="text-sm text-red-600">
                                Out of Stock
                            </span>
                        ) : (
                            <span className="text-sm text-green-600">
                                ✓ Available
                            </span>
                        )}
                    </div>
                    
                    {product.is_featured && (
                        <div className="mt-2">
                            <span className="bg-indigo-100 text-indigo-800 text-xs font-medium px-2 py-1 rounded">
                                Featured
                            </span>
                        </div>
                    )}
                </div>
            </Link>
        </div>
    );
}