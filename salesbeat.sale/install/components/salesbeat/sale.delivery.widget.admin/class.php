<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Web\Json;
use \Bitrix\Sale\Delivery;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\Basket;

class SbSaleDeliveryWidgetAdmin extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('salesbeat.sale')) die();

        \CUtil::InitJSCore(['jquery2']);

        Asset::getInstance()->addJs('//app.salesbeat.pro/static/widget/js/widget.js');
        Asset::getInstance()->addJs('//app.salesbeat.pro/static/widget/js/cart_widget.js');

        $this->includeComponentTemplate();
    }

    public function onPrepareComponentParams($arParams)
    {
        $arSbDeliveries = [];
        if ($arDeliveries = Delivery\Services\Table::getList()) {
            foreach ($arDeliveries as $arDelivery) {
                if (in_array($arDelivery['CLASS_NAME'], ['\Sale\Handlers\Delivery\SalesbeatHandler', '\Sale\Handlers\Delivery\Salesbeat2Profile'])) {
                    $arSbDeliveries[$arDelivery['ID']] = $arDelivery;
                }
            }
            unset($arDeliveries, $arDelivery);
        }

        $arParams['order_id'] = (int)$arParams['order_id'];
        $arParams['token'] = Option::get(System::getModuleId(), 'api_token');
        $arParams['products'] = Json::encode(Basket::getItemList($arParams['order_id']));
        $arParams['error'] = !$arSbDeliveries ? 'Ошибка! Создайте хотябы одну доставку Salesbeat' : '';

        return $arParams;
    }
}
