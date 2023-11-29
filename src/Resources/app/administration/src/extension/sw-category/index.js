const {Module} = Shopware;

import './view/sw-category-view';
import './view/sw-category-detail-dewa';

Module.register('sw-category-detail-dewa-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.category.detail') {
            currentRoute.children.push({
                name: 'sw.category.detail.dewa',
                path: '/sw/category/index/:id/dewa',
                component: 'sw-category-detail-dewa',
                meta: {
                    parentPath: "sw.category.index"
                }
            });
        }
        next(currentRoute);
    }
});
