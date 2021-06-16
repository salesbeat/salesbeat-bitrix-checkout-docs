<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;
?>
<script>
    if (typeof BX !== 'undefined') {
        BX.Salesbeat.SaleDeliveryWidget = {
            deliveries: <?= $arParams['deliveries'] ?>, // Список всех способов доставок Sb
            selectedDelivery: {}, // Объект выбранной доставки
            hiddenProps: <?= $arParams['hidden_properties'] ?>, // Скрываемые поля
            locationProps: <?= $arParams['location_properties'] ?>, // ID свойств местоположений

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
            onLoad: function (ajaxAns) {
                BX.Salesbeat.SaleDeliveryWidget.checkedDelivery(BX.Sale.OrderAjaxComponent.result.DELIVERY);
                BX.Salesbeat.SaleDeliveryWidget.hideOrderProps();
                BX.Salesbeat.SaleDeliveryWidget.checkedStorageDelivery();
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
                if (this.selectedDelivery['TYPE'] === 'widget' && !this.selectedDelivery['IS_STORAGE']) {
                    errors[0] = '<?= Loc::getMessage('SB_SDW_TEMPLATE_ERROR_WIDGET') ?>';
                    this.checkerDelivery();
                } else if (this.selectedDelivery['TYPE'] === 'widget') {
                    this.checkerDelivery();
                }

                const elements = document.querySelectorAll('#bx-soa-orderSave, .bx-soa-cart-total-button-container');
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
            checkerDelivery: function () {
                const checkerDeliveryId = setInterval(function () {
                    const deliverySection = document.querySelector('#bx-soa-delivery.bx-selected');
                    if (deliverySection) {
                        BX.Salesbeat.SaleDeliveryWidget.addWidgetBlock();

                        if (BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['IS_STORAGE']) {
                            BX.Salesbeat.SaleDeliveryWidget.addResultWidget();
                        } else {
                            BX.Salesbeat.SaleDeliveryWidget.addTagWidget();
                            BX.Salesbeat.SaleDeliveryWidget.loadWidget();
                        }

                        clearInterval(checkerDeliveryId);
                    }
                }, 500);
            },

            addWidgetBlock: function () {
                const sectionElement = document.querySelector('#bx-soa-delivery .sb-section-widget');
                if (!sectionElement) {
                    let widgetBlock = document.createElement('div');
                    widgetBlock.classList.add('sb-section-widget');

                    let element = document.querySelector('#bx-soa-delivery .bx-soa-pp');
                    if (element) element.after(widgetBlock);
                }
            },
            addTagWidget: function () {
                let element = document.querySelector('#bx-soa-delivery .sb-section-widget');
                if (element) element.innerHTML = '<div id="sb-cart-widget"></div>';
            },
            loadWidget: function () {
                SB.init_cart({
                    token: '<?= $arParams['widget']['token'] ?>',
                    city_code: '',
                    products: <?= $arParams['widget']['products'] ?>,
                    callback: function (data) {
                        let result = $.extend({delivery_id: BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['ID']}, data);
                        $.post('<?= $componentPath . '/ajax.php' ?>', result)
                            .done(function () {
                                BX.Salesbeat.SaleDeliveryWidget.selectedDelivery['IS_STORAGE'] = true;
                                BX.Salesbeat.SaleDeliveryWidget.sendOrderRequest();
                            });
                    }
                });
            },
            addResultWidget: function () {
                let result = document.querySelector('#bx-soa-delivery .bx-soa-pp-company-desc .bx-soa-pp-list');
                if (result) {
                    let reloadInfo = document.createElement('li');
                    reloadInfo.innerHTML = '<a href="" class="sb-reshow-cart-widget"><?= Loc::getMessage('SB_SDW_TEMPLATE_CHANGE_DELIVERY') ?></a>';
                    result.appendChild(reloadInfo);

                    let sectionWidget = document.querySelector('#bx-soa-delivery .sb-section-widget');
                    sectionWidget.appendChild(result);

                    let button = document.querySelector('.sb-reshow-cart-widget');
                    button.addEventListener('click', function (e) {
                        e.preventDefault();

                        BX.Salesbeat.SaleDeliveryWidget.reshowWidget();
                    });
                }
            },
            reshowWidget: function () {
                this.addTagWidget();
                this.loadWidget();
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