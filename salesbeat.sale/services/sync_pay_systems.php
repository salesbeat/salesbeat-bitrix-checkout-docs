<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Sale;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\Api;

Loc::loadMessages(__FILE__);

if (empty(Loader::includeModule('main'))) return;
if (empty(Loader::includeModule('sale'))) return;
if (empty(Loader::includeModule('salesbeat.sale'))) return;

$request = Main\Application::getInstance()->getContext()->getRequest();

$rsPaySystem = Sale\PaySystem\Manager::getList([
    'order' => ['ID' => 'ASC', 'NAME' => 'ASC'],
    'filter' => ['ACTIVE' => 'Y'],
]);

$paySystemList = [];
while ($arPaySystem = $rsPaySystem->fetch())
    $paySystemList[] = $arPaySystem;

try {
    $apiResult = Api::syncDeliveryPaymentTypes('', $paySystemList);
    if (empty($apiResult['success'])) throw new Exception(Loc::getMessage('SB_SERVICES_SYNC_PAY_SYSTEMS_ERROR_MESSAGE'));

    $objDateTime =  new DateTime();
    $strDatetime = $objDateTime->toString();
    Option::set(System::getModuleId(), 'pay_systems_last_sync', $strDatetime);

    $result = [
        'status' => 'success',
        'message' => $strDatetime
    ];
} catch (Exception $e) {
    $result = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

/** @noinspection PhpVariableNamingConventionInspection */
global $APPLICATION;
$APPLICATION->restartBuffer();
header('Content-Type:application/json; charset=UTF-8');

echo Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();