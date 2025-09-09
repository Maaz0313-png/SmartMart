import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::index
 * @see app/Http/Controllers/Admin/SettingsController.php:20
 * @route '/admin/settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::index
 * @see app/Http/Controllers/Admin/SettingsController.php:20
 * @route '/admin/settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::index
 * @see app/Http/Controllers/Admin/SettingsController.php:20
 * @route '/admin/settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::index
 * @see app/Http/Controllers/Admin/SettingsController.php:20
 * @route '/admin/settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:29
 * @route '/admin/settings'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/settings',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:29
 * @route '/admin/settings'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:29
 * @route '/admin/settings'
 */
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::uploadLogo
 * @see app/Http/Controllers/Admin/SettingsController.php:62
 * @route '/admin/settings/upload-logo'
 */
export const uploadLogo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadLogo.url(options),
    method: 'post',
})

uploadLogo.definition = {
    methods: ["post"],
    url: '/admin/settings/upload-logo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::uploadLogo
 * @see app/Http/Controllers/Admin/SettingsController.php:62
 * @route '/admin/settings/upload-logo'
 */
uploadLogo.url = (options?: RouteQueryOptions) => {
    return uploadLogo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::uploadLogo
 * @see app/Http/Controllers/Admin/SettingsController.php:62
 * @route '/admin/settings/upload-logo'
 */
uploadLogo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadLogo.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::testMail
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
export const testMail = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMail.url(options),
    method: 'post',
})

testMail.definition = {
    methods: ["post"],
    url: '/admin/settings/test-mail',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::testMail
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
testMail.url = (options?: RouteQueryOptions) => {
    return testMail.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::testMail
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
testMail.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMail.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::clearCache
 * @see app/Http/Controllers/Admin/SettingsController.php:103
 * @route '/admin/settings/clear-cache'
 */
export const clearCache = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearCache.url(options),
    method: 'post',
})

clearCache.definition = {
    methods: ["post"],
    url: '/admin/settings/clear-cache',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::clearCache
 * @see app/Http/Controllers/Admin/SettingsController.php:103
 * @route '/admin/settings/clear-cache'
 */
clearCache.url = (options?: RouteQueryOptions) => {
    return clearCache.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::clearCache
 * @see app/Http/Controllers/Admin/SettingsController.php:103
 * @route '/admin/settings/clear-cache'
 */
clearCache.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearCache.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::exportMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/settings/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::exportMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::exportMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::exportMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::importMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/admin/settings/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::importMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::importMethod
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})
const settings = {
    index: Object.assign(index, index),
update: Object.assign(update, update),
uploadLogo: Object.assign(uploadLogo, uploadLogo),
testMail: Object.assign(testMail, testMail),
clearCache: Object.assign(clearCache, clearCache),
export: Object.assign(exportMethod, exportMethod),
import: Object.assign(importMethod, importMethod),
}

export default settings