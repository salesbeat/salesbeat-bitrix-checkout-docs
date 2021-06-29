BX.namespace('BX.Salesbeat');

if (typeof BX.Salesbeat.SaleLocationSelector === 'undefined') {
    BX.Salesbeat.SaleLocationSelector = {
        isChange: false,

        init: function (params) {
            if (typeof BX.addCustomEvent !== 'undefined')
                BX.addCustomEvent('onAjaxSuccess', function (e) {
                    BX.Salesbeat.SaleLocationSelector.onLoad(params)
                });

            if (window.jsAjaxUtil) {
                jsAjaxUtil._CloseLocalWaitWindow = jsAjaxUtil.CloseLocalWaitWindow;
                jsAjaxUtil.CloseLocalWaitWindow = function (TID, cont) {
                    jsAjaxUtil._CloseLocalWaitWindow(TID, cont);
                    BX.Salesbeat.SaleLocationSelector.onLoad(params);
                }
            }

            BX.Salesbeat.SaleLocationSelector.onLoad(params);
        },

        onLoad: function (params) {
            this.params = params;

            BX.Salesbeat.SaleLocationSelector.locationBlock = BX(this.params.locationBlock);
            if (BX.Salesbeat.SaleLocationSelector.locationBlock !== null) {
                BX.Salesbeat.SaleLocationSelector.locationInput = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-input]');
                BX.Salesbeat.SaleLocationSelector.locationClear = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-clear]');
                BX.Salesbeat.SaleLocationSelector.locationList = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-list]');
                BX.Salesbeat.SaleLocationSelector.locationHidden = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-hidden]');
                BX.Salesbeat.SaleLocationSelector.ajaxURL = this.params.ajaxURL;

                BX.Salesbeat.SaleLocationSelector.bindEvents();
            }
        },
        bindEvents: function () {
            const me = this;
            BX.bind(this.locationInput, 'click', function (e) {
                e.preventDefault();

                if (!BX.hasClass(me.locationBlock, 'active'))
                    BX.addClass(me.locationBlock, 'active');
            });

            BX.bind(document, 'mousedown', function (e) {
                let target = e.target || e.srcElement,
                    element = BX.findParent(target, {className: 'sb-ui-location'});

                if (element !== null) return;

                if (me.isChange) {
                    me.locationHidden.value = '';
                    me.isChange = false;
                }

                BX.removeClass(me.locationBlock, 'active');
            });

            BX.bind(this.locationClear, 'click', function (e) {
                e.preventDefault();

                if (BX.hasClass(me.locationBlock, 'active'))
                    BX.removeClass(me.locationBlock, 'active');

                me.locationInput.value = '';
                me.locationHidden.value = '';
                me.locationList.innerText = '';
            });

            BX.bind(this.locationList, 'click', function (e) {
                e.preventDefault();

                let target = e.target || e.srcElement,
                    element = target.tagName === 'DIV' ? target : BX.findParent(target, {tagName: 'DIV'}),
                    cityName = element.innerText,
                    cityCode = element.getAttribute('data-location-code') || '';

                if (!cityName.length || !cityCode.length) return;

                me.isChange = false;
                me.locationInput.value = element.innerText;
                me.locationHidden.value = cityCode + '#' + cityName;

                if (BX.hasClass(me.locationBlock, 'active'))
                    BX.removeClass(me.locationBlock, 'active');

                BX.ajax({
                    method: 'POST',
                    url: me.ajaxURL,
                    dataType: 'json',
                    data: {action: 'setCity', data: me.locationHidden.value},
                    timeout: 30,
                    async: false,
                    processData: true,
                    scriptsRunFirst: true,
                    emulateOnload: false,
                    start: true,
                    cache: false,
                    onsuccess: function () {
                        me.sendOrderRequest();
                    }
                });
            });

            BX.bind(this.locationInput, 'keyup', BX.proxy(me.ajaxEvent, me));
        },
        ajaxEvent: function () {
            const me = this;
            const len = this.locationInput.value;

            this.isChange = true;

            if (len.length > 2) {
                clearTimeout(window.timerChange);
                window.timerChange = setTimeout(function () {
                    BX.ajax({
                        method: 'POST',
                        url: me.ajaxURL,
                        dataType: 'json',
                        data: {len: len},
                        timeout: 30,
                        async: false,
                        processData: true,
                        scriptsRunFirst: true,
                        emulateOnload: false,
                        start: true,
                        cache: false,
                        onsuccess: function (data) {
                            me.variantsList(data);
                        }
                    });
                }, 700);
            }
        },
        variantsList: function (arResult) {
            if (!BX.hasClass(this.locationBlock, 'active'))
                BX.addClass(this.locationBlock, 'active');

            this.locationList.innerText = '';
            if (typeof arResult.cities !== 'undefined' && arResult.cities.length > 0) {
                let city, i = 0;
                for (i; i < arResult.cities.length; i++) {
                    city = arResult.cities[i];

                    this.locationList.appendChild(
                        BX.create('DIV', {
                            props: {
                                className: 'sb-ui-location__variants-item'
                            },
                            attrs: {
                                'data-location-code': city.id
                            },
                            text: city.short_name + '. ' + city.name + ', ' + city.region_name
                        })
                    );
                }
            } else {
                this.locationList.appendChild(
                    BX.create('DIV', {
                        props: {
                            className: 'sb-ui-location__variants-error'
                        },
                        text: this.params.errorText
                    })
                );
            }
        },
        sendOrderRequest: function () {
            if (typeof BX !== 'undefined' && typeof BX.Sale.OrderAjaxComponent !== 'undefined')
                BX.Sale.OrderAjaxComponent.sendRequest();
        }
    }
}