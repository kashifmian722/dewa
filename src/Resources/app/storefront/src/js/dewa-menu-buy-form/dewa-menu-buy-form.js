import Plugin from 'src/plugin-system/plugin.class';

export default class DewaMenuBuyForm extends Plugin {
    init() {
        this._form = this.el;
        this._lang = document.documentElement.lang;

        this._registerEvents();
        this._productConfigurator();
    }

    _registerEvents() {
        this._form.addEventListener('change', event => this._productConfigurator(event));
        this._form.addEventListener('submit', this._formSubmit.bind(this));
    }

    _productConfigurator() {
        const basePrice = parseFloat(this._form.dataset.basePrice);
        const pricePrecision = this._form.dataset.pricePrecision ? this._form.dataset.pricePrecision : 0.25;
        const currencyIso = this._form.dataset.currency;
        const optionElements = this._form.querySelectorAll('[data-option]');
        const calculatedPriceElements = this._form.querySelectorAll('[data-calculated-price]');
        const unitPriceElements = this._form.querySelectorAll('[data-unit-price]');
        const currency = new Intl.NumberFormat(this._lang, {
            style: 'currency',
            currency: currencyIso,
        });
        const round = (number) => {
            return Math.round(number / pricePrecision) * pricePrecision;
        };
        let calculatedPrice = basePrice;
        let priceFactor = 1;

        let unitName = this._form.dataset.unitName;
        let referenceUnit = parseFloat(this._form.dataset.referenceUnit);
        let purchaseUnit = parseFloat(this._form.dataset.purchaseUnit);
        let quantity = null;

        optionElements.forEach((optionElement) => {
            const optionElementSingle = optionElement.querySelectorAll('option');

            if (optionElementSingle.length > 0) {
                optionElementSingle.forEach((optionItem) => {
                    optionItem.innerText = optionItem.dataset.name + ': ' + currency.format(round(optionItem.dataset.priceFactor * basePrice));

                    if (optionItem.selected) {
                        purchaseUnit = optionItem.dataset.purchaseUnit;
                        quantity = optionItem.dataset.quantity;
                        priceFactor = priceFactor * optionItem.dataset.priceFactor;
                        calculatedPrice = round(basePrice * priceFactor);
                    }

                    unitName = optionItem.parentElement.dataset.unitName;
                    referenceUnit = optionItem.parentElement.dataset.referenceUnit;
                });
            }
        });

        optionElements.forEach((optionElement) => {
            const optionElementMulti = optionElement.querySelectorAll('input[type="checkbox"],input[type="radio"]');

            if (optionElementMulti.length > 0) {
                optionElementMulti.forEach((optionItem) => {
                    optionItem.nextElementSibling.innerText = optionItem.dataset.name + ': ' + currency.format(round(optionItem.dataset.price * priceFactor));

                    if (optionItem.checked) {
                        calculatedPrice = calculatedPrice + round(optionItem.dataset.price * priceFactor);
                    }
                });
            }
        });

        /* Deposit Option */
        optionElements.forEach((optionElement) => {
            if (optionElement.type === "hidden" && optionElement.dataset.depositPrice) {
                calculatedPrice = calculatedPrice + parseFloat(optionElement.dataset.depositPrice);
            }
        });

        calculatedPriceElements.forEach((element) => {
            element.innerText = currency.format(calculatedPrice);
        });

        if (unitName && referenceUnit && purchaseUnit) {
            let pricePerUnit = calculatedPrice / purchaseUnit * referenceUnit;
            let pricePerUnitText = `${currency.format(pricePerUnit)}/${referenceUnit}${unitName}`;

            unitPriceElements.forEach((element) => {
                element.innerText = pricePerUnitText;
            });
        }
    }

    _formSubmit(event) {
        $('#dewaModal').modal('hide');
    }
}
