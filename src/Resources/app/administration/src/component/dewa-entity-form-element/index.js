import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('dewa-entity-form-element', {
    template,

    props: {
        column: {
            type: Object,
            required: true
        },
        value: {
            type: Object,
            required: true
        },
    }
});
