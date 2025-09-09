import Auth from './Auth'
import Api from './Api'
import HomeController from './HomeController'
import DashboardController from './DashboardController'
import CartController from './CartController'
import CheckoutController from './CheckoutController'
import OrderController from './OrderController'
import SubscriptionController from './SubscriptionController'
import ProductController from './ProductController'
import NotificationController from './NotificationController'
import GdprController from './GdprController'
import Admin from './Admin'
import Seller from './Seller'
import StripeWebhookController from './StripeWebhookController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Api: Object.assign(Api, Api),
HomeController: Object.assign(HomeController, HomeController),
DashboardController: Object.assign(DashboardController, DashboardController),
CartController: Object.assign(CartController, CartController),
CheckoutController: Object.assign(CheckoutController, CheckoutController),
OrderController: Object.assign(OrderController, OrderController),
SubscriptionController: Object.assign(SubscriptionController, SubscriptionController),
ProductController: Object.assign(ProductController, ProductController),
NotificationController: Object.assign(NotificationController, NotificationController),
GdprController: Object.assign(GdprController, GdprController),
Admin: Object.assign(Admin, Admin),
Seller: Object.assign(Seller, Seller),
StripeWebhookController: Object.assign(StripeWebhookController, StripeWebhookController),
}

export default Controllers