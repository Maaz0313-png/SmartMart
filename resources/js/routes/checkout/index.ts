import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\CheckoutController::index
 * @see app/Http/Controllers/CheckoutController.php:30
 * @route '/checkout'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/checkout',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CheckoutController::index
 * @see app/Http/Controllers/CheckoutController.php:30
 * @route '/checkout'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::index
 * @see app/Http/Controllers/CheckoutController.php:30
 * @route '/checkout'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CheckoutController::index
 * @see app/Http/Controllers/CheckoutController.php:30
 * @route '/checkout'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CheckoutController::process
 * @see app/Http/Controllers/CheckoutController.php:61
 * @route '/checkout'
 */
export const process = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CheckoutController::process
 * @see app/Http/Controllers/CheckoutController.php:61
 * @route '/checkout'
 */
process.url = (options?: RouteQueryOptions) => {
    return process.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::process
 * @see app/Http/Controllers/CheckoutController.php:61
 * @route '/checkout'
 */
process.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CheckoutController::confirmation
 * @see app/Http/Controllers/CheckoutController.php:134
 * @route '/checkout/confirmation/{order}'
 */
export const confirmation = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmation.url(args, options),
    method: 'get',
})

confirmation.definition = {
    methods: ["get","head"],
    url: '/checkout/confirmation/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CheckoutController::confirmation
 * @see app/Http/Controllers/CheckoutController.php:134
 * @route '/checkout/confirmation/{order}'
 */
confirmation.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return confirmation.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::confirmation
 * @see app/Http/Controllers/CheckoutController.php:134
 * @route '/checkout/confirmation/{order}'
 */
confirmation.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmation.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CheckoutController::confirmation
 * @see app/Http/Controllers/CheckoutController.php:134
 * @route '/checkout/confirmation/{order}'
 */
confirmation.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirmation.url(args, options),
    method: 'head',
})
const checkout = {
    index: Object.assign(index, index),
process: Object.assign(process, process),
confirmation: Object.assign(confirmation, confirmation),
}

export default checkout