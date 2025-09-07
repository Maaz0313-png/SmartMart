import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product } from '@/types';
import { ShoppingCartIcon, HeartIcon, StarIcon } from '@heroicons/react/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/react/24/solid';
import ImageGallery from '@/Components/ImageGallery';
import ProductVariants from '@/Components/ProductVariants';
import ReviewsList from '@/Components/ReviewsList';
import ProductCard from '@/Components/ProductCard';

interface Props {
    product: Product;
    relatedProducts: Product[];
}

export default function ProductShow({ product, relatedProducts }: Props) {
    const [selectedVariant, setSelectedVariant] = useState(null);
    const [quantity, setQuantity] = useState(1);
    const [activeTab, setActiveTab] = useState('description');
    
    const { data, setData, post, processing } = useForm({
        product_id: product.id,
        variant_id: null,
        quantity: 1,
    });

    const addToCart = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('cart.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // Show success notification
            },
        });
    };

    const addToWishlist = () => {
        post(route('wishlist.store'), {
            product_id: product.id,
        });
    };

    const currentPrice = selectedVariant ? selectedVariant.price : product.price;
    const comparePrice = selectedVariant ? selectedVariant.compare_price : product.compare_price;
    const inStock = selectedVariant ? selectedVariant.quantity > 0 : product.quantity > 0;
    const maxQuantity = selectedVariant ? selectedVariant.quantity : product.quantity;

    return (
        <AuthenticatedLayout>
            <Head title={product.name} />

            <div className=\"max-w-7xl mx-auto py-6 sm:px-6 lg:px-8\">
                <nav className=\"flex mb-6\" aria-label=\"Breadcrumb\">
                    <ol className=\"inline-flex items-center space-x-1 md:space-x-3\">
                        <li className=\"inline-flex items-center\">
                            <Link href={route('home')} className=\"text-gray-700 hover:text-gray-900\">
                                Home
                            </Link>
                        </li>
                        <li>
                            <div className=\"flex items-center\">
                                <span className=\"mx-2 text-gray-400\">/</span>
                                <Link href={route('products.index')} className=\"text-gray-700 hover:text-gray-900\">
                                    Products
                                </Link>
                            </div>
                        </li>
                        {product.category && (
                            <li>
                                <div className=\"flex items-center\">
                                    <span className=\"mx-2 text-gray-400\">/</span>
                                    <Link
                                        href={route('products.index', { category: product.category.id })}
                                        className=\"text-gray-700 hover:text-gray-900\"
                                    >
                                        {product.category.name}
                                    </Link>
                                </div>
                            </li>
                        )}
                        <li>
                            <div className=\"flex items-center\">
                                <span className=\"mx-2 text-gray-400\">/</span>
                                <span className=\"text-gray-500\">{product.name}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div className=\"bg-white shadow-sm rounded-lg overflow-hidden\">
                    <div className=\"lg:grid lg:grid-cols-2 lg:gap-x-8\">
                        {/* Product images */}
                        <div className=\"aspect-w-1 aspect-h-1\">
                            <ImageGallery images={product.image_gallery} alt={product.name} />
                        </div>

                        {/* Product info */}
                        <div className=\"p-6 lg:p-8\">
                            <div className=\"mb-4\">
                                <h1 className=\"text-3xl font-bold text-gray-900 mb-2\">{product.name}</h1>
                                <div className=\"flex items-center space-x-4 mb-4\">
                                    <div className=\"flex items-center\">
                                        {[...Array(5)].map((_, i) => (
                                            i < Math.floor(product.average_rating) ? (
                                                <StarIconSolid key={i} className=\"h-5 w-5 text-yellow-400\" />
                                            ) : (
                                                <StarIcon key={i} className=\"h-5 w-5 text-gray-300\" />
                                            )
                                        ))}
                                        <span className=\"ml-2 text-sm text-gray-600\">
                                            ({product.reviews_count} reviews)
                                        </span>
                                    </div>
                                    <span className=\"text-sm text-gray-500\">SKU: {product.sku}</span>
                                </div>
                            </div>

                            <div className=\"mb-6\">
                                <div className=\"flex items-center space-x-4 mb-2\">
                                    <span className=\"text-3xl font-bold text-gray-900\">
                                        ${currentPrice.toFixed(2)}
                                    </span>
                                    {comparePrice && comparePrice > currentPrice && (
                                        <>
                                            <span className=\"text-xl text-gray-500 line-through\">
                                                ${comparePrice.toFixed(2)}
                                            </span>
                                            <span className=\"bg-red-100 text-red-800 text-sm font-medium px-2.5 py-0.5 rounded\">
                                                {product.discount_percentage}% off
                                            </span>
                                        </>
                                    )}
                                </div>
                                <div className=\"text-sm text-gray-600\">
                                    {inStock ? (
                                        <span className=\"text-green-600\">✓ In stock</span>
                                    ) : (
                                        <span className=\"text-red-600\">Out of stock</span>
                                    )}
                                </div>
                            </div>

                            {product.short_description && (
                                <div className=\"mb-6\">
                                    <p className=\"text-gray-700\">{product.short_description}</p>
                                </div>
                            )}

                            {/* Product variants */}
                            {product.variants && product.variants.length > 0 && (
                                <div className=\"mb-6\">
                                    <ProductVariants
                                        variants={product.variants}
                                        selectedVariant={selectedVariant}
                                        onVariantChange={setSelectedVariant}
                                    />
                                </div>
                            )}

                            {/* Quantity and Add to Cart */}
                            <form onSubmit={addToCart} className=\"mb-6\">
                                <div className=\"flex items-center space-x-4 mb-4\">
                                    <div>
                                        <label htmlFor=\"quantity\" className=\"block text-sm font-medium text-gray-700 mb-1\">
                                            Quantity
                                        </label>
                                        <select
                                            id=\"quantity\"
                                            value={quantity}
                                            onChange={(e) => {
                                                const qty = parseInt(e.target.value);
                                                setQuantity(qty);
                                                setData('quantity', qty);
                                            }}
                                            className=\"border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500\"
                                            disabled={!inStock}
                                        >
                                            {[...Array(Math.min(maxQuantity, 10))].map((_, i) => (
                                                <option key={i + 1} value={i + 1}>
                                                    {i + 1}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className=\"flex space-x-4\">
                                    <button
                                        type=\"submit\"
                                        disabled={!inStock || processing}
                                        className=\"flex-1 bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center space-x-2\"
                                    >
                                        <ShoppingCartIcon className=\"h-5 w-5\" />
                                        <span>{processing ? 'Adding...' : 'Add to Cart'}</span>
                                    </button>
                                    <button
                                        type=\"button\"
                                        onClick={addToWishlist}
                                        className=\"p-3 border border-gray-300 rounded-md hover:bg-gray-50 flex items-center justify-center\"
                                    >
                                        <HeartIcon className=\"h-5 w-5\" />
                                    </button>
                                </div>
                            </form>

                            {/* Product details tabs */}
                            <div className=\"border-t pt-6\">
                                <div className=\"flex space-x-8 mb-4\">
                                    <button
                                        onClick={() => setActiveTab('description')}
                                        className={`pb-2 border-b-2 ${activeTab === 'description' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'}`}
                                    >
                                        Description
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('specifications')}
                                        className={`pb-2 border-b-2 ${activeTab === 'specifications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'}`}
                                    >
                                        Specifications
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('shipping')}
                                        className={`pb-2 border-b-2 ${activeTab === 'shipping' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'}`}
                                    >
                                        Shipping
                                    </button>
                                </div>

                                <div className=\"mt-4\">
                                    {activeTab === 'description' && (
                                        <div className=\"prose max-w-none\" dangerouslySetInnerHTML={{ __html: product.description }} />
                                    )}
                                    {activeTab === 'specifications' && (
                                        <div className=\"grid grid-cols-2 gap-4\">
                                            <div><strong>Weight:</strong> {product.weight} {product.weight_unit}</div>
                                            {product.dimensions && (
                                                <div><strong>Dimensions:</strong> {product.dimensions.length} × {product.dimensions.width} × {product.dimensions.height}</div>
                                            )}
                                            <div><strong>SKU:</strong> {product.sku}</div>
                                            <div><strong>Category:</strong> {product.category?.name}</div>
                                        </div>
                                    )}
                                    {activeTab === 'shipping' && (
                                        <div>
                                            <p>Free shipping on orders over $100.</p>
                                            <p>Standard delivery: 5-7 business days</p>
                                            <p>Express delivery: 2-3 business days</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Reviews */}
                {product.reviews && product.reviews.length > 0 && (
                    <div className=\"mt-8\">
                        <ReviewsList reviews={product.reviews} productId={product.id} />
                    </div>
                )}

                {/* Related products */}
                {relatedProducts.length > 0 && (
                    <div className=\"mt-12\">
                        <h2 className=\"text-2xl font-bold text-gray-900 mb-6\">Related Products</h2>
                        <div className=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6\">
                            {relatedProducts.map((relatedProduct) => (
                                <ProductCard key={relatedProduct.id} product={relatedProduct} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}