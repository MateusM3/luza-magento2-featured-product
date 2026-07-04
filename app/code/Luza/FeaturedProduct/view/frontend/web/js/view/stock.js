define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Luza_FeaturedProduct/stock',
            qty: 0,
            updateUrl: '',
            interval: 15000,
            enabled: false,
            pollingId: null
        },

        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            this._startPolling();

            return this;
        },

        /**
         * @returns {Object}
         */
        initObservable: function () {
            this._super().observe(['qty']);

            return this;
        },

        /**
         * Starts the periodic refresh when real-time updates are enabled.
         *
         * @private
         */
        _startPolling: function () {
            if (!this.enabled || !this.updateUrl || this.interval <= 0) {
                return;
            }

            this.pollingId = setInterval(this._refresh.bind(this), this.interval);
        },

        /**
         * Fetches the current salable quantity and updates the observable.
         *
         * @private
         */
        _refresh: function () {
            $.getJSON(this.updateUrl).done(function (data) {
                if (data && data.qty !== undefined) {
                    this.qty(data.qty);
                }
            }.bind(this));
        }
    });
});
