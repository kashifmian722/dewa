const { Component } = Shopware;

import template from './index.html.twig';

Component.override('sw-product-detail', {
    template,

    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('ingredients');
            criteria.addAssociation('badges');
            criteria.addAssociation('shops');
            return criteria;
        },
    }
});
