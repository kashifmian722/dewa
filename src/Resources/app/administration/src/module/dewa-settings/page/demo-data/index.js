const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('dewa-settings-demo-data', {
    template,

    inject: [
        'repositoryFactory',
        'dewaShopApiService'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            item: {
                salesChannelId: null
            },
            options: null,
            isLoading: true,
            processSuccess: false
        };
    },

    created() {
        this.getOptions();
    },

    methods: {
        getOptions() {
            this.isLoading = true;

            this.dewaShopApiService.get(`/dewa/settings/demo-data/options`).then(response => {
                this.options = response;
                this.item.type = response[0];

                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        },

        install() {
            this.isLoading = true;

            this.dewaShopApiService.get(`/dewa/settings/demo-data/install/${this.item.type}/${this.item.salesChannelId}`).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('dewa-shop.notification.demoDataInstalled')
                });

                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        },

        remove() {
            this.isLoading = true;

            this.dewaShopApiService.get(`/dewa/settings/demo-data/remove/${this.item.salesChannelId}`).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('dewa-shop.notification.demoDataRemoved')
                });

                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        }
    }
});
