<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;
?>
<div id="<?= $arParams['main_div_id'] ?>" class="sb-ui-location<?= !empty($arResult['MODE_CLASSES']) ? $arResult['MODE_CLASSES'] : '' ?>" data-sb-location>
    <input type="text" style="display: none" name="<?= $arParams['INPUT_NAME'] ?>"
           value="<?= htmlspecialcharsbx($arParams['INPUT_VALUE']) ?>" data-sb-location-hidden>

    <div class="sb-ui-location__search">
        <div class="sb-ui-location__search-icon"></div>
        <input type="text" value="<?= $arParams['CITY']['NAME'] ?>" class="sb-ui-location__search-input"
               placeholder="<?= Loc::getMessage('SB_SCED_PARAMS_DISPLAY_VALUE_NAME') ?>"
               autocomplete="off" data-sb-location-input>
        <div class="sb-ui-location__search-clear" title="<?= Loc::getMessage('SB_SLS_TEMPLATE_CLEAR') ?>"
             data-sb-location-clear></div>
    </div>

    <div class="sb-ui-location__variants scrollbar" data-sb-location-list></div>
</div>

<script>
    if (typeof BX !== 'undefined') {
        BX.Salesbeat.SaleLocationSelector = {
            isChange: false,
            init: function () {
                if (typeof BX.addCustomEvent !== 'undefined')
                    BX.addCustomEvent('onAjaxSuccess', BX.Salesbeat.SaleLocationSelector.onLoad);

                if (window.jsAjaxUtil) {
                    jsAjaxUtil._CloseLocalWaitWindow = jsAjaxUtil.CloseLocalWaitWindow;
                    jsAjaxUtil.CloseLocalWaitWindow = function (TID, cont) {
                        jsAjaxUtil._CloseLocalWaitWindow(TID, cont);
                        BX.Salesbeat.SaleLocationSelector.onLoad();
                    }
                }

                BX.Salesbeat.SaleLocationSelector.onLoad();
            },
            onLoad: function (ajaxAns) {
                BX.loadCSS(['<?= $templateFolder?>/style.css']);
                // BX.loadScript(['<?= $templateFolder?>/script.js']);

                BX.Salesbeat.SaleLocationSelector.locationBlock = BX('<?= $arParams['main_div_id'] ?>');

                if (BX.Salesbeat.SaleLocationSelector.locationBlock !== null) {
                    BX.Salesbeat.SaleLocationSelector.locationInput = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-input]');
                    BX.Salesbeat.SaleLocationSelector.locationClear = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-clear]');
                    BX.Salesbeat.SaleLocationSelector.locationList = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-list]');
                    BX.Salesbeat.SaleLocationSelector.locationHidden = BX.Salesbeat.SaleLocationSelector.locationBlock.querySelector('[data-sb-location-hidden]');
                    BX.Salesbeat.SaleLocationSelector.ajaxURL = '<?= CUtil::JSEscape($component->getPath() . '/ajax.php') ?>' || '';

                    BX.Salesbeat.SaleLocationSelector.bindEvents();
                }
            },
            bindEvents: function () {
                let me = this;
                BX.bind(this.locationInput, 'click', function (e) {
                    e.preventDefault();

                    if (!BX.hasClass(me.locationBlock, 'active'))
                        BX.addClass(me.locationBlock, 'active');
                });

                BX.bind(document, 'mousedown', function (e) {
                    let target = e.target || e.srcElement,
                        element = BX.findParent(target, {className: 'sb-ui-location'});

                    if (element !== null) return;

                    if (me.isChange) {
                        me.locationHidden.value = '';
                        me.isChange = false;
                    }

                    BX.removeClass(me.locationBlock, 'active');
                });

                BX.bind(this.locationClear, 'click', function (e) {
                    e.preventDefault();

                    if (BX.hasClass(me.locationBlock, 'active'))
                        BX.removeClass(me.locationBlock, 'active');

                    me.locationInput.value = '';
                    me.locationHidden.value = '';
                    me.locationList.innerText = '';
                });

                BX.bind(this.locationList, 'click', function (e) {
                    e.preventDefault();

                    let target = e.target || e.srcElement,
                        element = target.tagName === 'DIV' ? target : BX.findParent(target, {tagName: 'DIV'}),
                        cityName = element.innerText,
                        cityCode = element.getAttribute('data-location-code') || '';

                    if (!cityName.length || !cityCode.length) return;

                    me.isChange = false;
                    me.locationInput.value = element.innerText;
                    me.locationHidden.value = cityCode + '#' + cityName;

                    if (BX.hasClass(me.locationBlock, 'active'))
                        BX.removeClass(me.locationBlock, 'active');

                    me.sendOrderRequest();
                });

                BX.bind(this.locationInput, 'keyup', BX.proxy(this.ajaxEvent, this));
            },
            ajaxEvent: function () {
                let me = this,
                    len = this.locationInput.value;

                this.isChange = true;

                if (len.length > 2) {
                    clearTimeout(window.timerChange);
                    window.timerChange = setTimeout(function () {
                        BX.ajax({
                            method: 'POST',
                            url: me.ajaxURL,
                            dataType: 'json',
                            data: {len: len},
                            timeout: 30,
                            async: false,
                            processData: true,
                            scriptsRunFirst: true,
                            emulateOnload: false,
                            start: true,
                            cache: false,
                            onsuccess: function (data) {
                                me.variantsList(data);
                            }
                        });
                    }, 700);
                }
            },
            variantsList: function (arResult) {
                if (!BX.hasClass(this.locationBlock, 'active'))
                    BX.addClass(this.locationBlock, 'active');

                this.locationList.innerText = '';
                if (typeof arResult.cities !== 'undefined' && arResult.cities.length > 0) {
                    let city, i = 0;
                    for (i; i < arResult.cities.length; i++) {
                        city = arResult.cities[i];

                        this.locationList.appendChild(
                            BX.create('DIV', {
                                props: {
                                    className: 'sb-ui-location__variants-item'
                                },
                                attrs: {
                                    'data-location-code': city.id
                                },
                                text: city.short_name + '. ' + city.name + ', ' + city.region_name
                            })
                        );
                    }
                } else {
                    this.locationList.appendChild(
                        BX.create('DIV', {
                            props: {
                                className: 'sb-ui-location__variants-error'
                            },
                            text: '<?= Loc::getMessage('SB_SLS_TEMPLATE_LIST_ERROR') ?>'
                        })
                    );
                }
            },
            sendOrderRequest: function () {
                if (typeof (BX.Sale) !== 'undefined' && BX.Sale.OrderAjaxComponent !== 'undefined')
                    BX.Sale.OrderAjaxComponent.sendRequest();
            }
        };

        if (typeof window.frameCacheVars !== 'undefined') {
            BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleLocationSelector.init);
        } else {
            BX.ready(BX.Salesbeat.SaleLocationSelector.init);
        }
    }
</script>