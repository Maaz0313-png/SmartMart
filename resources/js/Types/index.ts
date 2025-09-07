export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    phone?: string;
    avatar?: string;
    is_active: boolean;
    roles?: Role[];
    created_at: string;
    updated_at: string;
}

export interface Role {
    id: number;
    name: string;
    permissions?: Permission[];
}

export interface Permission {
    id: number;
    name: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    parent_id?: number;
    sort_order: number;
    is_active: boolean;
    is_featured: boolean;
    meta_title?: string;
    meta_description?: string;
    commission_rate?: number;
    children?: Category[];
    products_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    short_description?: string;
    sku: string;
    price: number;
    compare_price?: number;
    cost_price?: number;
    quantity: number;
    min_quantity: number;
    track_quantity: boolean;
    status: 'active' | 'inactive' | 'draft';
    images?: string[];
    weight?: number;
    weight_unit: string;
    dimensions?: {
        length?: number;
        width?: number;
        height?: number;
    };
    category_id: number;
    user_id: number;
    tags?: string[];
    meta_data?: any;
    is_featured: boolean;
    is_digital: boolean;
    seo_data?: any;
    published_at?: string;
    main_image?: string;
    image_gallery?: string[];
    formatted_price: string;
    discount_percentage?: number;
    average_rating: number;
    reviews_count: number;
    category?: Category;
    seller?: User;
    variants?: ProductVariant[];
    reviews?: Review[];
    created_at: string;
    updated_at: string;
}

export interface ProductVariant {
    id: number;
    product_id: number;
    name: string;
    sku: string;
    price: number;
    cost_price?: number;
    quantity: number;
    weight?: number;
    dimensions?: any;
    options?: any;
    is_active: boolean;
    formatted_price: string;
    product?: Product;
    created_at: string;
    updated_at: string;
}

export interface Cart {
    id: number;
    user_id?: number;
    session_id?: string;
    total_amount: number;
    item_count: number;
    formatted_total_amount: string;
    items: CartItem[];
    created_at: string;
    updated_at: string;
}

export interface CartItem {
    id: number;
    cart_id: number;
    product_id: number;
    product_variant_id?: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    formatted_unit_price: string;
    formatted_total_price: string;
    product: Product;
    product_variant?: ProductVariant;
    created_at: string;
    updated_at: string;
}

export interface Order {
    id: number;
    order_number: string;
    user_id: number;
    status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
    subtotal: number;
    tax_amount: number;
    shipping_amount: number;
    discount_amount: number;
    total_amount: number;
    currency: string;
    payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
    payment_method: string;
    payment_reference?: string;
    billing_address: any;
    shipping_address: any;
    coupon_code?: string;
    notes?: string;
    shipped_at?: string;
    delivered_at?: string;
    tracking_info?: any;
    formatted_total: string;
    status_label: string;
    user?: User;
    items: OrderItem[];
    created_at: string;
    updated_at: string;
}

export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number;
    product_variant_id?: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    product_snapshot?: any;
    formatted_unit_price: string;
    formatted_total_price: string;
    order?: Order;
    product?: Product;
    product_variant?: ProductVariant;
    created_at: string;
    updated_at: string;
}

export interface Review {
    id: number;
    product_id: number;
    user_id: number;
    rating: number;
    title: string;
    comment: string;
    is_verified_purchase: boolean;
    is_approved: boolean;
    helpful_count: number;
    product?: Product;
    user?: User;
    created_at: string;
    updated_at: string;
}

export interface SubscriptionPlan {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    billing_cycle: 'weekly' | 'monthly' | 'quarterly' | 'yearly';
    trial_days: number;
    features?: any;
    stripe_plan_id?: string;
    paypal_plan_id?: string;
    is_active: boolean;
    max_products?: number;
    categories?: any;
    created_at: string;
    updated_at: string;
}

export interface Subscription {
    id: number;
    user_id: number;
    subscription_plan_id: number;
    stripe_subscription_id?: string;
    paypal_subscription_id?: string;
    status: 'active' | 'cancelled' | 'past_due' | 'paused' | 'expired';
    current_period_start: string;
    current_period_end: string;
    trial_ends_at?: string;
    cancelled_at?: string;
    paused_at?: string;
    preferences?: any;
    price: number;
    user?: User;
    subscription_plan?: SubscriptionPlan;
    created_at: string;
    updated_at: string;
}

export interface SearchResults {
    hits: Product[];
    total: number;
    processing_time: number;
    query: string;
    offset?: number;
    limit?: number;
}

export interface SearchFacets {
    categories: Array<{
        name: string;
        count: number;
        value: string;
    }>;
    price_ranges: Array<{
        name: string;
        count: number;
        min: number;
        max: number;
    }>;
    brands: Array<{
        name: string;
        count: number;
        value: string;
    }>;
    ratings: Array<{
        rating: number;
        count: number;
    }>;
}

export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> {
    auth: {
        user: User;
    };
    flash?: {
        message?: string;
        success?: string;
        error?: string;
    };
}

// Pagination types
export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links: {
        first: string;
        last: string;
        prev?: string;
        next?: string;
    };
}