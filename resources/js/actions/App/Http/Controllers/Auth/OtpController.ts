import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\OtpController::show
 * @see app/Http/Controllers/Auth/OtpController.php:81
 * @route '/verify-otp'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/verify-otp',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OtpController::show
 * @see app/Http/Controllers/Auth/OtpController.php:81
 * @route '/verify-otp'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpController::show
 * @see app/Http/Controllers/Auth/OtpController.php:81
 * @route '/verify-otp'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\OtpController::show
 * @see app/Http/Controllers/Auth/OtpController.php:81
 * @route '/verify-otp'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\OtpController::verify
 * @see app/Http/Controllers/Auth/OtpController.php:51
 * @route '/verify-otp'
 */
export const verify = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/verify-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OtpController::verify
 * @see app/Http/Controllers/Auth/OtpController.php:51
 * @route '/verify-otp'
 */
verify.url = (options?: RouteQueryOptions) => {
    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpController::verify
 * @see app/Http/Controllers/Auth/OtpController.php:51
 * @route '/verify-otp'
 */
verify.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\OtpController::send
 * @see app/Http/Controllers/Auth/OtpController.php:21
 * @route '/send-otp'
 */
export const send = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})

send.definition = {
    methods: ["post"],
    url: '/send-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OtpController::send
 * @see app/Http/Controllers/Auth/OtpController.php:21
 * @route '/send-otp'
 */
send.url = (options?: RouteQueryOptions) => {
    return send.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpController::send
 * @see app/Http/Controllers/Auth/OtpController.php:21
 * @route '/send-otp'
 */
send.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})
const OtpController = { show, verify, send }

export default OtpController