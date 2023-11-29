import AddToCartPlugin from 'src/plugin/add-to-cart/add-to-cart.plugin';
import HttpClient from 'src/service/http-client.service';

let xhr = null;
const cartWidget = window.PluginManager.getPluginInstances('CartWidget');


export default class DewaAddToCart extends AddToCartPlugin {
    _openOffCanvasCart(instance, requestUrl, formData) {
        const client = new HttpClient();
        // interrupt already running ajax calls
        if (xhr) xhr.abort();

        if (formData) {
            xhr = client.post(requestUrl, formData, this._refreshCartBadge());
        } else {
            xhr = client.get(requestUrl, this._refreshCartBadge());
        }
    }

    _refreshCartBadge(){

        setTimeout(function(){
            cartWidget[0].fetch();
        }, 500);
    }
}
