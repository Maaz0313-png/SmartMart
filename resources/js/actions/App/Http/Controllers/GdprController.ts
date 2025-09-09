import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\GdprController::dashboard
 * @see app/Http/Controllers/GdprController.php:96
 * @route '/privacy/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/privacy/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\GdprController::dashboard
 * @see app/Http/Controllers/GdprController.php:96
 * @route '/privacy/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::dashboard
 * @see app/Http/Controllers/GdprController.php:96
 * @route '/privacy/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\GdprController::dashboard
 * @see app/Http/Controllers/GdprController.php:96
 * @route '/privacy/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\GdprController::requestDataExport
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
export const requestDataExport = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDataExport.url(options),
    method: 'post',
})

requestDataExport.definition = {
    methods: ["post"],
    url: '/privacy/export',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::requestDataExport
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
requestDataExport.url = (options?: RouteQueryOptions) => {
    return requestDataExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::requestDataExport
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
requestDataExport.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDataExport.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\GdprController::requestDataDeletion
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
export const requestDataDeletion = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDataDeletion.url(options),
    method: 'post',
})

requestDataDeletion.definition = {
    methods: ["post"],
    url: '/privacy/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::requestDataDeletion
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
requestDataDeletion.url = (options?: RouteQueryOptions) => {
    return requestDataDeletion.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::requestDataDeletion
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
requestDataDeletion.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDataDeletion.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\GdprController::downloadExport
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
export const downloadExport = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadExport.url(args, options),
    method: 'get',
})

downloadExport.definition = {
    methods: ["get","head"],
    url: '/privacy/download/{dataRequest}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\GdprController::downloadExport
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
downloadExport.url = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { dataRequest: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { dataRequest: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    dataRequest: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        dataRequest: typeof args.dataRequest === 'object'
                ? args.dataRequest.id
                : args.dataRequest,
                }

    return downloadExport.definition.url
            .replace('{dataRequest}', parsedArgs.dataRequest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::downloadExport
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
downloadExport.get = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadExport.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\GdprController::downloadExport
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
downloadExport.head = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadExport.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\GdprController::recordConsent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
export const recordConsent = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordConsent.url(options),
    method: 'post',
})

recordConsent.definition = {
    methods: ["post"],
    url: '/privacy/consent',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::recordConsent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
recordConsent.url = (options?: RouteQueryOptions) => {
    return recordConsent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::recordConsent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
recordConsent.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordConsent.url(options),
    method: 'post',
})
const GdprController = { dashboard, requestDataExport, requestDataDeletion, downloadExport, recordConsent }

export default GdprController