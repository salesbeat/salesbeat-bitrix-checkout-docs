BX.namespace('BX.Salesbeat.Input');

BX.Salesbeat.Input = (function () {
    'use strict';

    let Module = BX.Sale.Input,
        Utils = Module.Utils;

    Module.SbLocationInput = SbLocationInput;
    Utils.extend(SbLocationInput, Module.BaseInput);
    Module.Manager.register('SBLOCATION', SbLocationInput);

    function SbLocationInput(name, settings, value, publicO) {
        /**
         * Костыль из-за Bitrix
         * Виновник костыля жесткий switch типов в js компонента sales.order.ajax
         */
        settings.TYPE = 'STRING';

        /**
         * Костыль из-за Bitrix
         * Тут почти таже беда что и выше, нет метода который бы позволил убрать возможность множественного значения,
         * По этому приходится подменять реальные значения =(
         */
        settings.MULTIPLE = 'N';

        SbLocationInput.__super__.constructor.call(this, name, settings, value, publicO);
    }

    SbLocationInput.prototype.createEditorSingle = function (name, value) {
        const element = BX.create('div');

        BX.ajax({
            method: 'POST',
            url: '/bitrix/services/salesbeat.sale/get_delivery_location.php',
            dataType: 'html',
            data: {INPUT_NAME: name, INPUT_VALUE: value},
            async: false,
            processData: true,
            scriptsRunFirst: true,
            emulateOnload: true,
            start: true,
            cache: false,
            onsuccess: function (data) {
                element.innerHTML = data;
            }
        });

        return [element];
    };

    SbLocationInput.prototype.afterEditorSingleInsert = function (item) {
        item[0].focus();
    };

    SbLocationInput.prototype.setValueSingle = function (item, value) {
        item[0].value = value;
    };

    SbLocationInput.prototype.getValueSingle = function (item) {
        return item[0].disabled ? null : item[0].value;
    };

    SbLocationInput.prototype.setDisabledSingle = function (item, disabled) {
        item[0].disabled = disabled;
    };

    SbLocationInput.prototype.addEventSingle = function (item, name, action) {
        Utils.addEventTo(item[0], name, action);
    };
});
BX.Salesbeat.Input();