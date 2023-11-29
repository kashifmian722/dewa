const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

import template from './index.html.twig';

Component.register('dewa-badge-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', [
            'name',
            'description'
        ]),

        identifier() {
            return this.placeholder(this.item, 'name');
        },

        repository() {
            return this.repositoryFactory.create('dewa_badge');
        },

        criteria() {
            const criteria = new Criteria();
            criteria.addAssociation('products');
            return criteria;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getItem();
        },

        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.criteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onChangeLanguage() {
            this.getItem();
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid')
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
