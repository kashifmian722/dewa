const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('dewa-shop-list', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            sortBy: 'name'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'id',
                    dataIndex: 'id',
                    label: this.$tc('dewa-shop.action.action'),
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('dewa-shop.properties.name'),
                    routerLink: 'dewa.shop.detail',
                    primary: true
                },
                {
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$tc('dewa-shop.properties.active')
                },
                {
                    property: 'deliveryActive',
                    dataIndex: 'deliveryActive',
                    label: this.$tc('dewa-shop.properties.deliveryActive')
                },
                {
                    property: 'collectActive',
                    dataIndex: 'collectActive',
                    label: this.$tc('dewa-shop.properties.collectActive')
                },
                {
                    property: 'city',
                    dataIndex: 'city',
                    label: this.$tc('dewa-shop.properties.city')
                }
            ]
        },

        repository() {
            return this.repositoryFactory.create('dewa_shop');
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sorting => {
                criteria.addSorting(Criteria.sort(sorting, this.sortDirection, this.naturalSorting));
            });
            criteria.addAssociation('media');
            criteria.addAssociation('category');

            return criteria;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        getList() {
            this.isLoading = true;

            const context = {...Shopware.Context.api, inheritance: true};
            return this.repository.search(this.criteria, context).then((result) => {
                this.total = result.total;
                this.items = result;
                this.isLoading = false;
            });
        },

        onDelete(option) {
            this.$refs.listing.deleteItem(option);

            this.repository.search(this.criteria, {...Shopware.Context.api, inheritance: true}).then((result) => {
                this.total = result.total;
                this.items = result;
            });
        },

        changeLanguage() {
            this.getList();
        }
    }
});
