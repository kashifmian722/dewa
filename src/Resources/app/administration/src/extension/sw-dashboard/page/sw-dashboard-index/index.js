import template from './index.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.override('sw-dashboard-index', {
    template,

    data() {
        return {
            dewaShops: []
        };
    },

    computed: {
        dewaShopRepository() {
            return this.repositoryFactory.create('dewa_shop');
        }
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            this.getDewaShops();
        },

        async getDewaShops() {
            const criteria = new Criteria(1, 25);
            const items = await this.dewaShopRepository.search(criteria, Shopware.Context.api);

            this.dewaShops = items;
        }
    }
});
