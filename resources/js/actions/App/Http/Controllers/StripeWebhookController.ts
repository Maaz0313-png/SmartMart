import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StripeWebhookController::handleWebhook
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
export const handleWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleWebhook.url(options),
    method: 'post',
})

handleWebhook.definition = {
    methods: ["post"],
    url: '/webhooks/stripe',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StripeWebhookController::handleWebhook
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
handleWebhook.url = (options?: RouteQueryOptions) => {
    return handleWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StripeWebhookController::handleWebhook
 * @see app/Http/Controllers/StripeWebhookController.php:16
 * @route '/webhooks/stripe'
 */
handleWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleWebhook.url(options),
    method: 'post',
})
const StripeWebhookController = { handleWebhook }

export default StripeWebhookController