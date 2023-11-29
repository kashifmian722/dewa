const {Module} = Shopware;

import './page/sw-product-detail';
import './view/sw-product-detail-dewa';

Module.register('sw-product-detail-dewa-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'sw.product.detail.dewa',
                path: '/sw/product/detail/:id/dewa',
                component: 'sw-product-detail-dewa',
                meta: {
                    parentPath: "sw.product.index"
                }
            });
        }
        next(currentRoute);
    }
});
