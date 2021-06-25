BX.namespace('BX.Salesbeat');

if (typeof window.timerUpdateBasket === 'undefined')
    window.timerUpdateBasket = {};

if (typeof BX.Salesbeat.SaleOrderAjax === 'undefined') {
    BX.Salesbeat.SaleOrderAjax = {
        init: function (params) {
            this.params = params;
            this.buttonPaySystem = document.querySelector('[data-pay-system] input[type=submit]');

            if (params.type === 'checkout') {
                this.openCheckout();
            } else if (params.type === 'confirm') {
                this.payed();
            }
        },

        initClient: function () {
            if (typeof this.client !== 'undefined') return false;

            this.client = new window.Salesbeat(
                this.params.sb_cart_id,
                this.params.token,
                {
                    onUpdateQuantity: ({id, quantity}) => {
                        clearTimeout(window.timerChange);
                        window.timerChange = setTimeout(() => {
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

        openCheckout: function () {
            this.initClient()
            this.client.openCheckout();
        },

        payed: function () {
            if (this.buttonPaySystem) this.buttonPaySystem.click();
        }
    }
}