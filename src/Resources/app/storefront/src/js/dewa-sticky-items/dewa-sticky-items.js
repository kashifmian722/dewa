import Plugin from 'src/plugin-system/plugin.class';
import Debouncer from 'src/helper/debouncer.helper';

export default class DewaStickyItems extends Plugin {

    static options = {
        buttonSelectorPhone: '.js-dewa-sticky-contact',
        buttonSelectorCart: '.js-dewa-sticky-cart',
        buttonSelectorAccount: '.js-dewa-sticky-account',
        visiblePos: 250,
        visibleCls: 'is-visible'
    };

    init() {
        this._buttonPhone = document.querySelector(this.options.buttonSelectorPhone);
        this._buttonCart = document.querySelector(this.options.buttonSelectorCart);
        this._buttonAccount = document.querySelector(this.options.buttonSelectorAccount);
        this._assignDebouncedOnScrollEvent();
        this._registerEvents();
    }

    /**
     * registers all needed events
     *
     * @private
     */
    _registerEvents() {
        document.addEventListener('scroll', this._debouncedOnScroll, false);
    }

    _assignDebouncedOnScrollEvent() {
        this._debouncedOnScroll = Debouncer.debounce(this._toggleVisibility.bind(this), this.options.scrollDebounceTime);
    }

    /**
     * toggle visibility scroll-up button
     *
     * @private
     */
    _toggleVisibility() {
        if (window.scrollY > this.options.visiblePos) {
            this._buttonPhone.classList.add(this.options.visibleCls);
            this._buttonCart.classList.add(this.options.visibleCls);
            this._buttonAccount.classList.add(this.options.visibleCls);
        } else {
            this._buttonPhone.classList.remove(this.options.visibleCls);
            this._buttonCart.classList.remove(this.options.visibleCls);
            this._buttonAccount.classList.remove(this.options.visibleCls);
        }

        this.$emitter.publish('toggleVisibility');
    }
}
