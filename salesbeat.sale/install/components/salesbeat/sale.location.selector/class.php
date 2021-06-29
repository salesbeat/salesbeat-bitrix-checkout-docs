<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Salesbeat\Sale\Api;
use \Salesbeat\Sale\City;

class SbSaleLocationSelector extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('salesbeat.sale')) die();

        $this->includeComponentTemplate();
    }

    public function onPrepareComponentParams($arParams)
    {
        $isAdmin = ((defined('ADMIN_SECTION') && ADMIN_SECTION == true) || $arParams['ADMIN_MODE'] == 'Y');

        if (!empty(City::getCity())) {
            $arParams['INPUT_VALUE'] = implode('#', City::getCity());
        } else if ($arParams['INPUT_VALUE'] === '#' && !$isAdmin) {
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
