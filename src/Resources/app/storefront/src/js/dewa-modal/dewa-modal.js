import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class DewaModal extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        jQuery('body').on('click', '[data-dewa-modal]', function () {
            let url = this.dataset.dewaModal;
            let target = this.dataset.target ? this.dataset.target : "#dewaModal";

            that._client.get(url, (response) => {
                that._openModal(response, target);
            });
        });

        window.dewaModal = function (url, target) {
            that._client.get(url, (response) => {
                that._openModal(response, target);
            });
        }
    }

    _openModal(response, target) {
        jQuery(target).html(response).modal({}).modal('show');

        window.PluginManager.initializePlugins();

        if (typeof callback == 'function') {
            callback();
        }
    }
}
