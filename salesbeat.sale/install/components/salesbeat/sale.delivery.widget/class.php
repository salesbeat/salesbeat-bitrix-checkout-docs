<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Web\Json;
use \Bitrix\Sale\Delivery;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\Basket;
use \Salesbeat\Sale\Internals;
use \Salesbeat\Sale\Storage;

class SbSaleDeliveryWidget extends CBitrixComponent
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
        $arStorage = Storage::getInstance()->getList(); // Получаем информацию из хранилища

        // Данные для виджетов
        $arParams['widget'] = [
            'token' => Option::get(System::getModuleId(), 'api_token'),
            'products' => Json::encode(Basket::getItemList()),
        ];

        // Получаем список всех доставок
        $deliveries = [];
        if ($arDeliveries = Delivery\Services\Manager::getActiveList()) {
            foreach ($arDeliveries as $arDelivery) {
                if ($arDelivery['CLASS_NAME'] === '\Sale\Handlers\Delivery\SalesbeatHandler') {
                    $arDelivery['CONFIG']['MAIN']['METHOD_ID'] = 'widget';
                    $arDelivery['CONFIG']['MAIN']['METHOD_TYPE'] = 'widget';
                }

                $code = !empty($arDelivery['CONFIG']['MAIN']['METHOD_ID']) ?
                    $arDelivery['CONFIG']['MAIN']['METHOD_ID'] : 'other';
                $type = !empty($arDelivery['CONFIG']['MAIN']['METHOD_TYPE']) ?
                    $arDelivery['CONFIG']['MAIN']['METHOD_TYPE'] : 'other';

                $isStorage = !empty($arStorage[$arDelivery['ID']]['DELIVERY_PRICE']);

                $deliveries[$arDelivery['ID']] = [
                    'ID' => $arDelivery['ID'],
                    'CODE' => $code,
                    'TYPE' => $type,
                    'IS_STORAGE' => $isStorage,
                ];
            }
            unset($arDeliveries, $arDelivery, $deliveryId, $deliveryCode, $deliveryType);
        }
        $arParams['deliveries'] = CUtil::PhpToJSObject($deliveries);

        $allProperties = [];
        $hiddenProperties = [];
        $locationProperties = [];

        $arSbProps = Internals::getPropertyList([
            'order' => ['ID' => 'ASC'],
            'filter' => ['CODE' => array_column(Internals::getSbPropertyList(), 'CODE')]
        ]);
        if ($arSbProps) {
            $allProperties = array_column($arSbProps, 'ID');

            $array = [
                'widget' => [
                    'SB_LOCATION', 'SB_DELIVERY_METHOD_NAME', 'SB_DELIVERY_METHOD_ID', 'SB_DELIVERY_PRICE',
                    'SB_DELIVERY_DAYS', 'SB_PVZ_ID', 'SB_PVZ_ADDRESS', 'SB_STREET', 'SB_HOUSE', 'SB_HOUSE_BLOCK',
                    'SB_FLAT', 'SB_INDEX', 'SB_COMMENT'
                ],
                'courier' => [
                    'SB_DELIVERY_METHOD_NAME', 'SB_DELIVERY_METHOD_ID', 'SB_DELIVERY_PRICE', 'SB_DELIVERY_DAYS',
                    'SB_PVZ_ID', 'SB_PVZ_ADDRESS', 'SB_INDEX', 'SB_COMMENT'
                ],
                'post' => [
                    'SB_DELIVERY_METHOD_NAME', 'SB_DELIVERY_METHOD_ID', 'SB_DELIVERY_PRICE', 'SB_DELIVERY_DAYS',
                    'SB_PVZ_ID', 'SB_PVZ_ADDRESS', 'SB_COMMENT'
                ],
                'pvz' => [
                    'SB_DELIVERY_METHOD_NAME', 'SB_DELIVERY_METHOD_ID', 'SB_DELIVERY_PRICE', 'SB_DELIVERY_DAYS',
                    'SB_PVZ_ID', 'SB_PVZ_ADDRESS', 'SB_STREET', 'SB_HOUSE', 'SB_HOUSE_BLOCK', 'SB_FLAT', 'SB_INDEX',
                    'SB_COMMENT'
                ],
                'other' => [
                    'SB_LOCATION', 'SB_DELIVERY_METHOD_NAME', 'SB_DELIVERY_METHOD_ID', 'SB_DELIVERY_PRICE',
                    'SB_DELIVERY_DAYS', 'SB_PVZ_ID', 'SB_PVZ_ADDRESS', 'SB_STREET', 'SB_HOUSE', 'SB_HOUSE_BLOCK',
                    'SB_FLAT', 'SB_INDEX', 'SB_COMMENT'
                ]
            ];

            foreach ($array as $type => $fields) {
                $arProperties = [];
                foreach ($arSbProps as $arSbProp) {
                    if (in_array($arSbProp['CODE'], $fields))
                        $arProperties[] = $arSbProp['ID'];
                    if ($arSbProp['CODE'] == 'SB_LOCATION' &&
                        !in_array($arSbProp['ID'], $locationProperties))
                        $locationProperties[] = $arSbProp['ID'];
                }

                $hiddenProperties[$type] = $arProperties;
            }
        }

        $arParams['all_properties'] = Json::encode($allProperties);
        $arParams['hidden_properties'] = Json::encode($hiddenProperties);
        $arParams['location_properties'] = Json::encode($locationProperties);

        return $arParams;
    }
}
