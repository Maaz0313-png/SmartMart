import ProductController from './ProductController'
import CategoryController from './CategoryController'
import CartController from './CartController'
import OrderController from './OrderController'
import ReviewController from './ReviewController'
import Seller from './Seller'
import Admin from './Admin'
import ApiDocController from './ApiDocController'
const Api = {
    ProductController: Object.assign(ProductController, ProductController),
CategoryController: Object.assign(CategoryController, CategoryController),
CartController: Object.assign(CartController, CartController),
OrderController: Object.assign(OrderController, OrderController),
ReviewController: Object.assign(ReviewController, ReviewController),
Seller: Object.assign(Seller, Seller),
Admin: Object.assign(Admin, Admin),
ApiDocController: Object.assign(ApiDocController, ApiDocController),
}

export default Api