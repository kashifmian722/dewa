const { Module } = Shopware;

import './page/list';
import './page/detail';
import './page/create';

Module.register('dewa-option', {
    type: 'plugin',
    name: 'dewa-option',
    title: 'dewa-shop.navigation.option',
    routes: {
        list: {
            component: 'dewa-option-list',
            path: 'list'
        },
        detail: {
            component: 'dewa-option-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'dewa.option.list'
            }
        },
        create: {
            component: 'dewa-option-create',
            path: 'create',
            meta: {
                parentPath: 'dewa.option.list'
            }
        }
    },
    settingsItem: [
        {
            privilege: 'dewa_option:read',
            name: 'dewa-option',
            to: 'dewa.option.list',
            group: 'plugins',
            iconComponent: 'appflix-svg-dewa',
            id: 'dewa-setting-option',
            label: 'dewa-shop.navigation.option',
        }
    ]
});
