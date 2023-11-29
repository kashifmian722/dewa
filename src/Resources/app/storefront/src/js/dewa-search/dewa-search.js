import SearchWidgetPlugin from 'src/plugin/header/search-widget.plugin';
import ButtonLoadingIndicator from 'src/utility/loading-indicator/button-loading-indicator.util';

export default class DewaSearch extends SearchWidgetPlugin {

    _suggest(value) {
        const url = this._url + encodeURIComponent(value);

        // init loading indicator
        const indicator = new ButtonLoadingIndicator(this._submitButton);
        indicator.create();

        this.$emitter.publish('beforeSearch');

        this._client.abort();
        this._client.get(url, (response) => {
            // remove existing search results popover first
            this._clearSuggestResults();

            // remove indicator
            indicator.remove();

            // attach search results to the DOM
            this.el.insertAdjacentHTML('afterend', response);
            window.PluginManager.initializePlugins();

            this.$emitter.publish('afterSuggest');
        });
    }

    _focusInput() {
        setTimeout(() =>  this._inputField.focus(), 1000);
    }

    _onBodyClick(e) {}
}
