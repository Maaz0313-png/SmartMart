import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ApiDocController::index
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/docs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ApiDocController::index
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ApiDocController::index
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\ApiDocController::index
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const ApiDocController = { index }

export default ApiDocController