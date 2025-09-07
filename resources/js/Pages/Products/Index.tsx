import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product, Category, PaginatedData } from '@/Types';
import ProductCard from '@/Components/ProductCard';
import SearchFilters from '@/Components/SearchFilters';
import Pagination from '@/Components/Pagination';

interface Props {
    products: PaginatedData<Product>;
    categories: Category[];
    filters: {
        category?: string;
        search?: string;
        min_price?: string;
        max_price?: string;
        sort?: string;
    };
}

export default function ProductIndex({ products, categories, filters }: Props) {
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters.category || '');
    const [priceRange, setPriceRange] = useState({
        min: filters.min_price || '',
        max: filters.max_price || '',
    });
    const [sortBy, setSortBy] = useState(filters.sort || 'newest');

    const handleSearch = () => {
        const searchParams = new URLSearchParams();
        
        if (searchQuery) searchParams.set('search', searchQuery);
        if (selectedCategory) searchParams.set('category', selectedCategory);
        if (priceRange.min) searchParams.set('min_price', priceRange.min);
        if (priceRange.max) searchParams.set('max_price', priceRange.max);
        if (sortBy !== 'newest') searchParams.set('sort', sortBy);

        router.get(route('products.index'), Object.fromEntries(searchParams));
    };

    const clearFilters = () => {
        setSearchQuery('');
        setSelectedCategory('');
        setPriceRange({ min: '', max: '' });
        setSortBy('newest');
        router.get(route('products.index'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Products" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h1 className="text-3xl font-bold text-gray-900">Products</h1>
                                <div className="text-sm text-gray-500">
                                    {products.total} products found
                                </div>
                            </div>

                            <SearchFilters
                                searchQuery={searchQuery}
                                setSearchQuery={setSearchQuery}
                                selectedCategory={selectedCategory}
                                setSelectedCategory={setSelectedCategory}
                                categories={categories}
                                priceRange={priceRange}
                                setPriceRange={setPriceRange}
                                sortBy={sortBy}
                                setSortBy={setSortBy}
                                onSearch={handleSearch}
                                onClear={clearFilters}
                            />

                            {products.data.length > 0 ? (
                                <>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                                        {products.data.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </div>

                                    <Pagination 
                                        data={products} 
                                        preserveScroll 
                                        preserveState 
                                    />
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <div className="text-gray-500 text-lg mb-4">
                                        No products found matching your criteria.
                                    </div>
                                    <button
                                        onClick={clearFilters}
                                        className="text-indigo-600 hover:text-indigo-500 font-medium"
                                    >
                                        Clear all filters
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}