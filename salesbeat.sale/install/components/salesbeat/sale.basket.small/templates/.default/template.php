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

/**
 * Используйте, если будете подключать компонент в шаблоне другого компонента
 * echo '<script type="text/javascript" src="//cdn.to.digital/checkout-sdk.js"></script>';
 * echo '<link rel="stylesheet" href="' . $templateFolder . '/style.css">';
 * echo '<script type="text/javascript" src="' . $templateFolder . '/script.js"></script>';
 */
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
            cart_id: '<?= $arResult['cart_id'] ?>',
            sb_cart_id: '<?= $arResult['sb_cart_id'] ?>',
            token: '<?= $arParams['token'] ?>'
        }));
    } else {
        BX.ready(BX.Salesbeat.SaleBasketSmall.init({
            siteId: '<?= SITE_ID ?>',
            ajaxPath: '<?= $componentPath ?>/ajax.php',
            isAjax: '<?= ($arParams['AJAX'] && $arParams['AJAX'] === 'Y') ?>',
            templateName: '<?= $templateName ?>',
            cart_id: '<?= $arResult['cart_id'] ?>',
            sb_cart_id: '<?= $arResult['sb_cart_id'] ?>',
            token: '<?= $arParams['token'] ?>'
        }));
    }
</script>