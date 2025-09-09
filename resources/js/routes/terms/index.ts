import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:18
 * @route '/terms-of-service'
 */
export const service = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: service.url(options),
    method: 'get',
})

service.definition = {
    methods: ["get","head"],
    url: '/terms-of-service',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:18
 * @route '/terms-of-service'
 */
service.url = (options?: RouteQueryOptions) => {
    return service.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:18
 * @route '/terms-of-service'
 */
service.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: service.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:18
 * @route '/terms-of-service'
 */
service.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: service.url(options),
    method: 'head',
})
const terms = {
    service: Object.assign(service, service),
}

export default terms