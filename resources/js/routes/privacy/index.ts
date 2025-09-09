import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:15
 * @route '/privacy-policy'
 */
export const policy = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: policy.url(options),
    method: 'get',
})

policy.definition = {
    methods: ["get","head"],
    url: '/privacy-policy',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:15
 * @route '/privacy-policy'
 */
policy.url = (options?: RouteQueryOptions) => {
    return policy.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:15
 * @route '/privacy-policy'
 */
policy.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: policy.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:15
 * @route '/privacy-policy'
 */
policy.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: policy.url(options),
    method: 'head',
})
const privacy = {
    policy: Object.assign(policy, policy),
}

export default privacy