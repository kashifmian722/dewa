const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('dewa-dashboard-orders', {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'orderStateMachineService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: [
        'value',
        'items',
        'title'
    ],

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    methods: {
        focusShopOrder(shopOrder) {
            this.value = shopOrder.id;
        },

        formatPayload(payload) {
            if (typeof payload == 'undefined') {
                return;
            }

            return payload.map(function(el) {
                return el.name + ': ' + (typeof el.value == 'string' ? el.value : el.value.join(", "));
            }).join(" | ");
        },

        deadline(desiredTime) {
            const then = new Date(desiredTime);
            const now = new Date();

            if (now > then) {
                return 'deadline';
            }
        }
    }
});
