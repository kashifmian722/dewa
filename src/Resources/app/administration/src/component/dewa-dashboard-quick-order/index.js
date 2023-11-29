const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('dewa-dashboard-quick-order', {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'orderStateMachineService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: [],

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('dewa_shop_order');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        criteria() {
            const criteria = new Criteria();

            criteria.addAssociation('media');
            criteria.addAssociation('category');
            criteria.addAssociation('deliverers.media');
            criteria.addAssociation('products');
            criteria.addAssociation('salesChannels');

            return criteria;
        },
    },

    watch: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getItem();
        },

        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.isLoading = false;
        },

        onCloseModal() {
            console.log('modal-close');
            this.$emit('modal-close');
        },

        onSaveQickOrder() {
            this.onCloseModal();

            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: this.$tc('global.default.error'),
                    });
                });
        },
    }
});
