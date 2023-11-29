const {Component} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.override('sw-order-detail-base', {
    template,

    computed: {
        shopOrderFilterColumns() {
            return [
                'order.orderNumber',
                'shop.name',
                'deliverer.name',
                'distance'
            ];
        },

        shopOrderCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('order');
            criteria.addAssociation('shop');
            criteria.addAssociation('deliverer');

            return criteria;
        },
    }
});