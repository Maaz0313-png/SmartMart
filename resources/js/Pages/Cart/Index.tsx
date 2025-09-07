import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { type Cart } from '@/Types/index';
import { ShoppingBagIcon, MinusIcon, PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

interface Props {
    cart: Cart;
}

export default function CartIndex({ cart }: Props) {
    const { auth } = usePage().props as any;
    
    const updateQuantity = (itemId: number, newQuantity: number) => {
        if (newQuantity < 1) return;
        
        router.patch(route('cart.update', itemId), {
            quantity: newQuantity
        }, {
            preserveScroll: true,
        });
    };

    const removeItem = (itemId: number) => {
        router.delete(route('cart.destroy', itemId), {
            preserveScroll: true,
        });
    };

    const clearCart = () => {
        router.delete(route('cart.clear'), {
            preserveScroll: true,
        });
    };

    if (cart.item_count === 0) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Shopping Cart" />
                
                <div className="py-12">
                    <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-8 text-center">
                                <ShoppingBagIcon className="mx-auto h-16 w-16 text-gray-400 mb-4" />
                                <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                    Your cart is empty
                                </h2>
                                <p className="text-gray-600 mb-6">
                                    Discover amazing products and add them to your cart.
                                </p>
                                <Link
                                    href={route('products.index')}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                >
                                    Continue Shopping
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Shopping Cart" />
            
            <div className="py-12">
                <div className="max-w-6xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h1 className="text-3xl font-bold text-gray-900">
                                    Shopping Cart ({cart.item_count} items)
                                </h1>
                                <button
                                    onClick={clearCart}
                                    className="text-sm text-red-600 hover:text-red-800"
                                >
                                    Clear Cart
                                </button>
                            </div>

                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                {/* Cart Items */}
                                <div className="lg:col-span-2">
                                    <div className="space-y-4">
                                        {cart.items.map((item) => (
                                            <div key={item.id} className="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                                                {/* Product Image */}
                                                <div className="flex-shrink-0">
                                                    <img
                                                        src={item.product.main_image || '/placeholder-product.png'}
                                                        alt={item.product.name}
                                                        className="h-20 w-20 object-cover rounded-md"
                                                    />
                                                </div>

                                                {/* Product Info */}
                                                <div className="flex-1">
                                                    <div className="flex justify-between">
                                                        <div>
                                                            <h3 className="text-lg font-medium text-gray-900">
                                                                <Link
                                                                    href={route('products.show', item.product.slug)}
                                                                    className="hover:text-indigo-600"
                                                                >
                                                                    {item.product.name}
                                                                </Link>
                                                            </h3>
                                                            {item.product_variant && (
                                                                <p className="text-sm text-gray-600">
                                                                    {item.product_variant.name}
                                                                </p>
                                                            )}
                                                            <p className="text-sm text-gray-900 font-medium">
                                                                {item.formatted_unit_price}
                                                            </p>
                                                        </div>
                                                        <div className="text-right">
                                                            <p className="text-lg font-bold text-gray-900">
                                                                {item.formatted_total_price}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {/* Quantity Controls */}
                                                    <div className="flex items-center justify-between mt-4">
                                                        <div className="flex items-center space-x-2">
                                                            <button
                                                                onClick={() => updateQuantity(item.id, item.quantity - 1)}
                                                                disabled={item.quantity <= 1}
                                                                className="p-1 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
                                                            >
                                                                <MinusIcon className="h-4 w-4" />
                                                            </button>
                                                            <span className="px-3 py-1 border border-gray-300 rounded-md text-center min-w-[3rem]">
                                                                {item.quantity}
                                                            </span>
                                                            <button
                                                                onClick={() => updateQuantity(item.id, item.quantity + 1)}
                                                                disabled={item.quantity >= 10}
                                                                className="p-1 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
                                                            >
                                                                <PlusIcon className="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                        <button
                                                            onClick={() => removeItem(item.id)}
                                                            className="text-red-600 hover:text-red-800"
                                                        >
                                                            <TrashIcon className="h-5 w-5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Order Summary */}
                                <div className="lg:col-span-1">
                                    <div className="bg-gray-50 rounded-lg p-6">
                                        <h2 className="text-lg font-bold text-gray-900 mb-4">
                                            Order Summary
                                        </h2>
                                        
                                        <div className="space-y-3 mb-6">
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">Subtotal ({cart.item_count} items)</span>
                                                <span className="text-gray-900">{cart.formatted_total_amount}</span>
                                            </div>
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">Shipping</span>
                                                <span className="text-gray-900">Calculated at checkout</span>
                                            </div>
                                            <div className="flex justify-between text-sm">
                                                <span className="text-gray-600">Tax</span>
                                                <span className="text-gray-900">Calculated at checkout</span>
                                            </div>
                                            <hr className="my-4" />
                                            <div className="flex justify-between text-lg font-bold">
                                                <span>Total</span>
                                                <span>{cart.formatted_total_amount}</span>
                                            </div>
                                        </div>

                                        <div className="space-y-3">
                                            <PrimaryButton
                                                href={route('checkout.index')}
                                                className="w-full justify-center"
                                            >
                                                Proceed to Checkout
                                            </PrimaryButton>
                                            <SecondaryButton
                                                href={route('products.index')}
                                                className="w-full justify-center"
                                            >
                                                Continue Shopping
                                            </SecondaryButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}