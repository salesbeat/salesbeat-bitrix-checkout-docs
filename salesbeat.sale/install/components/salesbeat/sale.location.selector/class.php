<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Salesbeat\Sale\Api;

class SbSaleLocationSelector extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('salesbeat.sale')) die();

        $this->includeComponentTemplate();
    }

    public function onPrepareComponentParams($arParams)
    {
        if (!$arParams['INPUT_VALUE'] && !CSite::InDir('/bitrix/')) {
            $arSbResult = Api::getCities('', ['ip' => $_SERVER['REMOTE_ADDR']]);

            if (!empty($arSbResult['success'])) {
                $arCity = $arSbResult['cities'][0];
                $arParams['INPUT_VALUE'] = $arCity['id'] . '#' . $arCity['short_name'] . '. ' . $arCity['name'] . ', ' . $arCity['region_name'];
            }
        }

        $arValue = explode('#', htmlspecialcharsbx($arParams['INPUT_VALUE']));
        $arParams['CITY'] = [
            'NAME' => $arValue[1],
            'CODE' => $arValue[0]
        ];
        $arParams['main_div_id'] = $arParams['main_div_id'] ?: 'sb-location-' . rand(1, 999);

        return $arParams;
    }
}
