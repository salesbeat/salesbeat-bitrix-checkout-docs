<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Service\GeoIp;
use \Salesbeat\Sale\System;

class SbCatalogElementDelivery extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('salesbeat.sale')) die();

        $this->includeComponentTemplate();
    }

    public function onPrepareComponentParams($arParams)
    {
        $moduleId = System::getModuleId();
        $sizeWidth = !empty($arParams['x']) ? ceil($arParams['x']) : Option::get($moduleId, 'default_width');
        $sizeHeight = !empty($arParams['y']) ? ceil($arParams['y']) : Option::get($moduleId, 'default_height');
        $sizeLength = !empty($arParams['z']) ? ceil($arParams['z']) : Option::get($moduleId, 'default_length');
        $weight = !empty($arParams['weight']) ? ceil($arParams['weight']) : Option::get($moduleId, 'default_weight');

        $randId = rand(1, 999);
        $arParams = [
            'token' => !empty($arParams['token']) ? $arParams['token'] : Option::get(System::getModuleId(), 'api_token'),
            'price_to_pay' => isset($arParams['price_to_pay']) ? ceil($arParams['price_to_pay']) : 0,
            'price_insurance' => isset($arParams['price_insurance']) ? ceil($arParams['price_insurance']) : 0,
            'weight' => ceil($weight),
            'x' => ceil($sizeWidth * 0.1),
            'y' => ceil($sizeHeight * 0.1),
            'z' => ceil($sizeLength * 0.1),
            'quantity' => isset($arParams['quantity']) ? ceil($arParams['quantity']) : 1,
            'city_code' => isset($arParams['city_code']) ? $arParams['city_code'] : GeoIp\Manager::getRealIp(),
            'params_by' => isset($arParams['params_by']) ? $arParams['params_by'] : 'params',
            'main_div_id' => isset($arParams['main_div_id']) ? $arParams['main_div_id'] : 'salesbeat-deliveries-' . $randId,
            'id' => $randId
        ];

        return $arParams;
    }
}
