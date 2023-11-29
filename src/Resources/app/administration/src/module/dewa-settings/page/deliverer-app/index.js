const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('dewa-settings-deliverer-app', {
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
            isLoading: false,
            processSuccess: false
        };
    },

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {

        }
    }
});
