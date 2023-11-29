const {Component} = Shopware;

import template from '../detail/index.html.twig';

Component.extend('dewa-badge-create', 'dewa-badge-detail', {
    template,

    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.isLoading = false;
        },

        onClickSave() {
            this.isLoading = true;
            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'dewa.badge.detail', params: {id: this.item.id}});
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid')
                    });
                });
        }
    }
});
