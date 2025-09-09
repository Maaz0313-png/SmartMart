import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\NotificationController::update
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/notifications/preferences',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\NotificationController::update
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NotificationController::update
 * @see app/Http/Controllers/NotificationController.php:122
 * @route '/notifications/preferences'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})
const preferences = {
    update: Object.assign(update, update),
}

export default preferences