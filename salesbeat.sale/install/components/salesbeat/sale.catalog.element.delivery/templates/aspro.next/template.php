<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global string $componentPath
 * @global string $templateName
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

$this->addExternalJS('//app.salesbeat.pro/static/widget/js/widget.js');

$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJS($templateFolder . '/script.js');
?>
<div id="<?= $arParams['main_div_id'] ?>" class="salesbeat-deliveries"></div>

<script>
    if (typeof window.frameCacheVars !== 'undefined') {
        BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleBasketSmall.init({
            token: '<?= $arParams['token'] ?>',
            price_to_pay: '<?= $arParams['price_to_pay'] ?>',
            price_insurance: '<?= $arParams['price_insurance'] ?>',
            weight: '<?= $arParams['weight'] ?>',
            x: '<?= $arParams['x'] ?>',
            y: '<?= $arParams['y'] ?>',
            z: '<?= $arParams['z'] ?>',
            quantity: quantity || '<?= $arParams['quantity'] ?>',
            city_by: '<?= $arParams['city_code'] ?>',
            params_by: '<?= $arParams['params_by'] ?>',
            main_div_id: '<?= $arParams['main_div_id'] ?>',
        }));
    } else {
        BX.ready(BX.Salesbeat.SaleBasketSmall.init({
            token: '<?= $arParams['token'] ?>',
            price_to_pay: '<?= $arParams['price_to_pay'] ?>',
            price_insurance: '<?= $arParams['price_insurance'] ?>',
            weight: '<?= $arParams['weight'] ?>',
            x: '<?= $arParams['x'] ?>',
            y: '<?= $arParams['y'] ?>',
            z: '<?= $arParams['z'] ?>',
            quantity: quantity || '<?= $arParams['quantity'] ?>',
            city_by: '<?= $arParams['city_code'] ?>',
            params_by: '<?= $arParams['params_by'] ?>',
            main_div_id: '<?= $arParams['main_div_id'] ?>',
        }));
    }
</script>
