const {Module} = Shopware;

import './page/config';
import './page/demo-data';
/*
import './page/deliverer-app';
import './page/printer-binaries';
*/

Module.register('dewa-settings', {
    type: 'plugin',
    name: 'dewa-settings',
    title: 'dewa-shop.navigation.demoData',

    routes: {
        config: {
            component: 'dewa-settings-config',
            path: 'config'
        }
        /*
        delivererapp: {
            component: 'dewa-settings-deliverer-app',
            path: 'delivererapp',
            meta: {
                parentPath: 'sw.settings.index'
            },
        },
        printerbinaries: {
            component: 'dewa-settings-printer-binaries',
            path: 'printerbinaries',
            meta: {
                parentPath: 'sw.settings.index'
            },
        } */
    },

    settingsItem: [
        {
            privilege: 'system.system_config',
            name: 'dewa-settings-config',
            to: 'dewa.settings.config',
            group: 'plugins',
            iconComponent: 'appflix-svg-dewa',
            id: 'dewa-setting-config',
            label: 'dewa-shop.navigation.config'
        }
        /*
        {
            name: 'ewa-settings-deliverer-app',
            to: 'dewa.settings.delivererapp',
            group: 'dewa',
            icon: 'default-object-lab-flask',
            label: 'dewa-shop.navigation.delivererApp'
        },
        {
            name: 'dewa-settings-printer-binaries',
            to: 'dewa.settings.printerbinaries',
            group: 'dewa',
            icon: 'default-object-lab-flask',
            label: 'dewa-shop.navigation.printerBinaries'
        },*/
    ]
});
