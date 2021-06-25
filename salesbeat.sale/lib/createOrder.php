<?php

namespace Salesbeat\Sale;

use \Bitrix\Main;
use \Bitrix\Main\Context;
use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Sale;

class CreateOrder
{
    private $siteId = 0;
    private $currencyCode = 'RUB';

    private $order = null;

    public function __construct()
    {
        $this->siteId = Context::getCurrent()->getSite();
        $this->currencyCode = CurrencyManager::getBaseCurrency();
    }

    /**
     * Начинаем процесс создания заказа
     * @param array $data
     */
    public function create(array $data)
    {
        $this->order = Sale\Order::create(
            $this->siteId,
            !empty($data['customer_id']) ? $data['customer_id'] : \CSaleUser::GetAnonymousUserID()
        );
        $this->order->setPersonTypeId(1);

        // Создаем корзину
        $basket = $this->createBasket((string)$data['shop_cart_id'], (array)$data['products']);

        // Привязываем корзину
        $this->order->setBasket($basket);

        // Создаем отгрузку
        $shipment = $this->createShipment(
            (int)$data['delivery']['delivery_id'],
            $data['delivery']['delivery_price']
        );

        // Добавляем товары корзины к отгрузке
        $this->setBasketToShipment($basket, $shipment);

        // Создаем платежную систему
        $this->createPaySystem(
            (int)$data['payment']['payment_id'],
            $data['payment']['payment_price']
        );

        // Устанавливаем текущий курс
        $this->order->setField('CURRENCY', $this->currencyCode);

        $this->order->doFinalAction(true);

        // Устанавливаем свойства
        $this->setProperties($data);

        // Делаем перерасчет
        $this->order->refreshData();

        // Сохраняем заказ
        $this->order->save();
        return $this->order->getId();
    }

    /**
     * Создаем корзину
     * @param string $fuser
     * @param array $products
     * @return mixed
     */
    private function createBasket(string $fuser, array $products)
    {
        if (!empty($fuser)) {
            $basket = \Bitrix\Sale\Basket::loadItemsForFUser($fuser, $this->siteId);

            // Получаем товары в текущей корзине и удаляем их
            $basketItems = $basket->getBasketItems();
            foreach ($basketItems as $key => $basketItem)
                $basket->deleteItem($key);
        } else {
            $basket = Sale\Basket::create($this->siteId);
        }

        // Добавляем товары в корзину
        foreach ($products as $product) {
            $item = $basket->createItem('catalog', $product['id']);
            $item->setFields([
                'QUANTITY' => $product['quantity'],
                'PRICE' => $product['price'],
                'CUSTOM_PRICE' => 'Y',
                'CURRENCY' => $this->currencyCode,
                'LID' => $this->siteId,
                'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvide',
            ]);
        }

        return $basket;
    }

    /**
     * Создаем отгрузку
     * @param int $deliveryId
     * @param string $deliveryPrice
     * @return mixed
     */
    private function createShipment(int $deliveryId, string $deliveryPrice)
    {
        $shipmentCollection = $this->order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();

        $selectedDelivery = Sale\Delivery\Services\Manager::getById($deliveryId);
        $shipment->setFields([
            'DELIVERY_ID' => $selectedDelivery['ID'],
            'DELIVERY_NAME' => $selectedDelivery['NAME'],
            'CURRENCY' => $this->currencyCode
        ]);
        $shipment->setField('PRICE_DELIVERY', $deliveryPrice);
        $shipment->setField('BASE_PRICE_DELIVERY', $deliveryPrice);
        return $shipment;
    }

    /**
     * Привязываем корзину к отгрузке
     * @param $basket
     * @param $shipment
     */
    private function setBasketToShipment($basket, $shipment)
    {
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($basket as $item) { // Добавляем товары корзины к отгрузке
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }
    }

    /**
     * Создаем платежную системы
     * @param int $paySystemId
     * @param string $paySystemPrice
     * @return mixed
     */
    private function createPaySystem(int $paySystemId, string $paySystemPrice)
    {
        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->createItem();

        $selectedPaySystem = Sale\PaySystem\Manager::getObjectById($paySystemId);
        $payment->setFields([
            'PAY_SYSTEM_ID' => $selectedPaySystem->getField('PAY_SYSTEM_ID'),
            'PAY_SYSTEM_NAME' => $selectedPaySystem->getField('NAME'),
            'SUM' => $paySystemPrice,
        ]);
        return $payment;
    }

    /**
     * Устанавливаем пользовательские свойства
     * @param array $data
     */
    private function setProperties(array $data)
    {
        $deliveryId = (int)$data['delivery']['delivery_id'];

        Storage::getInstance()->set($deliveryId, $data['delivery']);
        $storage = Storage::getInstance()->getByID($deliveryId);

        $propertyCollection = $this->order->getPropertyCollection();
        foreach ($propertyCollection as $property) {
            $arProperty = $property->getProperty();
            $propertyCode = str_replace('SB_', '', $arProperty['CODE']);

            if ($arProperty['CODE'] === 'FIO') {
                $name = [];
                if (!empty($data['last_name'])) $name[] = $data['last_name'];
                if (!empty($data['first_name'])) $name[] = $data['first_name'];
                if (!empty($data['middle_name'])) $name[] = $data['middle_name'];

                $property->setValue(implode ( ' ', $name));
            }

            if ($arProperty['CODE'] === 'EMAIL')
                $property->setValue($data['email']);

            if ($arProperty['CODE'] === 'PHONE')
                $property->setValue($data['phone']);

            // Устанавливаем регион
            if ($arProperty['CODE'] === 'SB_LOCATION') {
                $property->setValue(City::transformCityName($storage));
                continue;
            }

            if (array_key_exists($propertyCode, $storage))
                $property->setValue($storage[$propertyCode]);
        }
    }
}