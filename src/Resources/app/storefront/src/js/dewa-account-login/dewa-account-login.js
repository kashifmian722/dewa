import Plugin from 'src/plugin-system/plugin.class';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service';

export default class DewaAccountLogin extends Plugin {

    static options = {};

    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._form = document.getElementById('accountLoginCheckMailForm');
        this._messageEl = document.getElementById('accountLoginMessage');
        this._headlineEl = document.getElementById('accountLoginHeadline');
        this._emailInputs = this.el.querySelectorAll("[type=email]");
        this._links = this.el.querySelectorAll("a");

        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        this._form.addEventListener('submit', this._formSubmit.bind(this));

        this._links.forEach(item => {
            item.addEventListener('click', event => {
                if (item.href.includes('/account/recover')) {
                    event.preventDefault();
                    that._changeStep('accountRecover');
                }

                if (item.href.includes('/account/login')) {
                    event.preventDefault();
                    that._changeStep('accountLoginCheckMail');
                }
            });
        });

        $(this.el).find('[data-step]').click(function () {
            that._changeStep(this.dataset.step);
        });
    }

    _formSubmit(event) {
        if (typeof event != 'undefined') {
            event.preventDefault();
        }

        if (!this._form.checkValidity()) {
            return;
        }

        const requestUrl = DomAccess.getAttribute(this._form, 'action').toLowerCase();
        const formData = FormSerializeUtil.serialize(this._form);

        let email = formData.get('email');

        this._emailInputs.forEach(emailInput => {
            emailInput.value = email;
        });

        this._client.post(requestUrl, formData, this._onLoaded.bind(this))
    }

    _onLoaded(response) {
        response = JSON.parse(response);

        if (response.step) {
            this._changeStep(response.step);
        }

        if (response.message) {
            this._messageEl.innerHTML = response.message;
        }

        if (response.headline) {
            this._headlineEl.innerHTML = response.headline;
        }

        console.log(response);
    }

    _changeStep(step) {
        $('.dewa-account-login-step').addClass('d-none');
        $('#' + step).removeClass('d-none');
        this._messageEl.innerHTML = "";

        if (step === 'accountLoginCheckMail') {
            this._headlineEl.innerHTML = "";
        } else {
            this._headlineEl.innerHTML = "";
        }

        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}
