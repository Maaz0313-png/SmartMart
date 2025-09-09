import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CartController::index
 * @see app/Http/Controllers/Api/CartController.php:16
 * @route '/api/v1/cart'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\CartController::index
 * @see app/Http/Controllers/Api/CartController.php:16
 * @route '/api/v1/cart'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CartController::index
 * @see app/Http/Controllers/Api/CartController.php:16
 * @route '/api/v1/cart'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\CartController::index
 * @see app/Http/Controllers/Api/CartController.php:16
 * @route '/api/v1/cart'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\CartController::store
 * @see app/Http/Controllers/Api/CartController.php:36
 * @route '/api/v1/cart'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/cart',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CartController::store
 * @see app/Http/Controllers/Api/CartController.php:36
 * @route '/api/v1/cart'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CartController::store
 * @see app/Http/Controllers/Api/CartController.php:36
 * @route '/api/v1/cart'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\CartController::update
 * @see app/Http/Controllers/Api/CartController.php:78
 * @route '/api/v1/cart/{cart}'
 */
export const update = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/api/v1/cart/{cart}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Api\CartController::update
 * @see app/Http/Controllers/Api/CartController.php:78
 * @route '/api/v1/cart/{cart}'
 */
update.url = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cart: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cart: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cart: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cart: typeof args.cart === 'object'
                ? args.cart.id
                : args.cart,
                }

    return update.definition.url
            .replace('{cart}', parsedArgs.cart.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CartController::update
 * @see app/Http/Controllers/Api/CartController.php:78
 * @route '/api/v1/cart/{cart}'
 */
update.put = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Api\CartController::update
 * @see app/Http/Controllers/Api/CartController.php:78
 * @route '/api/v1/cart/{cart}'
 */
update.patch = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Api\CartController::destroy
 * @see app/Http/Controllers/Api/CartController.php:105
 * @route '/api/v1/cart/{cart}'
 */
export const destroy = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/cart/{cart}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\CartController::destroy
 * @see app/Http/Controllers/Api/CartController.php:105
 * @route '/api/v1/cart/{cart}'
 */
destroy.url = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cart: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cart: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cart: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cart: typeof args.cart === 'object'
                ? args.cart.id
                : args.cart,
                }

    return destroy.definition.url
            .replace('{cart}', parsedArgs.cart.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CartController::destroy
 * @see app/Http/Controllers/Api/CartController.php:105
 * @route '/api/v1/cart/{cart}'
 */
destroy.delete = (args: { cart: number | { id: number } } | [cart: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const CartController = { index, store, update, destroy }

export default CartController