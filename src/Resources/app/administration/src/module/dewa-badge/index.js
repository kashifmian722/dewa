const { Module } = Shopware;

import './page/list';
import './page/detail';
import './page/create';

Module.register('dewa-badge', {
    type: 'plugin',
    name: 'dewa-badge',
    title: 'dewa-shop.navigation.badge',
    routes: {
        list: {
            component: 'dewa-badge-list',
            path: 'list'
        },
        detail: {
            component: 'dewa-badge-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'dewa.badge.list'
            }
        },
        create: {
            component: 'dewa-badge-create',
            path: 'create',
            meta: {
                parentPath: 'dewa.badge.list'
            }
        }
    },
    settingsItem: [
        {
            privilege: 'dewa_badge:read',
            name: 'dewa-badge',
            to: 'dewa.badge.list',
            group: 'plugins',
            iconComponent: 'appflix-svg-dewa',
            id: 'dewa-setting-badge',
            label: 'dewa-shop.navigation.badge',
        }
    ]
});
