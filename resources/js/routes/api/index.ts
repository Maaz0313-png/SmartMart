import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ApiDocController::docs
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
export const docs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: docs.url(options),
    method: 'get',
})

docs.definition = {
    methods: ["get","head"],
    url: '/api/docs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ApiDocController::docs
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
docs.url = (options?: RouteQueryOptions) => {
    return docs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ApiDocController::docs
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
docs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: docs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\ApiDocController::docs
 * @see app/Http/Controllers/Api/ApiDocController.php:13
 * @route '/api/docs'
 */
docs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: docs.url(options),
    method: 'head',
})
const api = {
    docs: Object.assign(docs, docs),
}

export default api