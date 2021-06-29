<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global string $componentPath
 * @global string $templateName
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

echo '<link rel="stylesheet" href="' . $templateFolder . '/style.css">';
echo '<script type="text/javascript" src="' . $templateFolder . '/script.js"></script>';
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
        if (typeof window.frameCacheVars !== 'undefined') {
            BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleLocationSelector.init({
                locationBlock: '<?= $arParams['main_div_id'] ?>',
                ajaxURL: '<?= CUtil::JSEscape($component->getPath() . '/ajax.php') ?>',
                errorText: '<?= Loc::getMessage('SB_SLS_TEMPLATE_LIST_ERROR') ?>'
            }));
        } else {
            BX.ready(BX.Salesbeat.SaleLocationSelector.init({
                locationBlock: '<?= $arParams['main_div_id'] ?>',
                ajaxURL: '<?= CUtil::JSEscape($component->getPath() . '/ajax.php') ?>',
                errorText: '<?= Loc::getMessage('SB_SLS_TEMPLATE_LIST_ERROR') ?>'
            }));
        }
    }
</script>