const DewaShop = function DewaShop() {
    this.shippingMethodCollectId = '78d2aa20af38597657a4b48cf02f7dcb';
    this.shippingMethodDeliveryId = '38aeeba4f2a1432c6b45fd0b45ca0c61';
    this.OSM = {
        urlTemplate: '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
    }
};

DewaShop.prototype = {};

const DewaShopInstance = new DewaShop();

window.DewaShop = DewaShopInstance;
exports.default = DewaShopInstance;
module.exports = exports.default;
