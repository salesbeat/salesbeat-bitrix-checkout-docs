<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

CJSCore::Init(['popup']);
?>
<script>
    if (typeof BX !== 'undefined') {
        BX.Salesbeat.SaleDeliveryWidget = {
            deliveries: <?= $arParams['deliveries'] ?>, // Список всех способов доставок Sb
            selectedDelivery: {}, // Объект выбранной доставки
            hiddenProps: <?= $arParams['hidden_properties'] ?>, // Скрываемые поля
            locationProps: <?= $arParams['location_properties'] ?>, // ID свойств местоположений
            obPopupWin: {},

            init: function () {
                if (typeof BX.addCustomEvent !== 'undefined')
                    BX.addCustomEvent('onAjaxSuccess', BX.Salesbeat.SaleDeliveryWidget.onLoad);

                if (window.jsAjaxUtil) {
                    jsAjaxUtil._CloseLocalWaitWindow = jsAjaxUtil.CloseLocalWaitWindow;
                    jsAjaxUtil.CloseLocalWaitWindow = function (TID, cont) {
                        jsAjaxUtil._CloseLocalWaitWindow(TID, cont);
                        BX.Salesbeat.SaleDeliveryWidget.onLoad();
                    }
                }

                BX.Salesbeat.SaleDeliveryWidget.onLoad();
            },
            onLoad: function () {
                BX.Salesbeat.SaleDeliveryWidget.checkedDelivery(BX.Sale.OrderAjaxComponent.result.DELIVERY);
                BX.Salesbeat.SaleDeliveryWidget.positionLocationProp();
                BX.Salesbeat.SaleDeliveryWidget.hideOrderProps();
                BX.Salesbeat.SaleDeliveryWidget.checkedStorageDelivery();
                BX.Salesbeat.SaleDeliveryWidget.bindModal();
            },
            checkedDelivery: function (deliveries) {
                this.selectedDelivery = {};
                if (!deliveries) return;

                for (let i in deliveries) {
                    if (!deliveries.hasOwnProperty(i)) continue;

                    const delivery = deliveries[i];
                    const element = this.deliveries[delivery.ID];

                    if (typeof element !== 'undefined' && delivery.CHECKED) {
                        this.selectedDelivery = element;
                        break;
                    }
                }
            },
            positionLocationProp: function () {
                let location = document.querySelector('.bx_soa_location div');

                for (let i in this.locationProps) {
                    if (!this.locationProps.hasOwnProperty(i)) continue;

                    let property = document.querySelector('[data-property-id-row="' + this.locationProps[i] + '"]');
                    if (property !== null) {
                        location.appendChild(property.cloneNode(true));
                        property.remove();
                    }
                }
            },
            hideOrderProps: function () {
                const elements = this.hiddenProps[this.selectedDelivery['TYPE']];
                for (let i in elements) {
                    if (!elements.hasOwnProperty(i)) continue;

                    let property = document.querySelector('[data-property-id-row="' + elements[i] + '"]');
                    if (property) property.hidden = true;
                }
            },
            checkedStorageDelivery: function () {
                let errors = [];
                if (this.selectedDelivery['TYPE'] === 'widget' && this.selectedDelivery['IS_STORAGE'] === false) {
                    errors[0] = '<?= Loc::getMessage('SB_SDW_TEMPLATE_ERROR_WIDGET') ?>';
                } else if (this.selectedDelivery['TYPE'] === 'pvz' && this.selectedDelivery['IS_STORAGE'] === false) {
                    errors[0] = '<?= Loc::getMessage('SB_SDW_TEMPLATE_ERROR_PVZ') ?>';
                }

                let elements = document.querySelectorAll('#bx-soa-orderSave, .bx-soa-cart-total-button-container');
                for (let i in elements) {
                    if (!elements.hasOwnProperty(i)) continue;
                    elements[i].hidden = (errors.length > 0);
                }

                if (typeof (BX.Sale.OrderAjaxComponent.showBlockErrors) === 'function') {
                    BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = errors;
                    BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
                } else if (typeof (BX.Sale.OrderAjaxComponent.showError) === 'function' && errors.length > 0) {
                    BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, errors[0]);
                }
            },

            bindModal: function () {
                let widgetButtons = document.querySelectorAll('#sb_widget, #sb_widget_err');
                for (let button of widgetButtons) {
                    BX.bind(button, 'click', function (e) {
                        e.preventDefault();
                        BX.Salesbeat.SaleDeliveryWidget.loadWidget(e);
                    });
                }

                let pvzButtons = document.querySelectorAll('#sb_pvz, #sb_pvz_err');
                for (let button of pvzButtons) {
                    BX.bind(button, 'click', function (e) {
                        e.preventDefault();
                        BX.Salesbeat.SaleDeliveryWidget.loadPvzMap(e);
                    });
                }
            },
            loadPopup: function (modalId, widgetId) {
                this.obPopupWin = BX.PopupWindowManager.create(modalId, null, {
                    autoHide: true,
                    offsetTop: 0,
                    offsetLeft: 0,
                    lightShadow: true,
                    closeByEsc: true,
                    closeIcon: {},
                    overlay: {},
                    content: BX.create('div', {props: {id: widgetId}}),
                });
                this.obPopupWin.show();
            },
            loadWidget: function (e) {
                const target = e.target || e.srcElement;
                const cityCode = target.getAttribute('city-code') || '';

                this.loadPopup('bx-salesbeat-widget', 'sb-cart-widget');

                SB.init_cart({
                    token: '<?= $arParams['widget']['token'] ?>',
                    city_code: cityCode,
                    products: <?= $arParams['widget']['products'] ?>,
                    callback: function (data) {
                        let result = $.extend({delivery_id: BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['ID']}, data);
                        $.post('<?= $componentPath . '/ajax.php' ?>', result)
                            .done(function () {
                                BX.Salesbeat.SaleDeliveryWidget.obPopupWin.close();
                                BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['IS_STORAGE'] = true;
                                BX.Salesbeat.SaleDeliveryWidget.sendOrderRequest();
                            });
                    }
                });
            },
            loadPvzMap: function (e) {
                e.preventDefault();

                const target = e.target || e.srcElement;
                const cityCode = target.getAttribute('data-city-code') || '';

                this.loadPopup('bx-salesbeat-pvz', 'sb-cart-pvz-map2');

                SB.show_pvz_map({
                    token: '<?= $arParams['widget']['token'] ?>',
                    city_code: cityCode || '',
                    pvz_map_id: 'sb-cart-pvz-map2',
                    products: <?= $arParams['widget']['products'] ?>,
                    callback: function (data) {
                        let result = $.extend({delivery_id: BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['ID']}, data);
                        $.post('<?= $componentPath . '/ajax.php' ?>', result)
                            .done(function () {
                                BX.Salesbeat.SaleDeliveryWidget.obPopupWin.close();
                                BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['IS_STORAGE'] = true;
                                BX.Salesbeat.SaleDeliveryWidget.sendOrderRequest();
                            });
                    }
                });
            },

            sendOrderRequest: function () {
                if (BX.Sale.OrderAjaxComponent !== 'undefined')
                    BX.Sale.OrderAjaxComponent.sendRequest();
            }
        };

        if (typeof window.frameCacheVars !== 'undefined') {
            BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleDeliveryWidget.init);
        } else {
            BX.ready(BX.Salesbeat.SaleDeliveryWidget.init);
        }
    }
</script>