const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('dewa-dashboard-detail', {
    template,

    inject: [
        'repositoryFactory',
        'systemConfigApiService',
        'acl',
        'orderStateMachineService',
        'dewaShopApiService'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            config: [],
            isLoading: false,
            item: null,
            shopOrderItem: null,
            shopOrderItemId: null,
            orderStates: [],
            showEditModal: false,
            showConfirmModal: false,
            showDelivererModal: false,
            showPrintModal: false,
            showQuickOrderModal: false,
            showMap: false,
            orderStateChangeItem: null,
            orderStateChangeOption: null,
            refreshTimer: null,
            currentOrders: 9999,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        isModalOpen() {
            return this.showEditModal || this.showConfirmModal || this.showDelivererModal || this.showPrintModal;
        },

        isExpanded() {
            return Shopware.State.get('adminMenu').isExpanded;
        },

        identifier() {
            return this.placeholder(this.item, 'name');
        },

        repository() {
            return this.repositoryFactory.create('dewa_shop');
        },

        shopOrderRepository() {
            return this.repositoryFactory.create('dewa_shop_order');
        },

        stateMachineStateRepository() {
            return this.repositoryFactory.create('state_machine_state');
        },

        criteria() {
            const criteria = new Criteria();

            criteria.addAssociation('deliverers');
            criteria.addAssociation('printers');
            criteria.addAssociation('shopOrders.deliverer');
            criteria.addAssociation('shopOrders.order.lineItems');
            criteria.addAssociation('shopOrders.order.billingAddress');
            criteria.addAssociation('shopOrders.order.currency');
            criteria.addAssociation('shopOrders.order.deliveries.shippingMethod');
            criteria.addAssociation('shopOrders.order.deliveries.shippingOrderAddress');
            criteria.addAssociation('shopOrders.order.transactions.paymentMethod');
            criteria.addAssociation('shopOrders.order.transactions.stateMachineState');
            criteria.addAssociation('shopOrders.order.stateMachineState');

            const shopOrdersCriteria = criteria.getAssociation('shopOrders');
            shopOrdersCriteria.addFilter(Criteria.equals('order.versionId', Shopware.Context.api.liveVersionId));

            /* TODO: Sort not working because order.deliveries.shippingMethodId filter... */
            // https://github.com/shopware/platform/issues/2702
            //shopOrdersCriteria.addSorting(Criteria.sort('desiredTime', 'ASC'));

            shopOrdersCriteria.addFilter(Criteria.equalsAny(
                'order.stateMachineState.technicalName',
                [
                    'open',
                    'in_progress'
                ]
            ));

            shopOrdersCriteria.addFilter(Criteria.equalsAny(
                'order.deliveries.shippingMethodId',
                [
                    DewaShop.shippingMethodCollectId,
                    DewaShop.shippingMethodDeliveryId
                ]
            ));

            const orderWhitelistPaymentMethods = this.config['AppflixDewaShop.config.orderWhitelistPaymentMethods'];

            if (orderWhitelistPaymentMethods && orderWhitelistPaymentMethods.length > 0) {
                /*shopOrdersCriteria.addFilter(Criteria.not('OR', [
                    Criteria.multi('AND', [
                        Criteria.equals('order.transactions.stateMachineState.technicalName', 'open'),
                        Criteria.equalsAny('order.transactions.paymentMethodId', orderWhitelistPaymentMethods)
                    ])
                ]));*/
                shopOrdersCriteria.addFilter(Criteria.multi('OR', [
                    Criteria.equals('order.transactions.stateMachineState.technicalName', 'paid'),
                    Criteria.equalsAny('order.transactions.paymentMethodId', orderWhitelistPaymentMethods)
                ]));
            }

            /*const shopOrdersOrderCriteria = criteria.getAssociation('shopOrders.order');
            shopOrdersOrderCriteria.addFilter(Criteria.not('OR', [
                Criteria.multi('AND', [
                    Criteria.equals('transactions.stateMachineState.technicalName', 'open'),
                    Criteria.equals('transactions.paymentMethod.afterOrderEnabled', 0),
                ])
            ]));*/

            return criteria;
        },

        orderStateCriteria() {
            const criteria = new Criteria();

            criteria.setLimit(100);
            criteria.addAssociation('stateMachine');

            return criteria;
        },

        shopOrdersOpen() {
            return this.item.shopOrders.filter(item => {
                return (item.stateType === 'open');
            });
        },

        shopOrdersInProgress() {
            return this.item.shopOrders.filter(item => {
                return (item.stateType === 'in_progress');
            });
        },

        shopOrdersCollect() {
            return this.item.shopOrders.filter(item => {
                return (item.stateType === 'shipped' && item.shippingMethod === 'collect');
            });
        },

        shopOrdersDelivery() {
            return this.item.shopOrders.filter(item => {
                return (item.stateType === 'shipped' && item.shippingMethod === 'delivery');
            });
        },
    },

    created() {
        this.createdComponent();
    },

    beforeDestroy() {
        clearInterval(this.refreshTimer);
    },

    methods: {
        setRefreshTimer() {
            this.refreshTimer = setInterval(() => {
                if (!this.isModalOpen) {
                    this.updateShop();
                    this.getItem();
                }
            }, 5000);
        },

        /**
         * Open shop if logged in admin or POS device is online
         */
        updateShop() {
            this.dewaShopApiService.get(`/dewa/update-shop/${this.$route.params.id}`);
        },

        async createdComponent() {
            this.config = await this.systemConfigApiService.getValues('AppflixDewaShop.config');

            await this.getOrderStates();
        },

        async getOrderStates() {
            this.isLoading = true;

            this.stateMachineStateRepository
                .search(this.orderStateCriteria, Shopware.Context.api)
                .then((entities) => {
                    this.orderStates = entities;
                    this.isLoading = false;

                    this.getItem();
                    this.setRefreshTimer();
                });
        },

        getItem() {
            this.isLoading = true;

            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.criteria)
                .then((entity) => {
                    this.item = entity;
                    this.isLoading = false;
                    this.orderStateChangeItem = null;
                    this.orderStateChangeOption = null;

                    this.enrichShopOrders();
                    this.watchShopOrders();
                });
        },

        editItem() {
            this.showEditModal = true;
        },

        quickOrder() {
            this.showQuickOrderModal = true;
        },

        onSaveItem() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: this.$tc('global.default.error'),
                    });
                });
        },

        onCloseModal() {
            this.showEditModal = false;
            this.showConfirmModal = false;
            this.showDelivererModal = false;
            this.showPrintModal = false;
            this.showQuickOrderModal = false;
        },

        watchShopOrders() {
            if (this.item.shopOrders.length > this.currentOrders) {
                this.notifyMe(this.$tc('dewa-shop.notification.orderPlaced'));

                const audio = new Audio("/bundles/appflixdewashop/static/audio/Bike-Horn-SoundBible.com-602544869.mp3");
                audio.play().then(r => {
                    console.log(r);
                });
            }

            this.currentOrders = this.item.shopOrders.length;
        },

        notifyMe(message) {
            if (!("Notification" in window)) {
                alert("This browser does not support desktop notification");
            } else if (Notification.permission === "granted") {
                const notification = new Notification(message);
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then((permission) => {
                    if (permission === "granted") {
                        const notification = new Notification(message);

                    }
                });
            }
        },

        enrichShopOrders() {
            const that = this;

            this.item.shopOrders.forEach(shopOrder => {
                const states = that.shopOrderStates(shopOrder.order);
                let options = [];

                shopOrder.states = states;
                shopOrder.shippingMethod = states.isDelivery ? 'delivery' : 'collect';
                shopOrder.stateType = states.deliveryState !== 'shipped' ? states.orderState : states.deliveryState;
                shopOrder.className = shopOrder.shippingMethod + '-' + shopOrder.stateType;

                if (states.orderState === 'open') {
                    options.push('confirm');
                    if (states.transactionState !== 'paid') {
                        options.push('cancel');
                    }
                }

                if (states.orderState === 'in_progress') {
                    if (states.deliveryState === 'shipped') {
                        if (states.transactionState === 'paid') {
                            options.push('done');
                        } else {
                            options.push('donePaid');
                        }
                    } else {
                        if (states.isDelivery) {
                            options.push('delivery');
                        } else {
                            options.push('collect');
                        }
                    }
                }

                shopOrder.options = options;
            });
        },

        shopOrderStates(order) {
            const delivery = order.deliveries.last();
            const transaction = order.transactions.last();
            const orderState = this.orderStates.get(order.stateId);
            const transactionState = this.orderStates.get(transaction.stateId);
            const deliveryState = this.orderStates.get(delivery.stateId);
            const isCollect = (delivery.shippingMethodId === DewaShop.shippingMethodCollectId);

            return {
                orderState: orderState.technicalName,
                transactionState: transactionState.technicalName,
                deliveryState: deliveryState.technicalName,
                isCollect: isCollect,
                isDelivery: !isCollect,
            }
        },

        orderStateChange(shopOrder, option) {
            this.orderStateChangeItem = shopOrder.order;
            this.shopOrderItem = shopOrder;
            this.orderStateChangeOption = option;

            if (option === 'cancel') {
                this.showConfirmModal = true;
            } else if (option === 'confirm') {
                this.showPrintModal = true;
            } else if (option === 'delivery') {
                this.showDelivererModal = true;
            } else {
                this.onChangeState();
            }
        },

        onSelectDeliverer(delivererId) {
            this.isLoading = true;

            this.shopOrderItem.delivererId = delivererId

            this.shopOrderRepository
                .save(this.shopOrderItem, Shopware.Context.api)
                .then(() => {
                    this.onChangeState();
                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: this.$tc('global.default.error'),
                    });
                });
        },

        onSelectPrinter(printerId) {
            this.isLoading = true;

            this.dewaShopApiService.get(`/dewa/print-job/${printerId}/${this.shopOrderItem.id}`).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('dewa-shop.notification.printJobSent')
                });

                this.onChangeState();
                this.isLoading = false;
            });
        },

        onSetTime(minutes) {
            this.dewaShopApiService.get(`/dewa/order-time/${this.shopOrderItem.id}/${minutes}`);

            this.createNotificationSuccess({
                message: this.$tc('dewa-shop.notification.newTimeSet') + ` ${minutes}m`
            });
        },

        onChangeState() {
            this.onCloseModal();

            const delivery = this.orderStateChangeItem?.deliveries?.last();
            const transaction = this.orderStateChangeItem?.transactions?.last();

            if (this.orderStateChangeOption === 'confirm') {
                this.orderStateMachineService.transitionOrderState(this.orderStateChangeItem.id, 'process').then(() => {
                    this.getItem();
                }).catch((error) => {
                    this.createStateChangeErrorNotification(error);
                });
            } else if (this.orderStateChangeOption === 'cancel') {
                this.orderStateMachineService.transitionOrderState(this.orderStateChangeItem.id, 'cancel').then(() => {
                    this.getItem();
                }).catch((error) => {
                    this.createStateChangeErrorNotification(error);
                });
            } else if (this.orderStateChangeOption === 'delivery' || this.orderStateChangeOption === 'collect') {
                this.orderStateMachineService.transitionOrderDeliveryState(delivery.id, 'ship').then(() => {
                    this.getItem();
                }).catch((error) => {
                    this.createStateChangeErrorNotification(error);
                });
            } else if (this.orderStateChangeOption === 'done') {
                this.orderStateMachineService.transitionOrderState(this.orderStateChangeItem.id, 'complete').then(() => {
                    this.getItem();
                }).catch((error) => {
                    this.createStateChangeErrorNotification(error);
                });
            } else if (this.orderStateChangeOption === 'donePaid') {
                this.orderStateMachineService.transitionOrderTransactionState(transaction.id, 'paid').then(() => {
                    this.orderStateMachineService.transitionOrderState(this.orderStateChangeItem.id, 'complete').then(() => {
                        this.getItem();
                    }).catch((error) => {
                        this.createStateChangeErrorNotification(error);
                    });
                }).catch((error) => {
                    this.createStateChangeErrorNotification(error);
                });
            }
        },

        createStateChangeErrorNotification(errorMessage) {
            this.createNotificationError({
                message: this.$tc('sw-order.stateCard.labelErrorStateChange') + errorMessage
            });
        },
    }
});
