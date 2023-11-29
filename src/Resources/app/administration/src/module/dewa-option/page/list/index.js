const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('dewa-option-list', {
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
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('dewa-shop.properties.name'),
                    routerLink: 'dewa.option.detail',
                    primary: true
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$tc('dewa-shop.properties.type')
                }
            ]
        },

        repository() {
            return this.repositoryFactory.create('dewa_option');
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sorting => {
                criteria.addSorting(Criteria.sort(sorting, this.sortDirection, this.naturalSorting));
            });

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
