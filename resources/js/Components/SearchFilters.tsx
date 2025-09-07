import React from 'react';
import { SearchFacets } from '@/Types';

interface SearchFiltersProps {
    facets: SearchFacets;
    activeFilters: Record<string, any>;
    onFilterChange: (key: string, value: any) => void;
    onApplyFilters: () => void;
}

export default function SearchFilters({
    facets,
    activeFilters,
    onFilterChange,
    onApplyFilters
}: SearchFiltersProps) {
    return (
        <div className="space-y-6">
            {/* Categories */}
            {facets.categories && facets.categories.length > 0 && (
                <div>
                    <h4 className="font-medium text-gray-900 mb-3">Categories</h4>
                    <div className="space-y-2 max-h-48 overflow-y-auto">
                        {facets.categories.map((category) => (
                            <label key={category.value} className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={activeFilters.category === category.value}
                                    onChange={(e) => {
                                        onFilterChange('category', e.target.checked ? category.value : '');
                                    }}
                                    className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span className="ml-2 text-sm text-gray-700">
                                    {category.name} ({category.count})
                                </span>
                            </label>
                        ))}
                    </div>
                </div>
            )}

            {/* Price Range */}
            <div>
                <h4 className="font-medium text-gray-900 mb-3">Price Range</h4>
                <div className="grid grid-cols-2 gap-2">
                    <div>
                        <input
                            type="number"
                            placeholder="Min"
                            value={activeFilters.min_price || ''}
                            onChange={(e) => onFilterChange('min_price', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <input
                            type="number"
                            placeholder="Max"
                            value={activeFilters.max_price || ''}
                            onChange={(e) => onFilterChange('max_price', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>
                
                {/* Predefined Price Ranges */}
                {facets.price_ranges && facets.price_ranges.length > 0 && (
                    <div className="mt-3 space-y-2">
                        {facets.price_ranges.map((range, index) => (
                            <label key={index} className="flex items-center">
                                <input
                                    type="radio"
                                    name="price_range"
                                    checked={
                                        activeFilters.min_price == range.min &&
                                        activeFilters.max_price == range.max
                                    }
                                    onChange={() => {
                                        onFilterChange('min_price', range.min);
                                        onFilterChange('max_price', range.max);
                                    }}
                                    className="text-indigo-600 focus:ring-indigo-500"
                                />
                                <span className="ml-2 text-sm text-gray-700">
                                    {range.name} ({range.count})
                                </span>
                            </label>
                        ))}
                    </div>
                )}
            </div>

            {/* Brands */}
            {facets.brands && facets.brands.length > 0 && (
                <div>
                    <h4 className="font-medium text-gray-900 mb-3">Brands</h4>
                    <div className="space-y-2 max-h-48 overflow-y-auto">
                        {facets.brands.map((brand) => (
                            <label key={brand.value} className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={activeFilters.brand === brand.value}
                                    onChange={(e) => {
                                        onFilterChange('brand', e.target.checked ? brand.value : '');
                                    }}
                                    className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span className="ml-2 text-sm text-gray-700">
                                    {brand.name} ({brand.count})
                                </span>
                            </label>
                        ))}
                    </div>
                </div>
            )}

            {/* Ratings */}
            {facets.ratings && facets.ratings.length > 0 && (
                <div>
                    <h4 className="font-medium text-gray-900 mb-3">Customer Ratings</h4>
                    <div className="space-y-2">
                        {facets.ratings.map((rating) => (
                            <label key={rating.rating} className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={activeFilters.min_rating == rating.rating}
                                    onChange={(e) => {
                                        onFilterChange('min_rating', e.target.checked ? rating.rating : '');
                                    }}
                                    className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span className="ml-2 text-sm text-gray-700 flex items-center">
                                    {rating.rating}+ ⭐ ({rating.count})
                                </span>
                            </label>
                        ))}
                    </div>
                </div>
            )}

            {/* Apply Filters Button */}
            <div className="pt-4 border-t">
                <button
                    onClick={onApplyFilters}
                    className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 text-sm font-medium"
                >
                    Apply Filters
                </button>
            </div>
        </div>
    );
}