<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */
?>
<script>
    if (typeof window.frameCacheVars !== 'undefined') {
        BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleOrderAjax.init({
            type: 'checkout',
            token: '<?= $arParams['token'] ?>',
            cart_id: '<?= $arResult['sb_cart_id'] ?>'
        }));
    } else {
        BX.ready(BX.Salesbeat.SaleOrderAjax.init({
            type: 'checkout',
            token: '<?= $arParams['token'] ?>',
            cart_id: '<?= $arResult['sb_cart_id'] ?>'
        }));
    }
</script>
