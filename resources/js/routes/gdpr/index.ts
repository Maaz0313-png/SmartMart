import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
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
* @see \App\Http\Controllers\GdprController::exportMethod
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exportMethod.url(options),
    method: 'post',
})

exportMethod.definition = {
    methods: ["post"],
    url: '/privacy/export',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::exportMethod
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::exportMethod
 * @see app/Http/Controllers/GdprController.php:23
 * @route '/privacy/export'
 */
exportMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exportMethod.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\GdprController::deleteMethod
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
export const deleteMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteMethod.url(options),
    method: 'post',
})

deleteMethod.definition = {
    methods: ["post"],
    url: '/privacy/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::deleteMethod
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
deleteMethod.url = (options?: RouteQueryOptions) => {
    return deleteMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::deleteMethod
 * @see app/Http/Controllers/GdprController.php:53
 * @route '/privacy/delete'
 */
deleteMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteMethod.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\GdprController::download
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
export const download = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/privacy/download/{dataRequest}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\GdprController::download
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
download.url = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return download.definition.url
            .replace('{dataRequest}', parsedArgs.dataRequest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::download
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
download.get = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\GdprController::download
 * @see app/Http/Controllers/GdprController.php:76
 * @route '/privacy/download/{dataRequest}'
 */
download.head = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\GdprController::consent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
export const consent = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: consent.url(options),
    method: 'post',
})

consent.definition = {
    methods: ["post"],
    url: '/privacy/consent',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\GdprController::consent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
consent.url = (options?: RouteQueryOptions) => {
    return consent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\GdprController::consent
 * @see app/Http/Controllers/GdprController.php:111
 * @route '/privacy/consent'
 */
consent.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: consent.url(options),
    method: 'post',
})
const gdpr = {
    dashboard: Object.assign(dashboard, dashboard),
export: Object.assign(exportMethod, exportMethod),
delete: Object.assign(deleteMethod, deleteMethod),
download: Object.assign(download, download),
consent: Object.assign(consent, consent),
}

export default gdpr