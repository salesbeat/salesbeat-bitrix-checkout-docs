BX.namespace('BX.Salesbeat');

if (typeof BX.Salesbeat.CatalogElementDelivery === 'undefined') {
    BX.Salesbeat.CatalogElementDelivery = {
        init: function (params) {
            this.params = params;

            this.elementBlock = BX(this.params.main_div_id);
            this.buttonPlus = document.querySelector('.product-item-amount-field-btn-plus');
            this.buttonMinus = document.querySelector('.product-item-amount-field-btn-minus');
            this.input = document.querySelector('.product-item-amount-field');

            if (this.elementBlock !== null) {
                this.bindEvents();
                this.loadWidget();
            }
        },
        bindEvents: function () {
            const that = this;

            BX.bind(this.buttonPlus, 'click', function (e) {
                that.changeQuantity(this);
            });

            BX.bind(this.buttonMinus, 'click', function (e) {
                that.changeQuantity(this);
            });

            BX.bind(this.input, 'keyup', function (e) {
                that.changeQuantity(this);
            });
        },
        changeQuantity: function () {
            const that = this;

            clearTimeout(window.timerChange);
            window.timerChange = setTimeout(() => {
                const value = Math.ceil(that.input.value);
                if (typeof value !== 'undefined') that.loadWidget(value);
            }, 700);
        },
        loadWidget: function (quantity) {
            SB.init({
                token: this.params.token,
                price_to_pay: this.params.price_to_pay,
                price_insurance: this.params.price_insurance,
                weight: this.params.weight,
                x: this.params.x,
                y: this.params.y,
                z: this.params.z,
                quantity: quantity || this.params.quantity,
                city_by: this.params.city_code,
                params_by: this.params.params_by,
                main_div_id: this.params.main_div_id,
                callback: function () {
                    console.log('Salesbeat is ready!');
                }
            });
        },
    };
}