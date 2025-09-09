import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\StripeWebhookController::stripe
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
export const stripe = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stripe.url(options),
    method: 'post',
})

stripe.definition = {
    methods: ["post"],
    url: '/webhooks/stripe',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StripeWebhookController::stripe
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
stripe.url = (options?: RouteQueryOptions) => {
    return stripe.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StripeWebhookController::stripe
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
stripe.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stripe.url(options),
    method: 'post',
})
const webhooks = {
    stripe: Object.assign(stripe, stripe),
}

export default webhooks