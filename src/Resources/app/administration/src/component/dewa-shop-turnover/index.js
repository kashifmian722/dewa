import template from './index.html.twig';
import './index.scss'

const {Criteria} = Shopware.Data;
const {Component, Mixin, Context} = Shopware;

Component.register('dewa-shop-turnover', {
    template,

    props: [
        'shopId'
    ],

    inject: [
        'repositoryFactory',
        'dewaShopApiService'
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification')
    ],

    data() {
        return {
            items: [],
            sortBy: 'key',
            sortDirection: 'DESC',
            page: 1,
            limit: 25,
            total: 10,
            isLoading: true,
            defaultFilter: 'day',
            interval: 'day'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'key',
                    dataIndex: 'key',
                    label: 'dewa-shop.properties.period',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    width: '180px'
                },
                {
                    property: 'count',
                    dataIndex: 'count',
                    label: 'dewa-shop.properties.count',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    align: 'center',
                    width: '120px'
                },
                {
                    property: 'amountTotal.sum',
                    dataIndex: 'amountTotal.sum',
                    label: 'dewa-shop.properties.amountTotal',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    align: 'center',
                    width: '120px'
                },
                {
                    property: 'amountNet.sum',
                    dataIndex: 'amountNet.sum',
                    label: 'dewa-shop.properties.amountNet',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    align: 'center',
                    width: '120px'
                },
                {
                    property: 'tax',
                    dataIndex: 'tax',
                    label: 'dewa-shop.properties.tax',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    align: 'center',
                    width: '120px'
                },
                {
                    property: 'shippingTotal.sum',
                    dataIndex: 'shippingTotal.sum',
                    label: 'dewa-shop.properties.shippingTotal',
                    rawData: true,
                    sortable: true,
                    allowResize: true,
                    align: 'center',
                    width: '120px'
                }
            ];
        },

        timeZone() {
            return Shopware.State.get('session').currentUser?.timeZone ?? 'UTC';
        },

        shopOrderRepository() {
            return this.repositoryFactory.create('dewa_shop_order');
        },

        shopOrderCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addAssociation('order.currency');
            criteria.addAssociation('order.transactions');

            criteria.addFilter(Criteria.equals('order.stateMachineState.technicalName', 'completed'));
            //criteria.addFilter(Criteria.equals('order.stateMachineState.technicalName', 'open'));
            criteria.addFilter(Criteria.equals('shopId', this.shopId));

            return criteria;
        },
    },

    watch: {
        shopOrderCriteria: {
            handler() {
                this.getList();
            },
            deep: true,
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        async getList() {
            this.isLoading = true;

            const criteria = this.shopOrderCriteria;

            criteria.addAggregation(
                Criteria.histogram(
                    'order_amount_total',
                    'order.orderDateTime',
                    this.interval,
                    null,
                    Criteria.sum('amountTotal', 'order.amountTotal'),
                    this.timeZone
                )
            );

            criteria.addAggregation(
                Criteria.histogram(
                    'order_amount_net',
                    'order.orderDateTime',
                    this.interval,
                    null,
                    Criteria.sum('amountNet', 'order.amountNet'),
                    this.timeZone
                )
            );

            criteria.addAggregation(
                Criteria.histogram(
                    'order_shipping_total',
                    'order.orderDateTime',
                    this.interval,
                    null,
                    Criteria.sum('shippingTotal', 'order.shippingTotal'),
                    this.timeZone
                )
            );

            return this.shopOrderRepository.search(criteria, Context.api).then((response) => {
                const ra = response.aggregations;

                if (ra) {
                    const offset = (this.page - 1) * this.limit;
                    const buckets = [];

                    for (let i = 0; i < ra.order_amount_total.buckets.length; i++) {
                        buckets.push({
                            key: ra.order_amount_total.buckets[i].key,
                            count: ra.order_amount_total.buckets[i].count,
                            amountTotal: {
                                sum: ra.order_amount_total.buckets[i].amountTotal.sum
                            },
                            amountNet: {
                                sum: ra.order_amount_net.buckets[i].amountNet.sum
                            },
                            shippingTotal: {
                                sum: ra.order_shipping_total.buckets[i].shippingTotal.sum
                            },
                        });
                    }

                    if (this.sortBy) {
                        buckets.sort((a, b) => a[this.sortBy].localeCompare(b[this.sortBy]));
                    }

                    this.items = buckets.slice(offset).slice(0, this.limit).map(data => {
                        data.tax = this.$options.filters.currency(data.amountTotal.sum - data.amountNet.sum, 'EUR', 2);
                        data.amountTotal.sum = this.$options.filters.currency(data.amountTotal.sum, 'EUR', 2);
                        data.amountNet.sum = this.$options.filters.currency(data.amountNet.sum, 'EUR', 2);
                        data.shippingTotal.sum = this.$options.filters.currency(data.shippingTotal.sum, 'EUR', 2);

                        return data;
                    });

                    this.total = buckets.length;
                }

                this.isLoading = false;
            });
        },

        onPrintTurnover(key) {
            this.isLoading = true;

            this.dewaShopApiService.post(
                `/dewa/print-turnover/${this.shopId}/${this.interval}`,
                {
                    shopId: this.shopId,
                    interval: this.interval,
                    key: key
                }
            ).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('dewa-shop.notification.printJobSent')
                });

                this.isLoading = false;
            });
        },

        onColumnSort(column) {
            let direction = 'ASC';
            if (this.sortBy === column.dataIndex) {
                if (this.sortDirection === direction) {
                    direction = 'DESC';
                }
            }
            this.sortBy = column.dataIndex;
            this.sortDirection = direction;
            return this.getList();
        },

        paginate({page = 1, limit = 25}) {
            this.page = page;
            this.limit = limit;
            return this.getList();
        },

        onChangeTab(item) {
            this.interval = item.name;
            this.sortBy = null;
            this.sortDirection = 'DESC';
            this.page = 1;
            this.limit = 25;
            return this.getList();
        },
    }
});
