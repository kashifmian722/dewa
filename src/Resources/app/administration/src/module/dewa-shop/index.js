const { Module } = Shopware;

import './page/list';
import './page/detail';
import './page/create';

Module.register('dewa-shop', {
    type: 'core',
    name: 'dewa-shop',
    title: 'dewa-shop.navigation.shop',
    routes: {
        list: {
            component: 'dewa-shop-list',
            path: 'list'
        },
        detail: {
            component: 'dewa-shop-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'dewa.shop.list'
            }
        },
        create: {
            component: 'dewa-shop-create',
            path: 'create',
            meta: {
                parentPath: 'dewa.shop.list'
            }
        }
    },
    navigation: [{
        privilege: 'dewa_shop:read',
        label: 'dewa-shop.navigation.main',
        color: '#d95030',
        path: 'dewa.shop.list',
        position: 20,
        parent: 'sw-dashboard'
    }]
});
