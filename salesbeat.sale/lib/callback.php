<?php

namespace Salesbeat\Sale;

class Callback
{
    /**
     * @param array $arData
     */
    public static function save(array $arData = []): void
    {
        if (isset($arData['delivery_method_type']) && in_array($arData['delivery_method_type'], ['courier', 'post']))
            $arData = self::saveCalc($arData);

        $arData = Tools::utfDecode($arData); // Fix windows-1251
        Storage::getInstance()->set((int)$arData['delivery_id'], $arData);
    }

    /**
     * @param array $arData
     * @return array
     */
    public static function saveCalc(array $arData): array
    {
        $arCity = City::getCity();

        $arBasketSum = Basket::getItemList((int)$arData['order_id']);
        $arDeliveryInfo = Api::getDeliveryPrice(
            '',
            ['city_id' => $arCity['ID']],
            ['delivery_method_id' => $arData['delivery_method_id']],
            [
                'weight' => $arBasketSum['weight'],
                'x' => $arBasketSum['x'],
                'y' => $arBasketSum['y'],
                'z' => $arBasketSum['z']
            ],
            [
                'price_to_pay' => $arBasketSum['price_to_pay'],
                'price_insurance' => $arBasketSum['price_insurance'],
            ]
        );

        return array_merge($arData, [
            'delivery_price' => $arDeliveryInfo['delivery_price'],
            'delivery_days' => $arDeliveryInfo['delivery_days']
        ]);
    }
}