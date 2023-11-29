const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

import template from './index.html.twig';

Component.register('dewa-shop-detail', {
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
            processSuccess: false,
            mediaModalIsOpen: false,
            timezoneOptions: []
        };
    },

    computed: {
        ...mapPropertyErrors('item', [
            'name',
            'street',
            'zipcode',
            'city',
            'countryId',
            'email'
        ]),

        identifier() {
            return this.placeholder(this.item, 'name');
        },

        repository() {
            return this.repositoryFactory.create('dewa_shop');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        deliveryTypeOptions() {
            return [
                {
                    label: 'area',
                    value: 'area',
                },
                {
                    label: 'radius',
                    value: 'radius',
                }
            ]
        },

        shopCategoryOptions() {
            return [
                {label: 'german', value: 'german'},
                {label: 'italian', value: 'italian'},
                {label: 'indian', value: 'indian'},
                {label: 'greek', value: 'greek'},
                {label: 'mexican', value: 'mexican'},
                {label: 'spanish', value: 'spanish'},
                {label: 'american', value: 'american'},
                {label: 'chinese', value: 'chinese'},
                {label: 'kebab_snack_bar', value: 'kebab_snack_bar'},
                {label: 'snack_bar', value: 'snack_bar'},
                {label: 'fish_and_chips', value: 'fish_and_chips'},
                {label: 'bakery', value: 'bakery'},
                {label: 'catering', value: 'catering'},
                {label: 'cocktail', value: 'cocktail'},
                {label: 'candies', value: 'candies'}
            ]
        },

        criteria() {
            const criteria = new Criteria();

            criteria.addAssociation('media');
            criteria.addAssociation('category');
            criteria.addAssociation('deliverers.media');
            criteria.addAssociation('products');
            criteria.addAssociation('salesChannels');

            return criteria;
        },

        delivererFilterColumns() {
            return [
                'active',
                'name',
                'phoneNumber',
                'trackingCode',
                'locationLat',
                'locationLon'
            ];
        },

        printerCriteria() {
            const criteria = new Criteria();

            return criteria;
        },

        printerFilterColumns() {
            return [
                'active',
                'name',
                'server'
            ];
        },

        delivererCriteria() {
            const criteria = new Criteria();

            return criteria;
        },

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

        shopAreaFilterColumns() {
            return [
                'zipcode',
                'deliveryTime',
                'deliveryPrice',
                'minOrderValue',
                'shop.name'
            ];
        },

        shopAreaCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('shop');

            return criteria;
        },

        stockFilterColumns() {
            return [
                'product.name',
                'shop.name',
                'isStock',
                'stock',
                'info'
            ];
        },

        stockCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('product');
            criteria.addAssociation('shop');

            return criteria;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadTimezones();
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
                        message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        setMediaItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.mediaId = targetId;
                this.item.media = updatedMedia;
            });
        },
        onDropMedia(dragData) {
            this.setMediaItem({targetId: dragData.id});
        },
        setMediaFromSidebar(mediaEntity) {
            this.item.mediaId = mediaEntity.id;
        },
        onUnlinkMedia() {
            this.item.mediaId = null;
        },
        onCloseModal() {
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            this.item.mediaId = mediaEntity[0].id;
            this.item.media = mediaEntity[0];
        },
        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        },

        loadTimezones() {
            return Shopware.Service('timezoneService').loadTimezones()
                .then((result) => {
                    this.timezoneOptions.push({
                        label: 'UTC',
                        value: 'UTC',
                    });

                    const loadedTimezoneOptions = result.map(timezone => ({
                        label: timezone,
                        value: timezone,
                    }));

                    this.timezoneOptions.push(...loadedTimezoneOptions);
                });
        },
    }
});
