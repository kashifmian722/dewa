const PluginManager = window.PluginManager;

import DewaComponentsCustomJs from './js/components/custom.js';
import DewaMenuBuyForm from './js/dewa-menu-buy-form/dewa-menu-buy-form';
import DewaMenuPage from './js/dewa-menu-page/dewa-menu-page';
import DewaMenuNavbar from './js/dewa-menu-navbar/dewa-menu-navbar';
import DewaModal from './js/dewa-modal/dewa-modal';
import DewaShopSelection from './js/dewa-shop-selection/dewa-shop-selection';
import DewaSearch from './js/dewa-search/dewa-search.js';
import DewaAddToCart from './js/dewa-add-to-cart/dewa-add-to-cart';
import DewaCheckoutFinish from './js/dewa-checkout-finish/dewa-checkout-finish';
import DewaCheckoutShopModal from './js/dewa-checkout-shop-modal/dewa-checkout-shop-modal';
import DewaContentMinHeight from './js/dewa-content-min-height/dewa-content-min-height';
import DewaAccountLogin from './js/dewa-account-login/dewa-account-login';
import DewaStickyItems from './js/dewa-sticky-items/dewa-sticky-items';


PluginManager.override('SearchWidget', DewaSearch, '[data-search-form]');
PluginManager.register('DewaComponentsCustomJs', DewaComponentsCustomJs);
PluginManager.register('DewaMenuBuyForm', DewaMenuBuyForm,'[data-dewa-menu-buy-form]');
PluginManager.register('DewaMenuPage', DewaMenuPage,'[data-dewa-menu-page]');
PluginManager.register('DewaMenuNavbar', DewaMenuNavbar, '.dewa-menu-navigation-slider');
PluginManager.register('DewaModal', DewaModal);
PluginManager.register('DewaShopSelection', DewaShopSelection, '[data-dewa-shop-selection]');
PluginManager.register('DewaCheckoutFinish', DewaCheckoutFinish,'[data-dewa-checkout-finish]');
PluginManager.register('DewaCheckoutShopModal', DewaCheckoutShopModal,'[data-dewa-checkout-shop]');
PluginManager.register('DewaContentMinHeight', DewaContentMinHeight);
PluginManager.register('DewaAccountLogin', DewaAccountLogin, '[data-dewa-account-login]');
PluginManager.register('DewaStickyItems', DewaStickyItems, '[data-dewa-sticky-item]');

if (window.deactivateOffcanvasCart) {
    PluginManager.override('AddToCart', DewaAddToCart, '[data-add-to-cart]');
}

if (module.hot) {
    module.hot.accept();
}
