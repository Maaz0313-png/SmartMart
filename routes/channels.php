<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User-specific notifications channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Notifications channel for a specific user
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Order-specific channel (user can listen to their own orders)
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return $user->orders()->where('id', $orderId)->exists();
});

// Subscription-specific channel (user can listen to their own subscriptions)
Broadcast::channel('subscription.{subscriptionId}', function ($user, $subscriptionId) {
    return $user->subscriptions()->where('id', $subscriptionId)->exists();
});

// Admin broadcast channel
Broadcast::channel('admin', function ($user) {
    return $user->hasRole('admin');
});

// Seller broadcast channel
Broadcast::channel('seller.{sellerId}', function ($user, $sellerId) {
    return $user->hasRole('seller') && (int) $user->id === (int) $sellerId;
});

// General announcements channel (all authenticated users)
Broadcast::channel('announcements', function ($user) {
    return $user !== null;
});