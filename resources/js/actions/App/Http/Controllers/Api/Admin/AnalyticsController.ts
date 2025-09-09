import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\Admin\AnalyticsController::index
 * @see app/Http/Controllers/Api/Admin/AnalyticsController.php:18
 * @route '/api/v1/admin/analytics'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/admin/analytics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Admin\AnalyticsController::index
 * @see app/Http/Controllers/Api/Admin/AnalyticsController.php:18
 * @route '/api/v1/admin/analytics'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\AnalyticsController::index
 * @see app/Http/Controllers/Api/Admin/AnalyticsController.php:18
 * @route '/api/v1/admin/analytics'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\Admin\AnalyticsController::index
 * @see app/Http/Controllers/Api/Admin/AnalyticsController.php:18
 * @route '/api/v1/admin/analytics'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const AnalyticsController = { index }

export default AnalyticsController