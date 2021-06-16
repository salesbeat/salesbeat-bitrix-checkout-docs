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

$this->addExternalJS('//cdn.to.digital/checkout-sdk.js');

$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJS($templateFolder . '/script.js');
?>
<div class="basket-line" data-sb-basket>
    <div class="basket-line-block" data-sb-order>
        <div>Корзина <?= $arResult['count'] ?> шт</div>
        <div>на сумму <strong><?= $arResult['price'] ?> ₽</strong></div>
    </div>
</div>

<script>
    if (typeof window.frameCacheVars !== 'undefined') {
        BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleBasketSmall.init({
            siteId: '<?= SITE_ID ?>',
            ajaxPath: '<?= $componentPath ?>/ajax.php',
            isAjax: '<?= ($arParams['AJAX'] && $arParams['AJAX'] === 'Y') ?>',
            templateName: '<?= $templateName ?>',
            token: '<?= $arParams['token'] ?>',
            cart_id: '<?= $arResult['sb_cart_id'] ?>'
        }));
    } else {
        BX.ready(BX.Salesbeat.SaleBasketSmall.init({
            siteId: '<?= SITE_ID ?>',
            ajaxPath: '<?= $componentPath ?>/ajax.php',
            isAjax: '<?= ($arParams['AJAX'] && $arParams['AJAX'] === 'Y') ?>',
            templateName: '<?= $templateName ?>',
            token: '<?= $arParams['token'] ?>',
            cart_id: '<?= $arResult['sb_cart_id'] ?>'
        }));
    }
</script>