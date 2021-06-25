BX.namespace('BX.Salesbeat');

if (typeof window.timerUpdateBasket === 'undefined')
    window.timerUpdateBasket = {};

if (typeof BX.Salesbeat.SaleBasketSmall === 'undefined') {
    BX.Salesbeat.SaleBasketSmall = {
        init: function (params) {
            this.params = params;

            this.events();
            this.bindEvents();
        },

        initClient: function () {
            if (typeof this.client !== 'undefined') return false;

            this.client = new window.Salesbeat(
                this.params.sb_cart_id,
                this.params.token,
                {
                    onUpdateQuantity: ({id, quantity}) => {
                        clearTimeout(window.timerUpdateBasket[id]);
                        window.timerUpdateBasket[id] = setTimeout(() => {
                            BX.ajax({
                                url: '/bitrix/services/salesbeat.sale/update_basket.php',
                                method: 'POST',
                                dataType: 'json',
                                data: {cart_id: this.params.cart_id, product_id: id, quantity: quantity},
                                onsuccess: BX.onCustomEvent('OnBasketChange', ['no-update-sb-basket'])
                            });
                        }, 700);
                    }
                }
            );
        },

        events: function () {
            if (this.params.action === 'open-sb-checkout')
                BX.Salesbeat.SaleBasketSmall.openCheckout();
        },

        bindEvents: function () {
            if (!this.params.isAjax) {
                BX.addCustomEvent(window, 'OnBasketChange', (action) => {
                    BX.Salesbeat.SaleBasketSmall.refreshCart({
                        action: action
                    });
                });
            }

            const buttonOrder = document.querySelector('[data-sb-order]');
            BX.bind(buttonOrder, 'click', function (e) {
                e.preventDefault();
                BX.Salesbeat.SaleBasketSmall.openCheckout();
            });
        },

        openCheckout: function () {
            this.initClient();
            this.client.openCheckout();
        },

        refreshCart: function (data) {
            data.sessid = BX.bitrix_sessid();
            data.siteId = this.params.siteId;
            data.templateName = this.params.templateName;
            data.cart_id = this.params.cart_id;
            data.sb_cart_id = this.params.sb_cart_id;
            data.token = this.params.token;

            BX.ajax({
                url: this.params.ajaxPath,
                method: 'POST',
                dataType: 'html',
                data: data,
                onsuccess: BX.Salesbeat.SaleBasketSmall.setCartBody
            });
        },

        setCartBody: function (result) {
            const cartElement = document.querySelector('[data-sb-basket]')
            if (cartElement) cartElement.innerHTML = result;
        },
    }
}