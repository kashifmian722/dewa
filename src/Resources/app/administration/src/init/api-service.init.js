import DewaShopApiService from '../core/service/api/dewa-shop-api.service';

const {Application} = Shopware;

Application.addServiceProvider('dewaShopApiService', (container) => {
    const initContainer = Application.getContainer('init');
    return new DewaShopApiService(initContainer.httpClient, container.loginService);
});
