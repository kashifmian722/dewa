import template from './index.html.twig';

const {Component, Mixin} = Shopware;

Component.override('sw-category-view', {
    template
});
