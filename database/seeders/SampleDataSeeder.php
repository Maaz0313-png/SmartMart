<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\Category;
use App\Models\Product;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->createSubscriptionPlans();
        $this->createCategories();
        $this->createSampleProducts();
    }

    private function createSubscriptionPlans()
    {
        $plans = [
            [
                'name' => 'Basic Box',
                'slug' => 'basic-box',
                'description' => 'Perfect for trying new products. Get 3-5 carefully curated items each month.',
                'price' => 19.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'max_products' => 5,
                'features' => [
                    '3-5 products per box',
                    'Curated by experts',
                    'Free shipping',
                    'Cancel anytime',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Premium Box',
                'slug' => 'premium-box',
                'description' => 'Our most popular plan. Get 6-8 premium items with exclusive products.',
                'price' => 39.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_products' => 8,
                'features' => [
                    '6-8 premium products per box',
                    'Exclusive items',
                    'Priority curation',
                    'Free shipping',
                    'Member-only discounts',
                    'Cancel anytime',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Box',
                'slug' => 'deluxe-box',
                'description' => 'The ultimate experience. Get 10+ luxury items including full-size products.',
                'price' => 79.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_products' => 12,
                'features' => [
                    '10+ luxury products per box',
                    'Full-size items included',
                    'Limited edition products',
                    'Personal curation',
                    'Free express shipping',
                    'Exclusive member events',
                    'Cancel anytime',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Quarterly Box',
                'slug' => 'quarterly-box',
                'description' => 'Get seasonal collections delivered every 3 months with significant savings.',
                'price' => 99.99,
                'billing_cycle' => 'quarterly',
                'trial_days' => 0,
                'max_products' => 15,
                'features' => [
                    '15+ seasonal products',
                    'Quarterly themed collections',
                    'Best value pricing',
                    'Exclusive seasonal items',
                    'Free shipping',
                    'Cancel anytime',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('Subscription plans created successfully!');
    }

    private function createCategories()
    {
        $categories = [
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'description' => 'Skincare, makeup, haircare, and personal care products',
                'is_active' => true,
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Skincare', 'slug' => 'skincare'],
                    ['name' => 'Makeup', 'slug' => 'makeup'],
                    ['name' => 'Hair Care', 'slug' => 'hair-care'],
                    ['name' => 'Fragrance', 'slug' => 'fragrance'],
                ]
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Vitamins, supplements, fitness, and wellness products',
                'is_active' => true,
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Vitamins & Supplements', 'slug' => 'vitamins-supplements'],
                    ['name' => 'Fitness', 'slug' => 'fitness'],
                    ['name' => 'Mental Wellness', 'slug' => 'mental-wellness'],
                ]
            ],
            [
                'name' => 'Food & Beverages',
                'slug' => 'food-beverages',
                'description' => 'Gourmet foods, snacks, teas, and specialty beverages',
                'is_active' => true,
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Gourmet Snacks', 'slug' => 'gourmet-snacks'],
                    ['name' => 'Teas & Coffee', 'slug' => 'teas-coffee'],
                    ['name' => 'Specialty Foods', 'slug' => 'specialty-foods'],
                ]
            ],
            [
                'name' => 'Home & Lifestyle',
                'slug' => 'home-lifestyle',
                'description' => 'Home decor, lifestyle accessories, and everyday essentials',
                'is_active' => true,
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Home Decor', 'slug' => 'home-decor'],
                    ['name' => 'Candles & Aromatherapy', 'slug' => 'candles-aromatherapy'],
                    ['name' => 'Lifestyle Accessories', 'slug' => 'lifestyle-accessories'],
                ]
            ],
            [
                'name' => 'Fashion & Accessories',
                'slug' => 'fashion-accessories',
                'description' => 'Clothing, jewelry, bags, and fashion accessories',
                'is_active' => true,
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Jewelry', 'slug' => 'jewelry'],
                    ['name' => 'Bags & Purses', 'slug' => 'bags-purses'],
                    ['name' => 'Accessories', 'slug' => 'accessories'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create($categoryData);

            foreach ($children as $index => $childData) {
                $childData['parent_id'] = $category->id;
                $childData['is_active'] = true;
                $childData['sort_order'] = $index + 1;
                $childData['description'] = $childData['description'] ?? '';

                Category::create($childData);
            }
        }

        $this->command->info('Categories created successfully!');
    }

    private function createSampleProducts()
    {
        // Get admin user for products
        $adminUser = \App\Models\User::where('email', 'admin@smartmart.com')->first();
        if (!$adminUser) {
            $this->command->warn('Admin user not found. Please run RolesAndPermissionsSeeder first.');
            return;
        }

        $categories = Category::whereNotNull('parent_id')->get();

        $sampleProducts = [
            [
                'name' => 'Vitamin C Serum',
                'description' => 'A powerful antioxidant serum that brightens skin and reduces signs of aging.',
                'short_description' => 'Brightening vitamin C serum for glowing skin.',
                'price' => 29.99,
                'compare_price' => 39.99,
                'quantity' => 50,
                'weight' => 0.03,
                'tags' => ['skincare', 'vitamin-c', 'anti-aging'],
                'is_featured' => true,
            ],
            [
                'name' => 'Organic Green Tea',
                'description' => 'Premium organic green tea with antioxidants and natural flavors.',
                'short_description' => 'Premium organic green tea blend.',
                'price' => 24.99,
                'quantity' => 100,
                'weight' => 0.1,
                'tags' => ['organic', 'tea', 'antioxidants'],
                'is_featured' => true,
            ],
            [
                'name' => 'Bamboo Candle Set',
                'description' => 'Set of 3 eco-friendly bamboo candles with natural soy wax.',
                'short_description' => 'Eco-friendly bamboo candles with soy wax.',
                'price' => 34.99,
                'quantity' => 25,
                'weight' => 0.5,
                'tags' => ['candles', 'eco-friendly', 'bamboo'],
                'is_featured' => false,
            ],
            [
                'name' => 'Protein Energy Bars',
                'description' => 'High-protein energy bars made with natural ingredients.',
                'short_description' => 'Natural high-protein energy bars.',
                'price' => 19.99,
                'quantity' => 200,
                'weight' => 0.15,
                'tags' => ['protein', 'energy', 'natural'],
                'is_featured' => false,
            ],
        ];

        foreach ($sampleProducts as $index => $productData) {
            $category = $categories->random();
            $productData['category_id'] = $category->id;
            $productData['user_id'] = $adminUser->id;
            $productData['sku'] = 'PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $productData['slug'] = \Illuminate\Support\Str::slug($productData['name']);
            $productData['status'] = 'active';
            $productData['published_at'] = now();
            Product::create($productData);
        }
        $this->command->info('Sample products created successfully!');
    }
}