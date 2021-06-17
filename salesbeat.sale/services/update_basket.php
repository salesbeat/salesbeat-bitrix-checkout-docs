<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Context;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use \Bitrix\Sale;

Loc::loadMessages(__FILE__);

if (empty(Loader::includeModule('main'))) return;
if (empty(Loader::includeModule('sale'))) return;
if (empty(Loader::includeModule('salesbeat.sale'))) return;

global $APPLICATION;

$params = [];

$request = Main\Application::getInstance()->getContext()->getRequest();
if (!empty($request->getPostList()->getValues())) {
    $params = $request->getPostList()->getValues();
} else {
    $params = Json::decode(file_get_contents('php://input'));
}

try {
    if (empty($params['cart_id']))
        throw new Exception('Заполните cart_id');

    if (empty($params['product_id']))
        throw new Exception('Заполните product_id');

    if (empty($params['quantity']))
        throw new Exception('Заполните quantity');

    $basket = Sale\Basket::loadItemsForFUser((string)$params['cart_id'], (string)Context::getCurrent()->getSite());
    $basketItems = $basket->getBasketItems();
    foreach ($basketItems as $key => $basketItem) {
        if ($basketItem->getField('PRODUCT_ID') !== $params['product_id']) continue;

        if (!empty($params['quantity'] > 0)) {
            $basketItem->setField('QUANTITY', $params['quantity']);
        } else {
            $basket->deleteItem($key);
        }
    }
    $basket->save();

    $result = [
        'status' => 200,
        'data' => true
    ];
} catch (Exception $e) {
    $result = [
        'status' => 400,
        'error' => $e->getMessage()
    ];
}

global $APPLICATION;
$APPLICATION->restartBuffer();
http_response_code($result['status']);
header('Content-Type:application/json; charset=UTF-8');
echo Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();