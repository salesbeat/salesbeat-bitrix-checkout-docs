<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use \Salesbeat\Sale\CreateOrder;

Loc::loadMessages(__FILE__);

if (empty(Loader::includeModule('main'))) return;
if (empty(Loader::includeModule('sale'))) return;
if (empty(Loader::includeModule('salesbeat.sale'))) return;

global $APPLICATION;

try {
    $params = [];

    $request = Main\Application::getInstance()->getContext()->getRequest();
    if (!empty($request->getPostList()->getValues())) {
        $params = $request->getPostList()->getValues();
    } else {
        $params = Json::decode(file_get_contents('php://input'));
    }

    if (empty($params))
        throw new Exception('Укажите параметры');

    $params['delivery']['delivery_id'] = 5;
    $params['payment']['payment_id'] = 1;

    $order = new CreateOrder();
    $orderId = $order->create($params);

    $protocol = CMain::IsHTTPS() ? 'https' : 'http';
    $url = parse_url($_SERVER['HTTP_REFERER'] <> '' ? $_SESSION['SESS_HTTP_REFERER'] : $_SERVER['HTTP_REFERER']);
    $site = $APPLICATION->GetSiteByDir($url['path'], $url['host'])['SERVER_NAME'];

    $result = [
        'order_id' => $orderId,
        'callback' => $protocol . '://' . $site . '/personal/order/index.php?ORDER_ID=' . $orderId,
    ];
} catch (Exception $e) {
    $result = [
        'error' => $e->getMessage()
    ];
}

/** @noinspection PhpVariableNamingConventionInspection */
global $APPLICATION;
$APPLICATION->restartBuffer();
header('Content-Type:application/json; charset=UTF-8');

echo Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();