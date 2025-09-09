import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\ApiAuthController::register
 * @see app/Http/Controllers/Auth/ApiAuthController.php:92
 * @route '/api/v1/auth/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: '/api/v1/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::register
 * @see app/Http/Controllers/Auth/ApiAuthController.php:92
 * @route '/api/v1/auth/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::register
 * @see app/Http/Controllers/Auth/ApiAuthController.php:92
 * @route '/api/v1/auth/register'
 */
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::login
 * @see app/Http/Controllers/Auth/ApiAuthController.php:18
 * @route '/api/v1/auth/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/v1/auth/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::login
 * @see app/Http/Controllers/Auth/ApiAuthController.php:18
 * @route '/api/v1/auth/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::login
 * @see app/Http/Controllers/Auth/ApiAuthController.php:18
 * @route '/api/v1/auth/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::user
 * @see app/Http/Controllers/Auth/ApiAuthController.php:82
 * @route '/api/v1/auth/user'
 */
export const user = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})

user.definition = {
    methods: ["get","head"],
    url: '/api/v1/auth/user',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::user
 * @see app/Http/Controllers/Auth/ApiAuthController.php:82
 * @route '/api/v1/auth/user'
 */
user.url = (options?: RouteQueryOptions) => {
    return user.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::user
 * @see app/Http/Controllers/Auth/ApiAuthController.php:82
 * @route '/api/v1/auth/user'
 */
user.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\ApiAuthController::user
 * @see app/Http/Controllers/Auth/ApiAuthController.php:82
 * @route '/api/v1/auth/user'
 */
user.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: user.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logout
 * @see app/Http/Controllers/Auth/ApiAuthController.php:58
 * @route '/api/v1/auth/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/api/v1/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logout
 * @see app/Http/Controllers/Auth/ApiAuthController.php:58
 * @route '/api/v1/auth/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logout
 * @see app/Http/Controllers/Auth/ApiAuthController.php:58
 * @route '/api/v1/auth/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logoutAll
 * @see app/Http/Controllers/Auth/ApiAuthController.php:70
 * @route '/api/v1/auth/logout-all'
 */
export const logoutAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logoutAll.url(options),
    method: 'post',
})

logoutAll.definition = {
    methods: ["post"],
    url: '/api/v1/auth/logout-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logoutAll
 * @see app/Http/Controllers/Auth/ApiAuthController.php:70
 * @route '/api/v1/auth/logout-all'
 */
logoutAll.url = (options?: RouteQueryOptions) => {
    return logoutAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::logoutAll
 * @see app/Http/Controllers/Auth/ApiAuthController.php:70
 * @route '/api/v1/auth/logout-all'
 */
logoutAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logoutAll.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::refresh
 * @see app/Http/Controllers/Auth/ApiAuthController.php:126
 * @route '/api/v1/auth/refresh'
 */
export const refresh = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

refresh.definition = {
    methods: ["post"],
    url: '/api/v1/auth/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::refresh
 * @see app/Http/Controllers/Auth/ApiAuthController.php:126
 * @route '/api/v1/auth/refresh'
 */
refresh.url = (options?: RouteQueryOptions) => {
    return refresh.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::refresh
 * @see app/Http/Controllers/Auth/ApiAuthController.php:126
 * @route '/api/v1/auth/refresh'
 */
refresh.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::tokens
 * @see app/Http/Controllers/Auth/ApiAuthController.php:151
 * @route '/api/v1/auth/tokens'
 */
export const tokens = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tokens.url(options),
    method: 'get',
})

tokens.definition = {
    methods: ["get","head"],
    url: '/api/v1/auth/tokens',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::tokens
 * @see app/Http/Controllers/Auth/ApiAuthController.php:151
 * @route '/api/v1/auth/tokens'
 */
tokens.url = (options?: RouteQueryOptions) => {
    return tokens.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::tokens
 * @see app/Http/Controllers/Auth/ApiAuthController.php:151
 * @route '/api/v1/auth/tokens'
 */
tokens.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tokens.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\ApiAuthController::tokens
 * @see app/Http/Controllers/Auth/ApiAuthController.php:151
 * @route '/api/v1/auth/tokens'
 */
tokens.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tokens.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::revokeToken
 * @see app/Http/Controllers/Auth/ApiAuthController.php:171
 * @route '/api/v1/auth/tokens/{tokenId}'
 */
export const revokeToken = (args: { tokenId: string | number } | [tokenId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeToken.url(args, options),
    method: 'delete',
})

revokeToken.definition = {
    methods: ["delete"],
    url: '/api/v1/auth/tokens/{tokenId}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::revokeToken
 * @see app/Http/Controllers/Auth/ApiAuthController.php:171
 * @route '/api/v1/auth/tokens/{tokenId}'
 */
revokeToken.url = (args: { tokenId: string | number } | [tokenId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tokenId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    tokenId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        tokenId: args.tokenId,
                }

    return revokeToken.definition.url
            .replace('{tokenId}', parsedArgs.tokenId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ApiAuthController::revokeToken
 * @see app/Http/Controllers/Auth/ApiAuthController.php:171
 * @route '/api/v1/auth/tokens/{tokenId}'
 */
revokeToken.delete = (args: { tokenId: string | number } | [tokenId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeToken.url(args, options),
    method: 'delete',
})
const ApiAuthController = { register, login, user, logout, logoutAll, refresh, tokens, revokeToken }

export default ApiAuthController