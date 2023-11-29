const {Component} = Shopware;

import template from './index.html.twig';

Component.register('dewa-settings-config', {
    template,

    data() {},

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.$router.push({name: 'sw.extension.config', params: {namespace: 'AppflixDewaShop'}});
            location.reload();
        }
    }
});
