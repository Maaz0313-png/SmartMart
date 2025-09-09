import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\CategoryController::index
 * @see app/Http/Controllers/Admin/CategoryController.php:23
 * @route '/admin/categories'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::index
 * @see app/Http/Controllers/Admin/CategoryController.php:23
 * @route '/admin/categories'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::index
 * @see app/Http/Controllers/Admin/CategoryController.php:23
 * @route '/admin/categories'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\CategoryController::index
 * @see app/Http/Controllers/Admin/CategoryController.php:23
 * @route '/admin/categories'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::create
 * @see app/Http/Controllers/Admin/CategoryController.php:67
 * @route '/admin/categories/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/categories/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::create
 * @see app/Http/Controllers/Admin/CategoryController.php:67
 * @route '/admin/categories/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::create
 * @see app/Http/Controllers/Admin/CategoryController.php:67
 * @route '/admin/categories/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\CategoryController::create
 * @see app/Http/Controllers/Admin/CategoryController.php:67
 * @route '/admin/categories/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::store
 * @see app/Http/Controllers/Admin/CategoryController.php:79
 * @route '/admin/categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::store
 * @see app/Http/Controllers/Admin/CategoryController.php:79
 * @route '/admin/categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::store
 * @see app/Http/Controllers/Admin/CategoryController.php:79
 * @route '/admin/categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::show
 * @see app/Http/Controllers/Admin/CategoryController.php:115
 * @route '/admin/categories/{category}'
 */
export const show = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::show
 * @see app/Http/Controllers/Admin/CategoryController.php:115
 * @route '/admin/categories/{category}'
 */
show.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return show.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::show
 * @see app/Http/Controllers/Admin/CategoryController.php:115
 * @route '/admin/categories/{category}'
 */
show.get = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\CategoryController::show
 * @see app/Http/Controllers/Admin/CategoryController.php:115
 * @route '/admin/categories/{category}'
 */
show.head = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::edit
 * @see app/Http/Controllers/Admin/CategoryController.php:132
 * @route '/admin/categories/{category}/edit'
 */
export const edit = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/categories/{category}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::edit
 * @see app/Http/Controllers/Admin/CategoryController.php:132
 * @route '/admin/categories/{category}/edit'
 */
edit.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return edit.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::edit
 * @see app/Http/Controllers/Admin/CategoryController.php:132
 * @route '/admin/categories/{category}/edit'
 */
edit.get = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\CategoryController::edit
 * @see app/Http/Controllers/Admin/CategoryController.php:132
 * @route '/admin/categories/{category}/edit'
 */
edit.head = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::update
 * @see app/Http/Controllers/Admin/CategoryController.php:146
 * @route '/admin/categories/{category}'
 */
export const update = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::update
 * @see app/Http/Controllers/Admin/CategoryController.php:146
 * @route '/admin/categories/{category}'
 */
update.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return update.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::update
 * @see app/Http/Controllers/Admin/CategoryController.php:146
 * @route '/admin/categories/{category}'
 */
update.patch = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::destroy
 * @see app/Http/Controllers/Admin/CategoryController.php:190
 * @route '/admin/categories/{category}'
 */
export const destroy = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::destroy
 * @see app/Http/Controllers/Admin/CategoryController.php:190
 * @route '/admin/categories/{category}'
 */
destroy.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroy.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::destroy
 * @see app/Http/Controllers/Admin/CategoryController.php:190
 * @route '/admin/categories/{category}'
 */
destroy.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::bulkAction
 * @see app/Http/Controllers/Admin/CategoryController.php:219
 * @route '/admin/categories/bulk-action'
 */
export const bulkAction = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkAction.url(options),
    method: 'post',
})

bulkAction.definition = {
    methods: ["post"],
    url: '/admin/categories/bulk-action',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::bulkAction
 * @see app/Http/Controllers/Admin/CategoryController.php:219
 * @route '/admin/categories/bulk-action'
 */
bulkAction.url = (options?: RouteQueryOptions) => {
    return bulkAction.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::bulkAction
 * @see app/Http/Controllers/Admin/CategoryController.php:219
 * @route '/admin/categories/bulk-action'
 */
bulkAction.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkAction.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::reorder
 * @see app/Http/Controllers/Admin/CategoryController.php:270
 * @route '/admin/categories/reorder'
 */
export const reorder = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reorder.url(options),
    method: 'post',
})

reorder.definition = {
    methods: ["post"],
    url: '/admin/categories/reorder',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::reorder
 * @see app/Http/Controllers/Admin/CategoryController.php:270
 * @route '/admin/categories/reorder'
 */
reorder.url = (options?: RouteQueryOptions) => {
    return reorder.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::reorder
 * @see app/Http/Controllers/Admin/CategoryController.php:270
 * @route '/admin/categories/reorder'
 */
reorder.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reorder.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\CategoryController::getTree
 * @see app/Http/Controllers/Admin/CategoryController.php:286
 * @route '/admin/categories/tree/view'
 */
export const getTree = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTree.url(options),
    method: 'get',
})

getTree.definition = {
    methods: ["get","head"],
    url: '/admin/categories/tree/view',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CategoryController::getTree
 * @see app/Http/Controllers/Admin/CategoryController.php:286
 * @route '/admin/categories/tree/view'
 */
getTree.url = (options?: RouteQueryOptions) => {
    return getTree.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CategoryController::getTree
 * @see app/Http/Controllers/Admin/CategoryController.php:286
 * @route '/admin/categories/tree/view'
 */
getTree.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTree.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\CategoryController::getTree
 * @see app/Http/Controllers/Admin/CategoryController.php:286
 * @route '/admin/categories/tree/view'
 */
getTree.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getTree.url(options),
    method: 'head',
})
const CategoryController = { index, create, store, show, edit, update, destroy, bulkAction, reorder, getTree }

export default CategoryController