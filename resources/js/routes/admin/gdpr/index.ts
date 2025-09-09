import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\GdprController::dashboard
 * @see app/Http/Controllers/Admin/GdprController.php:176
 * @route '/admin/privacy'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/admin/privacy',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::dashboard
 * @see app/Http/Controllers/Admin/GdprController.php:176
 * @route '/admin/privacy'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::dashboard
 * @see app/Http/Controllers/Admin/GdprController.php:176
 * @route '/admin/privacy'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\GdprController::dashboard
 * @see app/Http/Controllers/Admin/GdprController.php:176
 * @route '/admin/privacy'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\GdprController::index
 * @see app/Http/Controllers/Admin/GdprController.php:26
 * @route '/admin/privacy/requests'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/privacy/requests',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::index
 * @see app/Http/Controllers/Admin/GdprController.php:26
 * @route '/admin/privacy/requests'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::index
 * @see app/Http/Controllers/Admin/GdprController.php:26
 * @route '/admin/privacy/requests'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\GdprController::index
 * @see app/Http/Controllers/Admin/GdprController.php:26
 * @route '/admin/privacy/requests'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\GdprController::show
 * @see app/Http/Controllers/Admin/GdprController.php:65
 * @route '/admin/privacy/requests/{dataRequest}'
 */
export const show = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/privacy/requests/{dataRequest}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::show
 * @see app/Http/Controllers/Admin/GdprController.php:65
 * @route '/admin/privacy/requests/{dataRequest}'
 */
show.url = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{dataRequest}', parsedArgs.dataRequest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::show
 * @see app/Http/Controllers/Admin/GdprController.php:65
 * @route '/admin/privacy/requests/{dataRequest}'
 */
show.get = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\GdprController::show
 * @see app/Http/Controllers/Admin/GdprController.php:65
 * @route '/admin/privacy/requests/{dataRequest}'
 */
show.head = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\GdprController::update
 * @see app/Http/Controllers/Admin/GdprController.php:77
 * @route '/admin/privacy/requests/{dataRequest}'
 */
export const update = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/privacy/requests/{dataRequest}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::update
 * @see app/Http/Controllers/Admin/GdprController.php:77
 * @route '/admin/privacy/requests/{dataRequest}'
 */
update.url = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{dataRequest}', parsedArgs.dataRequest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::update
 * @see app/Http/Controllers/Admin/GdprController.php:77
 * @route '/admin/privacy/requests/{dataRequest}'
 */
update.patch = (args: { dataRequest: number | { id: number } } | [dataRequest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\GdprController::bulkUpdate
 * @see app/Http/Controllers/Admin/GdprController.php:119
 * @route '/admin/privacy/requests/bulk'
 */
export const bulkUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdate.url(options),
    method: 'post',
})

bulkUpdate.definition = {
    methods: ["post"],
    url: '/admin/privacy/requests/bulk',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::bulkUpdate
 * @see app/Http/Controllers/Admin/GdprController.php:119
 * @route '/admin/privacy/requests/bulk'
 */
bulkUpdate.url = (options?: RouteQueryOptions) => {
    return bulkUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::bulkUpdate
 * @see app/Http/Controllers/Admin/GdprController.php:119
 * @route '/admin/privacy/requests/bulk'
 */
bulkUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkUpdate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\GdprController::exportMethod
 * @see app/Http/Controllers/Admin/GdprController.php:160
 * @route '/admin/privacy/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/privacy/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\GdprController::exportMethod
 * @see app/Http/Controllers/Admin/GdprController.php:160
 * @route '/admin/privacy/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\GdprController::exportMethod
 * @see app/Http/Controllers/Admin/GdprController.php:160
 * @route '/admin/privacy/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\GdprController::exportMethod
 * @see app/Http/Controllers/Admin/GdprController.php:160
 * @route '/admin/privacy/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})
const gdpr = {
    dashboard: Object.assign(dashboard, dashboard),
index: Object.assign(index, index),
show: Object.assign(show, show),
update: Object.assign(update, update),
bulkUpdate: Object.assign(bulkUpdate, bulkUpdate),
export: Object.assign(exportMethod, exportMethod),
}

export default gdpr