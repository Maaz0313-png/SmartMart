import UserController from './UserController'
import ProductController from './ProductController'
import OrderController from './OrderController'
import AnalyticsController from './AnalyticsController'
const Admin = {
    UserController: Object.assign(UserController, UserController),
ProductController: Object.assign(ProductController, ProductController),
OrderController: Object.assign(OrderController, OrderController),
AnalyticsController: Object.assign(AnalyticsController, AnalyticsController),
}

export default Admin