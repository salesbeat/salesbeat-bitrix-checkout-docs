BX.namespace('BX.Salesbeat');

BX.Salesbeat.SaleBasketSmall = {
    init: function (params) {
        this.params = params;

        this.client = new window.Salesbeat(this.params.cart_id, this.params.token);

        this.events();
        this.bindEvents();
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
        this.client.openCheckout();
    },

    refreshCart: function (data) {
        data.sessid = BX.bitrix_sessid();
        data.siteId = this.params.siteId;
        data.templateName = this.params.templateName;
        data.cart_id = this.params.cart_id;
        data.token = this.params.token;

        BX.ajax({
            url: this.params.ajaxPath,
            method: 'POST',
            dataType: 'html',
            data: data,
            onsuccess: this.setCartBody
        });
    },

    setCartBody: function (result) {
        const cartElement = document.querySelector('[data-sb-basket]')
        if (cartElement) cartElement.innerHTML = result;
    },
}