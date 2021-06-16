BX.Salesbeat.SaleOrderAjax = {
    init: function (params) {
        this.params = params;

        this.client = new window.Salesbeat(this.params.cart_id, this.params.token);
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