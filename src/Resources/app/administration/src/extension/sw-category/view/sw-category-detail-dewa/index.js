const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapApiErrors, mapGetters, mapState} = Shopware.Component.getComponentHelper();

import template from './index.html.twig';

Component.register('sw-category-detail-dewa', {
    template,

    inject: ['repositoryFactory', 'context'],

    props: {
        category: {
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
        ...mapState('swCategoryDetail', [
            'category'
        ]),

        category() {
            return Shopware.State.get('swCategoryDetail').category;
        },

        shopFilterColumns() {
            return [
                'category.name',
                'name',
                'city',
                'active'
            ];
        },

        shopCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('category');

            return criteria;
        },

        optionCategoryFilterColumns() {
            return [
                'category.name',
                'name',
                'option.name',
                'option.type',
                'priority'
            ];
        },

        optionCategoryCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('category');
            criteria.addAssociation('option');

            return criteria;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {}
    }
});
