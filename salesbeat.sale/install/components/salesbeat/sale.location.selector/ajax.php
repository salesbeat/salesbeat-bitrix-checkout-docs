<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Web\Json;
use \Salesbeat\Sale\Api;

$arCity = [];
if (Loader::includeModule('salesbeat.sale') && isset($_POST['len']))
    $arCity = Api::getCities('', ['city' => $_POST['len']]);

echo Json::encode($arCity);