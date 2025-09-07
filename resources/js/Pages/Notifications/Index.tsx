import React, { useState, useEffect } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    BellIcon,
    CheckIcon,
    TrashIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";
import { BellIcon as BellSolidIcon } from "@heroicons/react/24/solid";

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    data: any;
    read_at: string | null;
    created_at: string;
    time_ago: string;
}

interface Props {
    notifications: {
        data: Notification[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    unreadCount: number;
}

export default function NotificationsIndex({
    notifications,
    unreadCount,
}: Props) {
    const [isLoading, setIsLoading] = useState(false);
    const [localNotifications, setLocalNotifications] = useState(
        notifications.data
    );
    const [localUnreadCount, setLocalUnreadCount] = useState(unreadCount);

    const markAsRead = async (notificationId: number) => {
        try {
            await fetch(`/notifications/${notificationId}/mark-read`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            });

            setLocalNotifications((prev) =>
                prev.map((n) =>
                    n.id === notificationId
                        ? { ...n, read_at: new Date().toISOString() }
                        : n
                )
            );

            setLocalUnreadCount((prev) => Math.max(0, prev - 1));
        } catch (error) {
            console.error("Failed to mark notification as read:", error);
        }
    };

    const markAllAsRead = async () => {
        setIsLoading(true);
        try {
            await fetch("/notifications/mark-all-read", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            });

            setLocalNotifications((prev) =>
                prev.map((n) => ({ ...n, read_at: new Date().toISOString() }))
            );

            setLocalUnreadCount(0);
        } catch (error) {
            console.error("Failed to mark all notifications as read:", error);
        } finally {
            setIsLoading(false);
        }
    };

    const deleteNotification = async (notificationId: number) => {
        try {
            await fetch(`/notifications/${notificationId}`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            });

            setLocalNotifications((prev) =>
                prev.filter((n) => n.id !== notificationId)
            );
        } catch (error) {
            console.error("Failed to delete notification:", error);
        }
    };

    const getNotificationIcon = (type: string) => {
        switch (type) {
            case "order_status":
                return "📦";
            case "subscription_updates":
                return "📫";
            case "payment_notifications":
                return "💳";
            case "product_recommendation":
                return "✨";
            case "security_alerts":
                return "🔒";
            default:
                return "🔔";
        }
    };

    const handleNotificationClick = (notification: Notification) => {
        if (!notification.read_at) {
            markAsRead(notification.id);
        }

        // Navigate to action URL if available
        if (notification.data?.action_url) {
            router.visit(notification.data.action_url);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Notifications" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <div className="flex items-center space-x-3">
                                    <BellSolidIcon className="h-8 w-8 text-blue-600" />
                                    <div>
                                        <h1 className="text-2xl font-bold text-gray-900">
                                            Notifications
                                        </h1>
                                        <p className="text-sm text-gray-600">
                                            {localUnreadCount} unread
                                            notification
                                            {localUnreadCount !== 1 ? "s" : ""}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex space-x-3">
                                    <Link
                                        href={route(
                                            "notifications.preferences"
                                        )}
                                        className="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Settings
                                    </Link>
                                    {localUnreadCount > 0 && (
                                        <button
                                            onClick={markAllAsRead}
                                            disabled={isLoading}
                                            className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                        >
                                            {isLoading
                                                ? "Marking..."
                                                : "Mark All Read"}
                                        </button>
                                    )}
                                </div>
                            </div>

                            {localNotifications.length === 0 ? (
                                <div className="text-center py-12">
                                    <BellIcon className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        No notifications
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        You're all caught up! Check back later
                                        for new updates.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {localNotifications.map((notification) => (
                                        <div
                                            key={notification.id}
                                            className={`relative flex items-start space-x-3 p-4 rounded-lg border cursor-pointer transition-colors ${
                                                notification.read_at
                                                    ? "bg-white border-gray-200 hover:bg-gray-50"
                                                    : "bg-blue-50 border-blue-200 hover:bg-blue-100"
                                            }`}
                                            onClick={() =>
                                                handleNotificationClick(
                                                    notification
                                                )
                                            }
                                        >
                                            <div className="flex-shrink-0 text-2xl">
                                                {getNotificationIcon(
                                                    notification.type
                                                )}
                                            </div>

                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-start justify-between">
                                                    <div className="flex-1">
                                                        <h4
                                                            className={`text-sm font-medium ${
                                                                notification.read_at
                                                                    ? "text-gray-900"
                                                                    : "text-blue-900"
                                                            }`}
                                                        >
                                                            {notification.title}
                                                        </h4>
                                                        <p
                                                            className={`mt-1 text-sm ${
                                                                notification.read_at
                                                                    ? "text-gray-600"
                                                                    : "text-blue-800"
                                                            }`}
                                                        >
                                                            {
                                                                notification.message
                                                            }
                                                        </p>
                                                        <p className="mt-2 text-xs text-gray-500">
                                                            {
                                                                notification.time_ago
                                                            }
                                                        </p>
                                                    </div>

                                                    <div className="flex items-center space-x-2 ml-4">
                                                        {!notification.read_at && (
                                                            <button
                                                                onClick={(
                                                                    e
                                                                ) => {
                                                                    e.stopPropagation();
                                                                    markAsRead(
                                                                        notification.id
                                                                    );
                                                                }}
                                                                className="p-1 text-blue-600 hover:text-blue-800"
                                                                title="Mark as read"
                                                            >
                                                                <CheckIcon className="h-4 w-4" />
                                                            </button>
                                                        )}
                                                        <button
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                deleteNotification(
                                                                    notification.id
                                                                );
                                                            }}
                                                            className="p-1 text-gray-400 hover:text-red-600"
                                                            title="Delete notification"
                                                        >
                                                            <TrashIcon className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {notifications.last_page > 1 && (
                                <div className="mt-6 flex justify-center">
                                    {/* Add pagination component here */}
                                    <p className="text-sm text-gray-500">
                                        Showing {localNotifications.length} of{" "}
                                        {notifications.total} notifications
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
