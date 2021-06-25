<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale;

class SaleOrderEdit
{
    public static function onInit(): array
    {
        return [
            'TABSET' => 'Salesbeat',
            'GetTabs' => ['\Salesbeat\Sale\saleOrderEdit', 'getTabs'],
            'ShowTab' => ['\Salesbeat\Sale\saleOrderEdit', 'showTabs'],
            'Action' => ['\Salesbeat\Sale\saleOrderEdit', 'onSave'],
            'Check' => ['\Salesbeat\Sale\saleOrderEdit', 'onBeforeSave']
        ];
    }

    /**
     * Возвращает массив вкладок
     * @param array $args
     * @return array
     */
    public static function getTabs(array $args): array
    {
        return [
            [
                'DIV' => 'salesbeat',
                'TAB' => 'Salesbeat',
                'TITLE' => Loc::getMessage('SB_ORDER_FORM_TAB_TITLE'),
                'SORT' => 10
            ]
        ];
    }

    /**
     * Отображает вкладку
     * @param string $tabName
     * @param array $args
     * @param array $varsFromForm
     */
    public static function showTabs(string $tabName, array $args, $varsFromForm)
    {
        if ($tabName == 'salesbeat') {
            $GLOBALS['APPLICATION']->IncludeComponent(
                'salesbeat:sale.delivery.widget.admin',
                '.default',
                ['order_id' => $args['ID']],
                false
            );
        }
    }

    /**
     * Вызывается перед onSave
     * Бесполезный метод для формы просмотра
     * @param array $args
     * @return bool
     * @throws \Exception
     */
    public static function onBeforeSave(array $args): bool
    {
        return true;
    }

    /**
     * Вызывается после onBeforeSave при отправке формы
     * Бесполезный метод для формы просмотра
     * @param array $args
     * @return bool
     * @throws \Exception
     */
    public static function onSave(array $args): bool
    {
        $storage = Storage::getInstance('SALESBEAT_ADMIN_DELIVERY')->getByID(1);
        if (empty($storage)) return true;

        $orderId = (int)$args['ID'];
        if (empty($orderId)) return true;

        // Определяем тип доставки
        $methodId = $storage['PVZ_ID'] ? 'pvz' : $storage['DELIVERY_METHOD_ID'];
        $selectedDelivery = self::getSelectedDelivery($methodId);
        if (empty($selectedDelivery)) return true;

        $order = Sale\Order::load($orderId);
        $basket = $order->getBasket();

        // Работаем с отгрузками
        $shipmentCollection = $order->getShipmentCollection();
        foreach ($shipmentCollection as $key => $shipment) {
            if ($shipment->isSystem()) continue; // Игнорируем системную отгрузку

            // Удаляем товары из отгрузки
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach ($basket as $item)
                $shipmentItemCollection->deleteByBasketItem($item);

            // Удаляем отгрузку
            $shipmentCollection->deleteItem($key);
        }

        // Создаем новую отгрузку
        $shipment = $shipmentCollection->createItem();
        $shipment->setFields([
            'DELIVERY_ID' => $selectedDelivery['ID'],
            'DELIVERY_NAME' => $selectedDelivery['NAME'],
            'CURRENCY' => $order->getCurrency()
        ]);
        $shipment->setField('PRICE_DELIVERY', $storage['DELIVERY_PRICE']);
        $shipment->setField('BASE_PRICE_DELIVERY', $storage['DELIVERY_PRICE']);
        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        // Добавляем товары корзины к отгрузке
        foreach ($basket as $item) {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }

        // Устанавливаем свойства
        $propertyCollection = $order->getPropertyCollection();
        foreach ($propertyCollection as $property) {
            $arProperty = $property->getProperty();

            // Очищаем все SB свойства
            if (in_array($arProperty['CODE'], array_column(Internals::getSbPropertyList(), 'CODE')))
                $property->setValue('');

            // Устанавливаем регион
            if ($arProperty['CODE'] === 'SB_LOCATION') {
                $property->setValue(City::transformCityName($storage));
                continue;
            }

            // Приводим код свойства из хранилища к коду свойства в Bitrix
            $propertyCode = str_replace('SB_', '', $arProperty['CODE']);
            if (array_key_exists($propertyCode, $storage))
                $property->setValue($storage[$propertyCode]);
        }
        $order->setFieldNoDemand('PRICE_DELIVERY', $storage['DELIVERY_PRICE']);
        $order->save();

        return true;
    }

    /**
     * @param string $methodId
     * @return array
     */
    private static function getSelectedDelivery(string $methodId): array
    {
        $sbDeliveryList = System::getDeliveryList();
        foreach ($sbDeliveryList as $sbDelivery) {
            if ($sbDelivery['CLASS_NAME'] === '\Sale\Handlers\Delivery\SalesbeatHandler') {
                return $sbDelivery;
            } elseif ($sbDelivery['CLASS_NAME'] === '\Sale\Handlers\Delivery\Salesbeat2Profile') {
                if ($methodId !== $sbDelivery['CONFIG']['MAIN']['METHOD_ID']) continue;

                return $sbDelivery;
            }
        }

        return [];
    }
}