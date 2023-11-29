import template from './index.html.twig';
import './index.scss';

const {Component} = Shopware;

Component.register('dewa-weekday-config', {
    template,

    props: ['value'],

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    created() {
        if (!this.value) {
            this.value = [
                {day: 'monday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'tuesday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'wednesday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'thursday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'friday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'saturday', discountType: 'percentage', discountValue: 0, active: true},
                {day: 'sunday', discountType: 'percentage', discountValue: 0, active: true}
            ];
        }
    }
});
