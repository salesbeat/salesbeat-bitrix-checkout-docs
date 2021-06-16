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

    if (isset($params['shop_cart_id'])) $params['shop_cart_id'] = (string)$params['shop_cart_id'];
    if (isset($params['shop_cart_id'])) $params['shop_cart_id'] = (string)$params['shop_cart_id'];
    if (isset($params['quantity'])) $params['quantity'] = (int)$params['quantity'];

    if (!empty($params['shop_cart_id'])) {
        $siteId = Context::getCurrent()->getSite();
        $basket = Sale\Basket::loadItemsForFUser((string)$params['shop_cart_id'], (string)$siteId);

        $basketItems = $basket->getBasketItems();
        foreach ($basketItems as $key => $basketItem) {
            $productId = $basketItem->getField('ID');
            if ($basketItem->getField('PRODUCT_ID') !== $params['product_id']) continue;

            $basketItem->setField('QUANTITY', $params['quantity']);
        }

        $basket->save();

        // echo '<pre>', print_r(get_class_methods($basket)), '</pre>';
    } else {
        throw new Exception('Укажите параметр: shop_cart_id');
    }

    $result = ['data' => true];
} catch (Exception $e) {
    $result = [
        'error' => $e->getMessage()
    ];
}

/** @noinspection PhpVariableNamingConventionInspection */
//global $APPLICATION;
//$APPLICATION->restartBuffer();
//header('Content-Type:application/json; charset=UTF-8');

echo Json::encode($result, JSON_UNESCAPED_UNICODE);

//\CMain::FinalActions();
//die();