<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

if ($_SESSION['SALESBEAT_ADMIN'])
    //unset($_SESSION['SALESBEAT_ADMIN']);

if ($arParams['error']) {
    echo $arParams['error'];
    return false;
}
?>
<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <?= Loc::getMessage('SB_DAOW_TEMPLATE_INFO_MESSAGE') ?>
    </div>
</div>

<div id="sb-cart-widget"></div>
<div id="sb-cart-widget-result"></div>
<script>
    if (typeof BX !== 'undefined') {
        BX.Salesbeat.SaleDeliveryWidgetAdmin = {
            selected: false,

            init: function () {
                BX.Salesbeat.SaleDeliveryWidgetAdmin.bind();
            },

            bind: function () {
                BX.bind(BX('tab_cont_Salesbeat_salesbeat'), 'click',
                    BX.proxy(BX.Salesbeat.SaleDeliveryWidgetAdmin.loadWidget, this));
            },

            loadWidget: function () {
                if (BX.Salesbeat.SaleDeliveryWidgetAdmin.selected) return false;

                SB.init_cart({
                    token: '<?= $arParams['token'] ?>',
                    city_code: '',
                    products: <?= $arParams['products'] ?>,
                    callback: function (data) {
                        $.post('<?= $componentPath . '/ajax.php' ?>', data)
                            .done(function () {
                                BX.Salesbeat.SaleDeliveryWidgetAdmin.selected = true;
                                BX.Salesbeat.SaleDeliveryWidgetAdmin.callbackWidget(data);
                            });
                    }
                });
            },
            callbackWidget: function (data) {
                let me = this,
                    methodName = data['delivery_method_name'] || 'Не известно';

                const elementResultBlock = document.querySelector('#sb-cart-widget-result');

                let location = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_LOCATION') ?>';
                if (data['city_code']) {
                    location += data['short_name'] ? data['short_name'] + '. ' : '';
                    location += data['city_name'];
                    location += data['region_name'] ? ', ' + data['region_name'] : '';
                } else {
                    location += '<?= Loc::getMessage('SB_DAOW_TEMPLATE_LOCATION_ERR') ?>';
                }

                let address = '';
                if (data['pvz_address']) {
                    address = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_PVZ_ADDRESS') ?>' + data['pvz_address']
                } else {
                    address = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_ADDRESS') ?>';

                    if (data['street']) address += '<?= Loc::getMessage('SB_DAOW_TEMPLATE_ADDRESS_STREET') ?>' + data['street'];
                    if (data['house']) address += '<?= Loc::getMessage('SB_DAOW_TEMPLATE_ADDRESS_HOUSE') ?>' + data['house'];
                    if (data['house_block']) address += '<?= Loc::getMessage('SB_DAOW_TEMPLATE_ADDRESS_HOUSE_BLOCK') ?>' + data['house_block'];
                    if (data['flat']) address += '<?= Loc::getMessage('SB_DAOW_TEMPLATE_ADDRESS_FLAT') ?>' + data['flat'];
                }

                let deliveryDays = '';
                if (data['delivery_days']) {
                    if (data['delivery_days'] === 0) {
                        deliveryDays = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_TODAY') ?>';
                    } else if (data['delivery_days'] === 1) {
                        deliveryDays = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_TOMORROW') ?>';
                    } else {
                        deliveryDays = this.suffixToNumber(data['delivery_days'], [
                                '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_DAYS1') ?>',
                                '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_DAYS2') ?>',
                                '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_DAYS3') ?>'
                            ]);
                    }
                } else {
                    deliveryDays = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_DELIVERY_DAYS_ERR') ?>';
                }

                let deliveryPrice = '';
                if (data['delivery_price']) {
                    deliveryPrice = data['delivery_price'] === 0 ?
                        '<?= Loc::getMessage('SB_DAOW_TEMPLATE_PRICE_FREE') ?>' :
                        this.numberWithCommas(data['delivery_price']) + '<?= Loc::getMessage('SB_DAOW_TEMPLATE_PRICE_CUR') ?>';
                } else {
                    deliveryPrice = '<?= Loc::getMessage('SB_DAOW_TEMPLATE_PRICE_ERR') ?>';
                }

                let comment = data['comment'] ? '<p> <?= Loc::getMessage('SB_DAOW_TEMPLATE_COMMENT') ?>' + data['comment'] + '</p>' : '';
                elementResultBlock.innerHTML += ('<p><span class="salesbeat-summary-label"><?= Loc::getMessage('SB_DAOW_TEMPLATE_METHOD_DELIVERY') ?></span> ' + methodName + '</p>'
                    + '<p><span class="salesbeat-summary-label"><?= Loc::getMessage('SB_DAOW_TEMPLATE_PRICE_DELIVERY') ?></span> ' + deliveryPrice + '</p>'
                    + '<p><span class="salesbeat-summary-label"><?= Loc::getMessage('SB_DAOW_TEMPLATE_TIME_DELIVERY') ?></span> ' + deliveryDays + '</p>'
                    + '<p>' + location + '</p>'
                    + '<p>' + address + '</p>' + comment
                    + '<p><a href="" class="sb-reshow-cart-widget"><?= Loc::getMessage('SB_DAOW_TEMPLATE_BUTTON_CHANGE') ?></a></p>'
                    + '<p><a href="" class="sb-clear-data"><?= Loc::getMessage('SB_DAOW_TEMPLATE_BUTTON_CANCEL') ?></a></p>');

                let buttonReshow = elementResultBlock.querySelector('.sb-reshow-cart-widget');
                buttonReshow.addEventListener('click', function (e) {
                    e.preventDefault();
                    me.reshowCardWidget();
                });

                let buttonClear = elementResultBlock.querySelector('.sb-clear-data');
                buttonClear.addEventListener('click', function (e) {
                    e.preventDefault();
                    me.clearDataWidget();
                });
            },
            reshowCardWidget: function () {
                SB.reinit_cart(true);

                BX.Salesbeat.SaleDeliveryWidgetAdmin.selected = false;
                const elementResultBlock = document.querySelector('#sb-cart-widget-result');
                elementResultBlock.innerHTML = '';
            },
            clearDataWidget: function () {
                $.post('<?= $componentPath . '/ajax.php' ?>', {'action': 'clear'})
                    .done(function () {
                        SB.reinit_cart(true);
                        BX.Salesbeat.SaleDeliveryWidgetAdmin.selected = false;

                        const elementResultBlock = document.querySelector('#sb-cart-widget-result');
                        elementResultBlock.innerHTML = '';
                    });
            },
            suffixToNumber: function (number, suffix) {
                let cases = [2, 0, 1, 1, 1, 2];
                return number + ' ' + suffix[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
            },
            numberWithCommas: function (string) {
                return string.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            }
        };

        if (typeof window.frameCacheVars !== 'undefined') {
            BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleDeliveryWidgetAdmin.init);
        } else {
            BX.ready(BX.Salesbeat.SaleDeliveryWidgetAdmin.init);
        }
    }
</script>