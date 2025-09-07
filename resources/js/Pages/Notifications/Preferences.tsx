import React, { useState } from "react";
import { Head, Form } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { CogIcon } from "@heroicons/react/24/outline";

interface NotificationPreference {
    id: number;
    type: string;
    email_enabled: boolean;
    sms_enabled: boolean;
    push_enabled: boolean;
}

interface Props {
    preferences: NotificationPreference[];
    types: string[];
}

const typeLabels: Record<string, { title: string; description: string }> = {
    order_updates: {
        title: "Order Updates",
        description:
            "Notifications about your order status, shipping, and delivery",
    },
    subscription_updates: {
        title: "Subscription Updates",
        description: "Updates about your subscription boxes and billing",
    },
    payment_notifications: {
        title: "Payment Notifications",
        description: "Alerts about payments, billing issues, and receipts",
    },
    promotions: {
        title: "Promotions & Offers",
        description: "Special deals, discounts, and promotional offers",
    },
    recommendations: {
        title: "Product Recommendations",
        description:
            "Personalized product suggestions based on your preferences",
    },
    security_alerts: {
        title: "Security Alerts",
        description: "Important security notifications and account alerts",
    },
};

export default function NotificationPreferences({ preferences, types }: Props) {
    const [localPreferences, setLocalPreferences] = useState<
        Record<string, NotificationPreference>
    >(
        preferences.reduce((acc, pref) => {
            acc[pref.type] = pref;
            return acc;
        }, {} as Record<string, NotificationPreference>)
    );

    const updatePreference = (
        type: string,
        channel: string,
        enabled: boolean
    ) => {
        setLocalPreferences((prev) => ({
            ...prev,
            [type]: {
                ...prev[type],
                [`${channel}_enabled`]: enabled,
            },
        }));
    };

    const handleSubmit = () => {
        // The form will be submitted automatically by Inertia
    };

    return (
        <AuthenticatedLayout>
            <Head title="Notification Preferences" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center space-x-3 mb-6">
                                <CogIcon className="h-8 w-8 text-gray-600" />
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900">
                                        Notification Preferences
                                    </h1>
                                    <p className="text-sm text-gray-600">
                                        Customize how you receive notifications
                                        from SmartMart
                                    </p>
                                </div>
                            </div>

                            <Form
                                action={route(
                                    "notifications.preferences.update"
                                )}
                                method="post"
                                transform={(data) => ({
                                    preferences:
                                        Object.values(localPreferences),
                                })}
                                onSuccess={() => {
                                    // Handle success
                                }}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="space-y-8">
                                            {types.map((type) => {
                                                const pref =
                                                    localPreferences[type];
                                                const label = typeLabels[
                                                    type
                                                ] || {
                                                    title: type,
                                                    description: "",
                                                };

                                                return (
                                                    <div
                                                        key={type}
                                                        className="border-b border-gray-200 pb-8 last:border-b-0"
                                                    >
                                                        <div className="mb-4">
                                                            <h3 className="text-lg font-medium text-gray-900">
                                                                {label.title}
                                                            </h3>
                                                            <p className="text-sm text-gray-600">
                                                                {
                                                                    label.description
                                                                }
                                                            </p>
                                                        </div>

                                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                            {/* Email Notifications */}
                                                            <div className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                                                <div className="flex items-center space-x-3">
                                                                    <div className="text-2xl">
                                                                        📧
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-sm font-medium text-gray-900">
                                                                            Email
                                                                        </p>
                                                                        <p className="text-xs text-gray-500">
                                                                            Receive
                                                                            via
                                                                            email
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <label className="relative inline-flex items-center cursor-pointer">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            pref?.email_enabled ||
                                                                            false
                                                                        }
                                                                        onChange={(
                                                                            e
                                                                        ) =>
                                                                            updatePreference(
                                                                                type,
                                                                                "email",
                                                                                e
                                                                                    .target
                                                                                    .checked
                                                                            )
                                                                        }
                                                                        className="sr-only peer"
                                                                    />
                                                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                                </label>
                                                            </div>

                                                            {/* SMS Notifications */}
                                                            <div className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                                                <div className="flex items-center space-x-3">
                                                                    <div className="text-2xl">
                                                                        📱
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-sm font-medium text-gray-900">
                                                                            SMS
                                                                        </p>
                                                                        <p className="text-xs text-gray-500">
                                                                            Receive
                                                                            via
                                                                            text
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <label className="relative inline-flex items-center cursor-pointer">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            pref?.sms_enabled ||
                                                                            false
                                                                        }
                                                                        onChange={(
                                                                            e
                                                                        ) =>
                                                                            updatePreference(
                                                                                type,
                                                                                "sms",
                                                                                e
                                                                                    .target
                                                                                    .checked
                                                                            )
                                                                        }
                                                                        className="sr-only peer"
                                                                    />
                                                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                                </label>
                                                            </div>

                                                            {/* Push Notifications */}
                                                            <div className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                                                <div className="flex items-center space-x-3">
                                                                    <div className="text-2xl">
                                                                        🔔
                                                                    </div>
                                                                    <div>
                                                                        <p className="text-sm font-medium text-gray-900">
                                                                            Push
                                                                        </p>
                                                                        <p className="text-xs text-gray-500">
                                                                            Browser
                                                                            notifications
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <label className="relative inline-flex items-center cursor-pointer">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            pref?.push_enabled ||
                                                                            false
                                                                        }
                                                                        onChange={(
                                                                            e
                                                                        ) =>
                                                                            updatePreference(
                                                                                type,
                                                                                "push",
                                                                                e
                                                                                    .target
                                                                                    .checked
                                                                            )
                                                                        }
                                                                        className="sr-only peer"
                                                                    />
                                                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>

                                        {errors &&
                                            Object.keys(errors).length > 0 && (
                                                <div className="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
                                                    <h4 className="text-sm font-medium text-red-800">
                                                        There were errors with
                                                        your submission:
                                                    </h4>
                                                    <ul className="mt-2 text-sm text-red-700 list-disc list-inside">
                                                        {Object.values(
                                                            errors
                                                        ).map(
                                                            (error, index) => (
                                                                <li key={index}>
                                                                    {error}
                                                                </li>
                                                            )
                                                        )}
                                                    </ul>
                                                </div>
                                            )}

                                        <div className="mt-8 flex justify-end space-x-3">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    window.history.back()
                                                }
                                                className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                            >
                                                {processing
                                                    ? "Saving..."
                                                    : "Save Preferences"}
                                            </button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
