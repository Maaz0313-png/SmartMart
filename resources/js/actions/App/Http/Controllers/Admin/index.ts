import DashboardController from './DashboardController'
import UserController from './UserController'
import ProductController from './ProductController'
import OrderController from './OrderController'
import SubscriptionController from './SubscriptionController'
import CategoryController from './CategoryController'
import AnalyticsController from './AnalyticsController'
import SettingsController from './SettingsController'
import GdprController from './GdprController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
UserController: Object.assign(UserController, UserController),
ProductController: Object.assign(ProductController, ProductController),
OrderController: Object.assign(OrderController, OrderController),
SubscriptionController: Object.assign(SubscriptionController, SubscriptionController),
CategoryController: Object.assign(CategoryController, CategoryController),
AnalyticsController: Object.assign(AnalyticsController, AnalyticsController),
SettingsController: Object.assign(SettingsController, SettingsController),
GdprController: Object.assign(GdprController, GdprController),
}

export default Admin