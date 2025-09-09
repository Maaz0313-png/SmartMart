import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import products from './products'
/**
 * @see routes/web.php:223
 * @route '/seller'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/seller',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:223
 * @route '/seller'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:223
 * @route '/seller'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:223
 * @route '/seller'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})
const seller = {
    dashboard: Object.assign(dashboard, dashboard),
products: Object.assign(products, products),
}

export default seller