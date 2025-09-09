import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ReviewController::store
 * @see app/Http/Controllers/Api/ReviewController.php:16
 * @route '/api/v1/reviews'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/reviews',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::store
 * @see app/Http/Controllers/Api/ReviewController.php:16
 * @route '/api/v1/reviews'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::store
 * @see app/Http/Controllers/Api/ReviewController.php:16
 * @route '/api/v1/reviews'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})
const ReviewController = { store }

export default ReviewController