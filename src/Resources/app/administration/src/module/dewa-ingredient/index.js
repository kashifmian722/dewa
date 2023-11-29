const { Module } = Shopware;

import './page/list';
import './page/detail';
import './page/create';

Module.register('dewa-ingredient', {
    type: 'plugin',
    name: 'dewa-ingredient',
    title: 'dewa-shop.navigation.ingredient',
    routes: {
        list: {
            component: 'dewa-ingredient-list',
            path: 'list'
        },
        detail: {
            component: 'dewa-ingredient-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'dewa.ingredient.list'
            }
        },
        create: {
            component: 'dewa-ingredient-create',
            path: 'create',
            meta: {
                parentPath: 'dewa.ingredient.list'
            }
        }
    },
    settingsItem: [
        {
            privilege: 'dewa_ingredient:read',
            name: 'dewa-ingredient',
            to: 'dewa.ingredient.list',
            group: 'plugins',
            iconComponent: 'appflix-svg-dewa',
            id: 'dewa-setting-ingredient',
            label: 'dewa-shop.navigation.ingredient',
        }
    ]
});
