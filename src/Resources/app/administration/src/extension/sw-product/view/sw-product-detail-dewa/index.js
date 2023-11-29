const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapState} = Shopware.Component.getComponentHelper();

import template from './index.html.twig';

Component.register('sw-product-detail-dewa', {
    template,

    inject: ['repositoryFactory', 'context'],

    props: {
        product: {
            type: Object,
            required: true
        }
    },

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            isLoading: false
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ]),

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        product() {
            const product = Shopware.State.get('swProductDetail').product;

            if (!product.customFields) {
                this.$set(product, 'customFields', {});
            }

            return product;
        },

        optionProductFilterColumns() {
            return [
                'product.name',
                'name',
                'option.name',
                'option.type',
                'priority'
            ];
        },

        optionProductCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('product');
            criteria.addAssociation('option');

            return criteria;
        },

        stockFilterColumns() {
            return [
                'product.name',
                'shop.name',
                'isStock',
                'stock',
                'info'
            ];
        },

        stockCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('product');
            criteria.addAssociation('shop');

            return criteria;
        },

        bundleFilterColumns() {
            return [
                'priority',
                'product.name',
                'accessoryProduct.name',
                'accessoryStream.name'
            ];
        },

        bundleCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('product');
            criteria.addAssociation('accessoryProduct');
            criteria.addAssociation('accessoryStream');

            return criteria;
        }
    },

    methods: {
        saveProduct() {
            if (this.product) {
                this.productRepository.save(this.product, Shopware.Context.api);
            }
        }
    }
});
