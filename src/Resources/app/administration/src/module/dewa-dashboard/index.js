const { Module } = Shopware;

import './page/detail';

Module.register('dewa-dashboard', {
    type: 'plugin',
    name: 'dewa-option-dashboard',
    title: 'dewa-shop.navigation.dashboard',
    routes: {
        detail: {
            component: 'dewa-dashboard-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'dewa.shop.list'
            }
        },
    }
});
