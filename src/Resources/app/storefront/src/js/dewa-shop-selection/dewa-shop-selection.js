import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import DomAccess from 'src/helper/dom-access.helper';

export default class DewaShopSelection extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._form = this.el;
        this._optionsElement = this.el.querySelector('#shopSelectionOptions')

        this._registerEvents();
    }

    _registerEvents() {
        this._form.addEventListener('submit', this._formSubmit.bind(this));
    }

    _formSubmit(event) {
        if (typeof event != 'undefined') {
            event.preventDefault();
        }

        console.log(event);

        const requestUrl = DomAccess.getAttribute(this._form, 'action').toLowerCase();
        //const formData = FormSerializeUtil.serialize(this._form);
        const formData = new FormData(this._form);

        if (!formData.get("action")) {
            try {
                formData.set('action', event.submitter.value);
            } catch (err) {
                console.log(err);
            }
        }

        this._client.post(requestUrl, formData, this._onLoaded.bind(this))
    }

    _onLoaded(response) {
        console.log(response);


        response = JSON.parse(response);

        if (response.reload) {
            window.location.reload();
        }

        if (response.options) {
            this._optionsElement.innerHTML = response.options;

            window.PluginManager.initializePlugins();
        }
    }
}
