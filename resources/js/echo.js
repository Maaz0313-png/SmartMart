// resources/js/echo.js - Real-time notifications setup

import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configure Pusher
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? "mt1",
    wsHost:
        import.meta.env.VITE_PUSHER_HOST ??
        `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusherapp.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                fetch("/broadcasting/auth", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN":
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") || "",
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name,
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        callback(null, data);
                    })
                    .catch((error) => {
                        callback(error);
                    });
            },
        };
    },
});

// Notification helpers
class NotificationManager {
    constructor() {
        this.callbacks = [];
        this.unreadCount = 0;
        this.init();
    }

    init() {
        // Listen for user-specific notifications
        if (window.Laravel?.user?.id) {
            window.Echo.private(`user.${window.Laravel.user.id}`).listen(
                "notification.sent",
                (data) => {
                    this.handleNewNotification(data);
                }
            );

            // Listen for order updates
            window.Echo.private(`user.${window.Laravel.user.id}`).listen(
                "order.status.updated",
                (data) => {
                    this.handleOrderUpdate(data);
                }
            );
        }

        // Request notification permission
        this.requestNotificationPermission();
    }

    handleNewNotification(data) {
        console.log("New notification received:", data);

        this.unreadCount++;

        // Show browser notification if permission granted
        this.showBrowserNotification(data);

        // Call registered callbacks
        this.callbacks.forEach((callback) => {
            try {
                callback(data, "notification");
            } catch (error) {
                console.error("Error in notification callback:", error);
            }
        });

        // Update UI elements
        this.updateNotificationBadge();
    }

    handleOrderUpdate(data) {
        console.log("Order update received:", data);

        // Show browser notification
        this.showBrowserNotification({
            title: "Order Update",
            message: `Your order #${data.order_number} is now ${data.status}`,
            type: "order_update",
            data: data,
        });

        // Call registered callbacks
        this.callbacks.forEach((callback) => {
            try {
                callback(data, "order_update");
            } catch (error) {
                console.error("Error in order update callback:", error);
            }
        });
    }

    showBrowserNotification(data) {
        if ("Notification" in window && Notification.permission === "granted") {
            const notification = new Notification(data.title, {
                body: data.message,
                icon: "/favicon.ico",
                badge: "/favicon.ico",
                tag: `notification-${data.id || Date.now()}`,
                requireInteraction: false,
                silent: false,
            });

            // Auto close after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);

            // Handle click
            notification.onclick = () => {
                window.focus();
                if (data.data?.action_url) {
                    window.location.href = data.data.action_url;
                }
                notification.close();
            };
        }
    }

    requestNotificationPermission() {
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission().then((permission) => {
                console.log("Notification permission:", permission);
            });
        }
    }

    updateNotificationBadge() {
        // Update notification badge in navigation
        const badges = document.querySelectorAll("[data-notification-badge]");
        badges.forEach((badge) => {
            if (this.unreadCount > 0) {
                badge.textContent =
                    this.unreadCount > 99 ? "99+" : this.unreadCount;
                badge.style.display = "flex";
            } else {
                badge.style.display = "none";
            }
        });
    }

    // Register callback for new notifications
    onNotification(callback) {
        this.callbacks.push(callback);

        // Return unsubscribe function
        return () => {
            const index = this.callbacks.indexOf(callback);
            if (index > -1) {
                this.callbacks.splice(index, 1);
            }
        };
    }

    // Get current unread count
    getUnreadCount() {
        return this.unreadCount;
    }

    // Update unread count (e.g., when user reads notifications)
    setUnreadCount(count) {
        this.unreadCount = Math.max(0, count);
        this.updateNotificationBadge();
    }

    // Decrease unread count
    decrementUnreadCount(amount = 1) {
        this.unreadCount = Math.max(0, this.unreadCount - amount);
        this.updateNotificationBadge();
    }
}

// Create global notification manager
window.NotificationManager = new NotificationManager();

// Export for use in React components
export default window.NotificationManager;
