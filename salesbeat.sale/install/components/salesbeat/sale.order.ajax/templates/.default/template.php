<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;

/**
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */
$this->addExternalJS('//cdn.to.digital/checkout-sdk.js');

$this->addExternalCss($templateFolder . '/stale.css');
$this->addExternalJS($templateFolder . '/script.js');

$context = Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->get('ORDER_ID') <> '') {
    include Main\Application::getDocumentRoot() . $templateFolder . '/confirm.php';
} else {
    include Main\Application::getDocumentRoot() . $templateFolder . '/order.php';
}