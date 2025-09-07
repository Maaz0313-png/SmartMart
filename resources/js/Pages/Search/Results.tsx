import React, { useState, useEffect } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { type SearchResults, type SearchFacets } from '@/Types/index';
import ProductCard from '@/Components/ProductCard';
import SearchFilters from '@/Components/SearchFilters';
import Pagination from '@/Components/Pagination';
import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';

interface Props {
    query: string;
    results: SearchResults;
    suggestions: string[];
    trendingSearches: string[];
    filters: {
        category?: string;
        min_price?: string;
        max_price?: string;
        sort?: string;
        offset?: number;
    };
    facets: SearchFacets;
}

export default function SearchResults({
    query,
    results,
    suggestions,
    trendingSearches,
    filters,
    facets
}: Props) {
    const { auth } = usePage().props as any;
    const [searchQuery, setSearchQuery] = useState(query);
    const [activeFilters, setActiveFilters] = useState(filters);
    const [isLoading, setIsLoading] = useState(false);

    const handleSearch = (newQuery?: string) => {
        setIsLoading(true);
        
        const searchParams = new URLSearchParams();
        const queryToUse = newQuery || searchQuery;
        
        searchParams.set('q', queryToUse);
        
        Object.entries(activeFilters).forEach(([key, value]) => {
            if (value) {
                searchParams.set(key, value.toString());
            }
        });

        router.get(route('search'), Object.fromEntries(searchParams), {
            preserveState: true,
            onFinish: () => setIsLoading(false),
        });
    };

    const handleFilterChange = (key: string, value: any) => {
        setActiveFilters(prev => ({ ...prev, [key]: value }));
    };

    const clearFilters = () => {
        setActiveFilters({});
        router.get(route('search'), { q: query });
    };

    useEffect(() => {
        setSearchQuery(query);
    }, [query]);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Search: ${query}`} />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Search Header */}
                    <div className="mb-8">
                        <div className="flex items-center space-x-4 mb-4">
                            <div className="flex-1 max-w-2xl">
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                        placeholder="Search products..."
                                        className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <button
                                onClick={() => handleSearch()}
                                disabled={isLoading}
                                className="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {isLoading ? 'Searching...' : 'Search'}
                            </button>
                        </div>

                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900">
                                    Search Results for "{query}"
                                </h1>
                                <p className="text-gray-600">
                                    {results.total} products found in {results.processing_time}ms
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        {/* Filters Sidebar */}
                        <div className="lg:col-span-1">
                            <div className="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">Filters</h3>
                                    <button
                                        onClick={clearFilters}
                                        className="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        Clear all
                                    </button>
                                </div>

                                <SearchFilters
                                    facets={facets}
                                    activeFilters={activeFilters}
                                    onFilterChange={handleFilterChange}
                                    onApplyFilters={() => handleSearch(query)}
                                />

                                {/* Suggestions */}
                                {suggestions.length > 0 && (
                                    <div className="mt-6">
                                        <h4 className="font-medium text-gray-900 mb-3">Suggestions</h4>
                                        <div className="space-y-2">
                                            {suggestions.map((suggestion, index) => (
                                                <button
                                                    key={index}
                                                    onClick={() => handleSearch(suggestion)}
                                                    className="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded"
                                                >
                                                    {suggestion}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Trending Searches */}
                                {trendingSearches.length > 0 && (
                                    <div className="mt-6">
                                        <h4 className="font-medium text-gray-900 mb-3">Trending</h4>
                                        <div className="flex flex-wrap gap-2">
                                            {trendingSearches.map((trend, index) => (
                                                <button
                                                    key={index}
                                                    onClick={() => handleSearch(trend)}
                                                    className="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200"
                                                >
                                                    {trend}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Results */}
                        <div className="lg:col-span-3">
                            {results.total > 0 ? (
                                <>
                                    {/* Sort Options */}
                                    <div className="flex justify-between items-center mb-6">
                                        <span className="text-sm text-gray-600">
                                            Showing {Math.min(24, results.total)} of {results.total} results
                                        </span>
                                        <select
                                            value={activeFilters.sort || 'relevance'}
                                            onChange={(e) => {
                                                handleFilterChange('sort', e.target.value);
                                                handleSearch(query);
                                            }}
                                            className="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                                        >
                                            <option value="relevance">Most Relevant</option>
                                            <option value="popularity">Most Popular</option>
                                            <option value="price_low">Price: Low to High</option>
                                            <option value="price_high">Price: High to Low</option>
                                            <option value="newest">Newest First</option>
                                            <option value="rating">Highest Rated</option>
                                        </select>
                                    </div>

                                    {/* Products Grid */}
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                        {results.hits.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </div>

                                    {/* Pagination */}
                                    {results.total > 24 && (
                                        <div className="flex justify-center">
                                            <Pagination 
                                                currentPage={Math.floor((activeFilters.offset || 0) / 24) + 1}
                                                totalPages={Math.ceil(results.total / 24)}
                                                onPageChange={(page: number) => {
                                                    handleFilterChange('offset', (page - 1) * 24);
                                                    handleSearch(query);
                                                }}
                                            />
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <MagnifyingGlassIcon className="mx-auto h-16 w-16 text-gray-400 mb-4" />
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        No results found
                                    </h3>
                                    <p className="text-gray-600 mb-4">
                                        We couldn't find any products matching "{query}".
                                    </p>
                                    <div className="space-y-2">
                                        <p className="text-sm text-gray-500">Try:</p>
                                        <ul className="text-sm text-gray-600 space-y-1">
                                            <li>• Check your spelling</li>
                                            <li>• Use fewer or different keywords</li>
                                            <li>• Browse our categories</li>
                                        </ul>
                                    </div>
                                    
                                    {suggestions.length > 0 && (
                                        <div className="mt-6">
                                            <p className="text-sm text-gray-500 mb-3">Did you mean:</p>
                                            <div className="flex flex-wrap justify-center gap-2">
                                                {suggestions.map((suggestion, index) => (
                                                    <button
                                                        key={index}
                                                        onClick={() => handleSearch(suggestion)}
                                                        className="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full hover:bg-indigo-200 text-sm"
                                                    >
                                                        {suggestion}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}