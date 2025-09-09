import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
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
* @see \App\Http\Controllers\Admin\SettingsController::testMailConfiguration
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
export const testMailConfiguration = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMailConfiguration.url(options),
    method: 'post',
})

testMailConfiguration.definition = {
    methods: ["post"],
    url: '/admin/settings/test-mail',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::testMailConfiguration
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
testMailConfiguration.url = (options?: RouteQueryOptions) => {
    return testMailConfiguration.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::testMailConfiguration
 * @see app/Http/Controllers/Admin/SettingsController.php:88
 * @route '/admin/settings/test-mail'
 */
testMailConfiguration.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testMailConfiguration.url(options),
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
* @see \App\Http\Controllers\Admin\SettingsController::exportSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
export const exportSettings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSettings.url(options),
    method: 'get',
})

exportSettings.definition = {
    methods: ["get","head"],
    url: '/admin/settings/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::exportSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportSettings.url = (options?: RouteQueryOptions) => {
    return exportSettings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::exportSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportSettings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSettings.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::exportSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:121
 * @route '/admin/settings/export'
 */
exportSettings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportSettings.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\SettingsController::importSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
export const importSettings = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSettings.url(options),
    method: 'post',
})

importSettings.definition = {
    methods: ["post"],
    url: '/admin/settings/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::importSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
importSettings.url = (options?: RouteQueryOptions) => {
    return importSettings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::importSettings
 * @see app/Http/Controllers/Admin/SettingsController.php:134
 * @route '/admin/settings/import'
 */
importSettings.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSettings.url(options),
    method: 'post',
})
const SettingsController = { index, update, uploadLogo, testMailConfiguration, clearCache, exportSettings, importSettings }

export default SettingsController