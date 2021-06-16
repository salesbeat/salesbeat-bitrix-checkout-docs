<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 */
$arParams = htmlspecialcharsBack($arParams);

$this->arResult['ADMIN_MODE'] = defined('ADMIN_SECTION') && ADMIN_SECTION == true;

// Mode
$mode = '';
if ((defined('ADMIN_SECTION') && ADMIN_SECTION == true) || $arParams['ADMIN_MODE'] == 'Y')
    $mode = ' sb-ui-location--admin';

$arResult['MODE_CLASSES'] = $mode;