BX.namespace('BX.Salesbeat');

window.timerUpdateBasket = {};

BX.Salesbeat.SaleOrderAjax = {
    init: function (params) {
        this.params = params;

        this.client = new window.Salesbeat(
            this.params.sb_cart_id,
            this.params.token,
            {
                onUpdateQuantity: ({ id, quantity }) => {
                    clearTimeout(window.timerChange);
                    window.timerChange = setTimeout(() => {
                        BX.ajax({
                            url: '/bitrix/services/salesbeat.sale/update_basket.php',
                            method: 'POST',
                            dataType: 'json',
                            data: { cart_id: params.cart_id, product_id: id, quantity: quantity },
                            onsuccess: BX.onCustomEvent('OnBasketChange')
                        });
                    }, 700);
                }
            }
        );

        this.buttonPaySystem = document.querySelector('[data-pay-system] input[type=submit]');

        if (params.type === 'checkout') {
            this.openCheckout();
        } else if (params.type === 'confirm') {
            this.payed();
        }
    },

    payed: function () {
        if (this.buttonPaySystem) this.buttonPaySystem.click();
    },

    openCheckout: function () {
        this.client.openCheckout();
    }
}