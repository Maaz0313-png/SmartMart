import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { type SubscriptionPlan, type Subscription } from '@/Types';
import { CheckIcon } from '@heroicons/react/24/outline';
import PrimaryButton from '@/Components/PrimaryButton';

interface Props {
    plans: SubscriptionPlan[];
    userSubscription?: Subscription;
}

export default function SubscriptionPlans({ plans, userSubscription }: Props) {
    const { auth } = usePage().props as any;

    const handleSelectPlan = (plan: SubscriptionPlan) => {
        if (userSubscription) {
            // Redirect to manage subscription if user already has one
            return;
        }
        
        // Redirect to checkout
        window.location.href = route('subscriptions.checkout', plan.id);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Subscription Plans" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="text-center mb-12">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Choose Your SmartMart Subscription
                        </h1>
                        <p className="text-lg text-gray-600 max-w-3xl mx-auto">
                            Get curated products delivered to your door every month. 
                            Cancel or pause anytime.
                        </p>
                    </div>

                    {userSubscription && (
                        <div className="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-medium text-blue-900">
                                        You have an active subscription
                                    </h3>
                                    <p className="text-blue-700">
                                        {userSubscription.subscription_plan?.name} - ${userSubscription.subscription_plan?.price}/{userSubscription.subscription_plan?.billing_cycle}
                                    </p>
                                </div>
                                <Link
                                    href={route('subscriptions.manage')}
                                    className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                                >
                                    Manage Subscription
                                </Link>
                            </div>
                        </div>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {plans.map((plan) => (
                            <div
                                key={plan.id}
                                className="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-indigo-500 transition-colors"
                            >
                                <div className="p-6">
                                    <h3 className="text-2xl font-bold text-gray-900 mb-2">
                                        {plan.name}
                                    </h3>
                                    <p className="text-gray-600 mb-4">
                                        {plan.description}
                                    </p>
                                    
                                    <div className="mb-6">
                                        <div className="flex items-baseline">
                                            <span className="text-4xl font-bold text-gray-900">
                                                ${plan.price}
                                            </span>
                                            <span className="ml-2 text-gray-600">
                                                /{plan.billing_cycle}
                                            </span>
                                        </div>
                                        {plan.trial_days && plan.trial_days > 0 && (
                                            <p className="text-sm text-green-600 mt-1">
                                                {plan.trial_days}-day free trial
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-6">
                                        <h4 className="font-semibold text-gray-900 mb-3">Features:</h4>
                                        <ul className="space-y-2">
                                            {(plan.features as string[])?.map((feature: string, index: number) => (
                                                <li key={index} className="flex items-center">
                                                    <CheckIcon className="h-5 w-5 text-green-500 mr-2" />
                                                    <span className="text-gray-700">{feature}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    {plan.max_products && (
                                        <div className="mb-6">
                                            <p className="text-sm text-gray-600">
                                                Up to {plan.max_products} products per box
                                            </p>
                                        </div>
                                    )}

                                    <PrimaryButton
                                        onClick={() => handleSelectPlan(plan)}
                                        disabled={!!userSubscription}
                                        className="w-full justify-center"
                                    >
                                        {userSubscription ? 'Current Plan' : 'Choose Plan'}
                                    </PrimaryButton>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-12 text-center">
                        <h2 className="text-2xl font-bold text-gray-900 mb-4">
                            How it works
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                            <div className="text-center">
                                <div className="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">1</span>
                                </div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Choose Your Plan</h3>
                                <p className="text-gray-600">
                                    Select a subscription plan that fits your needs and preferences.
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">2</span>
                                </div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">We Curate</h3>
                                <p className="text-gray-600">
                                    Our team handpicks products based on your preferences and current trends.
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl font-bold text-indigo-600">3</span>
                                </div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">Enjoy & Discover</h3>
                                <p className="text-gray-600">
                                    Receive your carefully curated box and discover new amazing products.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}