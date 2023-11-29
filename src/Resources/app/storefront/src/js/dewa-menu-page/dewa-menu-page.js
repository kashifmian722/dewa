import Plugin from 'src/plugin-system/plugin.class';

export default class DewaMenuPage extends Plugin {

    init() {
        this._badgeOptions = this.el.querySelectorAll('.dewa-product-badges-container input');
        this._badgeProducts = this.el.querySelectorAll('[data-dewa-product-badges]');
        this._dropdownFilter = this.el.querySelector('[data-dewa-filter-panel-item-dropdown]');

        this._registerEvents();
        this._badgeFilter();
        this._preventDropdownClose();
    }

    _registerEvents() {
        this._badgeOptions.forEach((optionElement) => {
            optionElement.addEventListener('change', event => this._badgeFilter());
        });
    }

    _badgeFilter() {
        let selected = [];

        this._badgeOptions.forEach((optionElement) => {
            if (optionElement.checked) {
                selected.push(optionElement.value)
            }
        });

        this._badgeProducts.forEach((productElement) => {
            if (selected.length === 0) {
                productElement.classList.remove('d-none');
            } else {
                let productBadges = productElement.dataset.dewaProductBadges;
                let displayProduct = false;

                for (let badgeId of selected) {
                    if (productBadges.indexOf(badgeId) !== -1) {
                        displayProduct = true;
                    }
                }

                if (displayProduct) {
                    productElement.classList.remove('d-none');
                } else {
                    productElement.classList.add('d-none');
                }
            }
        });
    }

    _preventDropdownClose() {
        const dropdownMenu = this._dropdownFilter;

        if (!dropdownMenu) {
            return;
        }

        dropdownMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    }
}
