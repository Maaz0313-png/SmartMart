import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\NotificationController::index
 * @see app/Http/Controllers/NotificationController.php:22
 * @route '/notifications'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NotificationController::index
 * @see app/Http/Controllers/NotificationController.php:22
 * @route '/notifications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::index
 * @see app/Http/Controllers/NotificationController.php:22
 * @route '/notifications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NotificationController::index
 * @see app/Http/Controllers/NotificationController.php:22
 * @route '/notifications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NotificationController::markAsRead
 * @see app/Http/Controllers/NotificationController.php:37
 * @route '/notifications/{notification}/mark-read'
 */
export const markAsRead = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsRead.url(args, options),
    method: 'post',
})

markAsRead.definition = {
    methods: ["post"],
    url: '/notifications/{notification}/mark-read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\NotificationController::markAsRead
 * @see app/Http/Controllers/NotificationController.php:37
 * @route '/notifications/{notification}/mark-read'
 */
markAsRead.url = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notification: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    notification: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notification: args.notification,
                }

    return markAsRead.definition.url
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::markAsRead
 * @see app/Http/Controllers/NotificationController.php:37
 * @route '/notifications/{notification}/mark-read'
 */
markAsRead.post = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAsRead.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NotificationController::markAllAsRead
 * @see app/Http/Controllers/NotificationController.php:52
 * @route '/notifications/mark-all-read'
 */
export const markAllAsRead = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllAsRead.url(options),
    method: 'post',
})

markAllAsRead.definition = {
    methods: ["post"],
    url: '/notifications/mark-all-read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\NotificationController::markAllAsRead
 * @see app/Http/Controllers/NotificationController.php:52
 * @route '/notifications/mark-all-read'
 */
markAllAsRead.url = (options?: RouteQueryOptions) => {
    return markAllAsRead.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::markAllAsRead
 * @see app/Http/Controllers/NotificationController.php:52
 * @route '/notifications/mark-all-read'
 */
markAllAsRead.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllAsRead.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NotificationController::unreadCount
 * @see app/Http/Controllers/NotificationController.php:66
 * @route '/notifications/unread-count'
 */
export const unreadCount = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unreadCount.url(options),
    method: 'get',
})

unreadCount.definition = {
    methods: ["get","head"],
    url: '/notifications/unread-count',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NotificationController::unreadCount
 * @see app/Http/Controllers/NotificationController.php:66
 * @route '/notifications/unread-count'
 */
unreadCount.url = (options?: RouteQueryOptions) => {
    return unreadCount.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::unreadCount
 * @see app/Http/Controllers/NotificationController.php:66
 * @route '/notifications/unread-count'
 */
unreadCount.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unreadCount.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NotificationController::unreadCount
 * @see app/Http/Controllers/NotificationController.php:66
 * @route '/notifications/unread-count'
 */
unreadCount.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: unreadCount.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NotificationController::destroy
 * @see app/Http/Controllers/NotificationController.php:77
 * @route '/notifications/{notification}'
 */
export const destroy = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/notifications/{notification}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\NotificationController::destroy
 * @see app/Http/Controllers/NotificationController.php:77
 * @route '/notifications/{notification}'
 */
destroy.url = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notification: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    notification: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notification: args.notification,
                }

    return destroy.definition.url
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::destroy
 * @see app/Http/Controllers/NotificationController.php:77
 * @route '/notifications/{notification}'
 */
destroy.delete = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\NotificationController::preferences
 * @see app/Http/Controllers/NotificationController.php:93
 * @route '/notifications/preferences'
 */
export const preferences = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preferences.url(options),
    method: 'get',
})

preferences.definition = {
    methods: ["get","head"],
    url: '/notifications/preferences',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NotificationController::preferences
 * @see app/Http/Controllers/NotificationController.php:93
 * @route '/notifications/preferences'
 */
preferences.url = (options?: RouteQueryOptions) => {
    return preferences.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::preferences
 * @see app/Http/Controllers/NotificationController.php:93
 * @route '/notifications/preferences'
 */
preferences.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preferences.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NotificationController::preferences
 * @see app/Http/Controllers/NotificationController.php:93
 * @route '/notifications/preferences'
 */
preferences.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preferences.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\NotificationController::updatePreferences
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
export const updatePreferences = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePreferences.url(options),
    method: 'post',
})

updatePreferences.definition = {
    methods: ["post"],
    url: '/notifications/preferences',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\NotificationController::updatePreferences
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
updatePreferences.url = (options?: RouteQueryOptions) => {
    return updatePreferences.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::updatePreferences
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
updatePreferences.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePreferences.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\NotificationController::recent
 * @see app/Http/Controllers/NotificationController.php:151
 * @route '/notifications/recent'
 */
export const recent = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})

recent.definition = {
    methods: ["get","head"],
    url: '/notifications/recent',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NotificationController::recent
 * @see app/Http/Controllers/NotificationController.php:151
 * @route '/notifications/recent'
 */
recent.url = (options?: RouteQueryOptions) => {
    return recent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::recent
 * @see app/Http/Controllers/NotificationController.php:151
 * @route '/notifications/recent'
 */
recent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NotificationController::recent
 * @see app/Http/Controllers/NotificationController.php:151
 * @route '/notifications/recent'
 */
recent.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recent.url(options),
    method: 'head',
})
const NotificationController = { index, markAsRead, markAllAsRead, unreadCount, destroy, preferences, updatePreferences, recent }

export default NotificationController