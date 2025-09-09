import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::overview
 * @see app/Http/Controllers/Admin/AnalyticsController.php:25
 * @route '/admin/analytics'
 */
export const overview = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})

overview.definition = {
    methods: ["get","head"],
    url: '/admin/analytics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::overview
 * @see app/Http/Controllers/Admin/AnalyticsController.php:25
 * @route '/admin/analytics'
 */
overview.url = (options?: RouteQueryOptions) => {
    return overview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::overview
 * @see app/Http/Controllers/Admin/AnalyticsController.php:25
 * @route '/admin/analytics'
 */
overview.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::overview
 * @see app/Http/Controllers/Admin/AnalyticsController.php:25
 * @route '/admin/analytics'
 */
overview.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overview.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::sales
 * @see app/Http/Controllers/Admin/AnalyticsController.php:65
 * @route '/admin/analytics/sales'
 */
export const sales = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sales.url(options),
    method: 'get',
})

sales.definition = {
    methods: ["get","head"],
    url: '/admin/analytics/sales',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::sales
 * @see app/Http/Controllers/Admin/AnalyticsController.php:65
 * @route '/admin/analytics/sales'
 */
sales.url = (options?: RouteQueryOptions) => {
    return sales.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::sales
 * @see app/Http/Controllers/Admin/AnalyticsController.php:65
 * @route '/admin/analytics/sales'
 */
sales.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sales.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::sales
 * @see app/Http/Controllers/Admin/AnalyticsController.php:65
 * @route '/admin/analytics/sales'
 */
sales.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sales.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::products
 * @see app/Http/Controllers/Admin/AnalyticsController.php:122
 * @route '/admin/analytics/products'
 */
export const products = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})

products.definition = {
    methods: ["get","head"],
    url: '/admin/analytics/products',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::products
 * @see app/Http/Controllers/Admin/AnalyticsController.php:122
 * @route '/admin/analytics/products'
 */
products.url = (options?: RouteQueryOptions) => {
    return products.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::products
 * @see app/Http/Controllers/Admin/AnalyticsController.php:122
 * @route '/admin/analytics/products'
 */
products.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::products
 * @see app/Http/Controllers/Admin/AnalyticsController.php:122
 * @route '/admin/analytics/products'
 */
products.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: products.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::customers
 * @see app/Http/Controllers/Admin/AnalyticsController.php:191
 * @route '/admin/analytics/customers'
 */
export const customers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})

customers.definition = {
    methods: ["get","head"],
    url: '/admin/analytics/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::customers
 * @see app/Http/Controllers/Admin/AnalyticsController.php:191
 * @route '/admin/analytics/customers'
 */
customers.url = (options?: RouteQueryOptions) => {
    return customers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::customers
 * @see app/Http/Controllers/Admin/AnalyticsController.php:191
 * @route '/admin/analytics/customers'
 */
customers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::customers
 * @see app/Http/Controllers/Admin/AnalyticsController.php:191
 * @route '/admin/analytics/customers'
 */
customers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customers.url(options),
    method: 'head',
})
const analytics = {
    overview: Object.assign(overview, overview),
sales: Object.assign(sales, sales),
products: Object.assign(products, products),
customers: Object.assign(customers, customers),
}

export default analytics