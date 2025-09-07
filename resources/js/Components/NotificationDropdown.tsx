import React, { useState, useEffect, useRef } from "react";
import { Link } from "@inertiajs/react";
import { BellIcon } from "@heroicons/react/24/outline";
import { BellIcon as BellSolidIcon } from "@heroicons/react/24/solid";

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    data: any;
    read: boolean;
    time_ago: string;
}

interface Props {
    className?: string;
}

export default function NotificationDropdown({ className = "" }: Props) {
    const [isOpen, setIsOpen] = useState(false);
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                dropdownRef.current &&
                !dropdownRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        // Fetch initial notifications
        fetchNotifications();

        // Set up real-time notifications if Echo is available
        if (window.Echo && window.Laravel?.user?.id) {
            const channel = window.Echo.private(
                `user.${window.Laravel.user.id}`
            );

            channel.listen("notification.sent", (data: any) => {
                setNotifications((prev) => [data, ...prev.slice(0, 9)]);
                setUnreadCount((prev) => prev + 1);
            });

            return () => {
                window.Echo.leaveChannel(`user.${window.Laravel.user.id}`);
            };
        }
    }, []);

    const fetchNotifications = async () => {
        setLoading(true);
        try {
            const response = await fetch("/notifications/recent");
            const data = await response.json();
            setNotifications(data.notifications);
            setUnreadCount(data.unreadCount);
        } catch (error) {
            console.error("Failed to fetch notifications:", error);
        } finally {
            setLoading(false);
        }
    };

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

            setNotifications((prev) =>
                prev.map((n) =>
                    n.id === notificationId ? { ...n, read: true } : n
                )
            );

            setUnreadCount((prev) => Math.max(0, prev - 1));
        } catch (error) {
            console.error("Failed to mark notification as read:", error);
        }
    };

    const toggleDropdown = () => {
        setIsOpen(!isOpen);
        if (!isOpen) {
            fetchNotifications();
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

    return (
        <div className={`relative ${className}`} ref={dropdownRef}>
            <button
                onClick={toggleDropdown}
                className="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
            >
                {unreadCount > 0 ? (
                    <BellSolidIcon className="h-6 w-6" />
                ) : (
                    <BellIcon className="h-6 w-6" />
                )}

                {unreadCount > 0 && (
                    <span className="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                        {unreadCount > 99 ? "99+" : unreadCount}
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div className="p-4 border-b border-gray-100">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-medium text-gray-900">
                                Notifications
                            </h3>
                            <Link
                                href={route("notifications.index")}
                                className="text-xs text-blue-600 hover:text-blue-800"
                                onClick={() => setIsOpen(false)}
                            >
                                View all
                            </Link>
                        </div>
                    </div>

                    <div className="max-h-80 overflow-y-auto">
                        {loading ? (
                            <div className="p-4 text-center text-sm text-gray-500">
                                Loading...
                            </div>
                        ) : notifications.length === 0 ? (
                            <div className="p-4 text-center text-sm text-gray-500">
                                No notifications
                            </div>
                        ) : (
                            notifications.map((notification) => (
                                <div
                                    key={notification.id}
                                    className={`p-4 border-b border-gray-50 last:border-b-0 hover:bg-gray-50 cursor-pointer ${
                                        !notification.read ? "bg-blue-50" : ""
                                    }`}
                                    onClick={() => {
                                        if (!notification.read) {
                                            markAsRead(notification.id);
                                        }
                                        setIsOpen(false);
                                        if (notification.data?.action_url) {
                                            window.location.href =
                                                notification.data.action_url;
                                        }
                                    }}
                                >
                                    <div className="flex items-start space-x-3">
                                        <div className="flex-shrink-0 text-lg">
                                            {getNotificationIcon(
                                                notification.type
                                            )}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p
                                                className={`text-sm font-medium truncate ${
                                                    !notification.read
                                                        ? "text-gray-900"
                                                        : "text-gray-700"
                                                }`}
                                            >
                                                {notification.title}
                                            </p>
                                            <p
                                                className={`text-xs mt-1 line-clamp-2 ${
                                                    !notification.read
                                                        ? "text-gray-700"
                                                        : "text-gray-500"
                                                }`}
                                            >
                                                {notification.message}
                                            </p>
                                            <p className="text-xs text-gray-400 mt-1">
                                                {notification.time_ago}
                                            </p>
                                        </div>
                                        {!notification.read && (
                                            <div className="flex-shrink-0">
                                                <div className="h-2 w-2 bg-blue-600 rounded-full"></div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {notifications.length > 0 && (
                        <div className="p-4 border-t border-gray-100">
                            <Link
                                href={route("notifications.index")}
                                className="block w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                                onClick={() => setIsOpen(false)}
                            >
                                View all notifications
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

// Add type definitions for window objects
declare global {
    interface Window {
        Echo?: any;
        Laravel?: {
            user?: {
                id: number;
            };
        };
    }
}
