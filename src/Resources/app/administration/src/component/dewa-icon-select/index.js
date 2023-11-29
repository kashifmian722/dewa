import template from './index.html.twig';
import './index.scss';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('dewa-icon-select', {
    template,

    inject: ['repositoryFactory'],

    props: {
        value: {
            type: String,
            required: true
        },
        iconColor: {
            type: String,
            required: false,
            default: '#555555'
        },
        backgroundColor: {
            type: String,
            required: false,
            default: 'transparent'
        },
    },

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    data() {
        return {
            isLoading: false,
            showModal: false,
            item: null,
            items: null,
            total: null,
            selection: {}
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('dewa_svg_icon');
        },

        style() {
            return {
                backgroundColor: this.backgroundColor,
                color: this.iconColor,
                fill: this.iconColor,
            };
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        onClickUpload() {
            this.$refs.fileInput.click();
        },

        readFileAsync(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => {
                    resolve(reader.result);
                };
                reader.onerror = reject;
                reader.readAsText(file);
            })
        },

        async onFileInputChange() {
            const files = this.$refs.fileInput.files;
            this.isLoading = true;

            for (const file of files) {
                const icon = this.repository.create(Shopware.Context.api);
                icon.content = await this.readFileAsync(file);
                await this.repository.save(icon, Shopware.Context.api);
            }

            this.$refs.fileForm.reset();
            this.getList();
        },

        selectItem(itemId) {
            if (typeof this.selection[itemId] === 'undefined') {
                this.selection[itemId] = itemId;
            } else {
                delete this.selection[itemId];
            }
        },

        isSelected(itemId) {
            return typeof this.selection[itemId] !== 'undefined';
        },

        onDeleteSelectedItems() {
            this.isLoading = true;
            const promises = [];

            Object.keys(this.selection).forEach((id) => {
                promises.push(this.repository.delete(id, Shopware.Context.api));
            });

            this.selection = {};
            this.$forceUpdate();

            Promise.all(promises).then(() => {
                this.getList();
            });
        },

        getList() {
            this.isLoading = true;

            const criteria = new Criteria(1, 500);

            return this.repository.search(criteria, Shopware.Context.api).then((result) => {
                this.total = result.total;
                this.items = result;
                this.item = result.get(this.value);
                this.isLoading = false;
            });
        },

        onSelectIcon(icon) {
            this.item = icon;
            this.value = icon.id;
            this.showModal = false;
        },

        onOpenModal() {
            this.showModal = true;
        },

        onCloseModal() {
            this.showModal = false;
        }
    }
});
