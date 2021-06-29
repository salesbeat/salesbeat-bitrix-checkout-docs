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

/**
 * Используйте, если будете подключать компонент в шаблоне другого компонента
 * echo '<script type="text/javascript" src="//cdn.to.digital/checkout-sdk.js"></script>';
 * echo '<link rel="stylesheet" href="' . $templateFolder . '/style.css">';
 * echo '<script type="text/javascript" src="' . $templateFolder . '/script.js"></script>';
 */

$context = Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->get('ORDER_ID') <> '') {
    include Main\Application::getDocumentRoot() . $templateFolder . '/confirm.php';
} else {
    include Main\Application::getDocumentRoot() . $templateFolder . '/order.php';
}