const {Component, Mixin} = Shopware;
import template from './index.html.twig';

Component.register('dewa-google-api-test', {
    template,

    props: ['label'],
    inject: ['dewaShopApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        pluginConfig() {
            let $parent = this.$parent;

            while ($parent.actualConfigData === undefined) {
                $parent = $parent.$parent;
            }

            return $parent.actualConfigData.null;
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;

            this.dewaShopApiService.post(`/dewa/google-api-test`, this.pluginConfig).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('dewa-shop.notification.apiTestSuccess')
                });

                this.isLoading = false;
            }).catch((exception) => {
                const detail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');

                this.createNotificationError({
                    title: this.$tc('dewa-shop.notification.apiTestFailed'),
                    message: detail
                });

                this.isLoading = false;
            });
        },
    }
})