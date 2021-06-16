<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Context;
use \Bitrix\Main\Config\Option;
use \Bitrix\Sale;

class Basket
{
    /**
     * @param int $orderId
     * @param bool $isSendOrder
     * @return array
     */
    public static function getItemList(int $orderId = 0, bool $isSendOrder = false): array
    {
        $moduleId = System::getModuleId();

        if ($orderId > 0) {
            $basket = Sale\Order::load($orderId)->getBasket();
            $basketItems = $basket->getBasketItems(); // Получаем корзину
        } else {
            $basket = Sale\Basket::loadItemsForFUser(
                Sale\Fuser::getId(),
                Context::getCurrent()->getSite()
            );

            $basketItems = $basket->getBasketItems(); // Получаем корзину
            if (!empty($basketItems)) {
                // Применяем скидки и правила работы с корзиной
                $discountContext = new Sale\Discount\Context\Fuser($basket->getFUserId());
                $discount = Sale\Discount::buildFromBasket($basket, $discountContext);

                $calculateResult = $discount->calculate();
                if (!$calculateResult->isSuccess())
                    Tools::vardump($calculateResult->getErrorMessages());

                $calculateData = $calculateResult->getData();
                if (isset($calculateData['BASKET_ITEMS'])) {
                    $calculateResult = $basket->applyDiscount($calculateData['BASKET_ITEMS']);
                    if (!$calculateResult->isSuccess())
                        Tools::vardump($calculateResult->getErrorMessages());
                }
            }
        }
        unset($basket);

        // Получаем значения по умолчанию
        $defaultWidth = Option::get($moduleId, 'default_width');
        $defaultHeight = Option::get($moduleId, 'default_height');
        $defaultLength = Option::get($moduleId, 'default_length');
        $defaultWeight = Option::get($moduleId, 'default_weight');

        $basketItemList = [];
        foreach ($basketItems as $basketItem) {
            $sizes = unserialize($basketItem->getField('DIMENSIONS'));

            $sizeWidth = !empty($sizes['WIDTH']) ? ceil($sizes['WIDTH']) : $defaultWidth;
            $sizeHeight = !empty($sizes['HEIGHT']) ? ceil($sizes['HEIGHT']) : $defaultHeight;
            $sizeLength = !empty($sizes['LENGTH']) ? ceil($sizes['LENGTH']) : $defaultLength;
            $weight = $basketItem->getWeight() > 0 ? $basketItem->getWeight() : $defaultWeight;

            $price = $basketItem->getFields()->getOriginalValues();
            $price = $price['PRICE'] > 0 ? $price['PRICE'] : $basketItem->getPrice();

            $arItem = [
                'price_to_pay' => ceil($price),
                'price_insurance' => ceil($price),
                'weight' => ceil($weight),
                'x' => ceil($sizeWidth * 0.1),
                'y' => ceil($sizeHeight * 0.1),
                'z' => ceil($sizeLength * 0.1),
                'quantity' => ceil($basketItem->getQuantity()),
            ];

            if ($isSendOrder) {
                $arItem['id'] = $basketItem->getProductId();
                $arItem['name'] = $basketItem->getField('NAME');
            }

            $basketItemList[] = $arItem;
        }

        return $basketItemList;
    }

    /**
     * @param int $orderId
     * @return array
     */
    public static function getSumItem(int $orderId = 0): array
    {
        $basket = [];

        $basketItemList = self::getItemList($orderId);
        foreach ($basketItemList as $basketItem) {
            $basket['price_to_pay'] += $basketItem['price_to_pay'] * $basketItem['quantity'];
            $basket['price_insurance'] += $basketItem['price_insurance'] * $basketItem['quantity'];
            $basket['weight'] += $basketItem['weight'] * $basketItem['quantity'];
        }

        return array_merge($basket, Api::packer('', $basketItemList));
    }
}