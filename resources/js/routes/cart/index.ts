import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
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
* @see \App\Http\Controllers\CartController::index
 * @see app/Http/Controllers/CartController.php:19
 * @route '/cart'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CartController::index
 * @see app/Http/Controllers/CartController.php:19
 * @route '/cart'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::index
 * @see app/Http/Controllers/CartController.php:19
 * @route '/cart'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CartController::index
 * @see app/Http/Controllers/CartController.php:19
 * @route '/cart'
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
* @see \App\Http\Controllers\CartController::store
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/cart',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartController::store
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::store
 * @see app/Http/Controllers/CartController.php:36
 * @route '/cart'
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
* @see \App\Http\Controllers\CartController::update
 * @see app/Http/Controllers/CartController.php:99
 * @route '/cart/{cartItem}'
 */
export const update = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/cart/{cartItem}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\CartController::update
 * @see app/Http/Controllers/CartController.php:99
 * @route '/cart/{cartItem}'
 */
update.url = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cartItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cartItem: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cartItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cartItem: typeof args.cartItem === 'object'
                ? args.cartItem.id
                : args.cartItem,
                }

    return update.definition.url
            .replace('{cartItem}', parsedArgs.cartItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::update
 * @see app/Http/Controllers/CartController.php:99
 * @route '/cart/{cartItem}'
 */
update.patch = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
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

/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:133
 * @route '/cart/{cartItem}'
 */
export const destroy = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/cart/{cartItem}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:133
 * @route '/cart/{cartItem}'
 */
destroy.url = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cartItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { cartItem: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    cartItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        cartItem: typeof args.cartItem === 'object'
                ? args.cartItem.id
                : args.cartItem,
                }

    return destroy.definition.url
            .replace('{cartItem}', parsedArgs.cartItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::destroy
 * @see app/Http/Controllers/CartController.php:133
 * @route '/cart/{cartItem}'
 */
destroy.delete = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\CartController::clear
 * @see app/Http/Controllers/CartController.php:150
 * @route '/cart'
 */
export const clear = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

clear.definition = {
    methods: ["delete"],
    url: '/cart',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\CartController::clear
 * @see app/Http/Controllers/CartController.php:150
 * @route '/cart'
 */
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::clear
 * @see app/Http/Controllers/CartController.php:150
 * @route '/cart'
 */
clear.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\CartController::count
 * @see app/Http/Controllers/CartController.php:162
 * @route '/cart/count'
 */
export const count = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: count.url(options),
    method: 'get',
})

count.definition = {
    methods: ["get","head"],
    url: '/cart/count',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CartController::count
 * @see app/Http/Controllers/CartController.php:162
 * @route '/cart/count'
 */
count.url = (options?: RouteQueryOptions) => {
    return count.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::count
 * @see app/Http/Controllers/CartController.php:162
 * @route '/cart/count'
 */
count.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: count.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CartController::count
 * @see app/Http/Controllers/CartController.php:162
 * @route '/cart/count'
 */
count.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: count.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CartController::coupon
 * @see app/Http/Controllers/CartController.php:175
 * @route '/cart/coupon'
 */
export const coupon = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: coupon.url(options),
    method: 'post',
})

coupon.definition = {
    methods: ["post"],
    url: '/cart/coupon',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartController::coupon
 * @see app/Http/Controllers/CartController.php:175
 * @route '/cart/coupon'
 */
coupon.url = (options?: RouteQueryOptions) => {
    return coupon.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::coupon
 * @see app/Http/Controllers/CartController.php:175
 * @route '/cart/coupon'
 */
coupon.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: coupon.url(options),
    method: 'post',
})
const cart = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
clear: Object.assign(clear, clear),
count: Object.assign(count, count),
coupon: Object.assign(coupon, coupon),
}

export default cart