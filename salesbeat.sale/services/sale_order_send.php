<?php

namespace Salesbeat\Sale;

use \Bitrix\Main;
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Type;
use \Bitrix\Sale;

Loc::loadMessages(__FILE__);

if (empty(Loader::includeModule('main'))) return;
if (empty(Loader::includeModule('sale'))) return;
if (empty(Loader::includeModule('salesbeat.sale'))) return;

$moduleId = System::getModuleId();

$request = Application::getInstance()->getContext()->getRequest();
$orderIdList = (array)$request->get('ID');

$orderIdList = array_filter($orderIdList);
if (empty($orderIdList)) return;

if (empty($request->get('grid_id')) && $request->get('grid_id') !== 'tbl_salesbeat_sale') {
    $action = '';
    if (!empty($request->get('action_button'))) $action = $request->get('action_button');
    if (!empty($request->get('action'))) $action = $request->get('action');
    if (!in_array($action, ['send_order', 'send_orders'])) return;
}

foreach ($orderIdList as $orderId) {
    $order = Sale\Order::load($orderId);

    // Получаем первую не системную отгрузку из коллекции
    $orderShipment = [];
    foreach ($order->getShipmentCollection() as $shipment) {
        if ($shipment->isSystem()) continue;

        $orderShipment = $shipment->getFieldValues();
        break;
    }
    unset($shipment);

    // Получаем свойства заказа из коллекции
    $orderProperties = [];
    foreach ($order->getPropertyCollection() as $property) {
        if (empty($property->getField('CODE'))) continue;

        $orderProperties[$property->getField('CODE')] = $property->getValue();
    }

    unset($property);

    // Формируем ФИО получателя
    if (Option::get($moduleId, 'recipient_extend') === 'N') {
        $recipientFullName = $orderProperties[Option::get($moduleId, 'recipient_full_name')];
    } else {
        $recipientFullName = trim(implode(' ', [
            $orderProperties[Option::get($moduleId, 'recipient_last_name')],
            $orderProperties[Option::get($moduleId, 'recipient_first_name')],
            $orderProperties[Option::get($moduleId, 'recipient_middle_name')]
        ]));
    }

    $fields = [
        'secret_token' => Option::get($moduleId, 'secret_token'),
        'test_mode' => false,
        'order' => [
            'delivery_method_code' => $orderProperties['SB_DELIVERY_METHOD_ID'],
            'id' => $orderId,
            'delivery_price' => $orderShipment['BASE_PRICE_DELIVERY'],
            'delivery_from_shop' => false
        ],
        'recipient' => [
            'city_id' => explode('#', $orderProperties['SB_LOCATION'])[0],
            'full_name' => $recipientFullName,
            'phone' => Tools::phoneToTel($orderProperties[Option::get($moduleId, 'recipient_phone')]),
            'email' => $orderProperties[Option::get($moduleId, 'recipient_email')],
        ]
    ];

    if (!empty($orderProperties['SB_PVZ_ID'])) {
        $fields['recipient']['pvz'] = [
            'id' => $orderProperties['SB_PVZ_ID']
        ];
    } else {
        $dateTime = new Type\DateTime();
        $dateTime->add('1 day');

        $fields['recipient']['courier'] = [
            'street' => $orderProperties['SB_STREET'],
            'house' => $orderProperties['SB_HOUSE'],
            'house_block' => $orderProperties['SB_HOUSE_BLOCK'],
            'flat' => $orderProperties['SB_FLAT'],
            'date' => $dateTime->format('Y-m-d')
        ];
    }
    unset($orderProperties);

    $fields['products'] = Basket::getItemList($orderId, true);

    $apiResult = Api::createOrder($fields);
    unset($fields);

    if (!empty($apiResult['success'])) {
        OrderTable::add([
            'ORDER_ID' => $orderId,
            'SHIPMENT_ID' => $orderShipment['ID'],
            'SB_ORDER_ID' => $apiResult['salesbeat_order_id'],
            'TRACK_CODE' => $apiResult['track_code'],
            'SENT_COURIER' => 'N',
        ]);

        $message = Loc::getMessage(
            'SB_SERVICES_SALE_ORDER_SEND_SUCCESS',
            [
                '#ORDER_ID#' => $orderId,
                '#TRACK_CODE#' => $apiResult['track_code']
            ]
        );

        $result = [
            'status' => 'success',
            'message' => $message
        ];
    } else {
        $message = Loc::getMessage(
            'SB_SERVICES_SALE_ORDER_SEND_ERROR',
            [
                '#ORDER_ID#' => $orderId,
                '#ERROR_MESSAGE#' => $apiResult['error_message']
            ]
        );
        foreach ($apiResult['error_list'] as $arError)
            $message .= '<br> ● ' . $arError['message'];

        $result = [
            'status' => 'error',
            'message' => $message
        ];
    }

    if ($request->get('table_id') === 'tbl_sale_order') {
        echo BeginNote(), $result['message'], EndNote();
    } elseif ($request->get('grid_id') === 'tbl_salesbeat_sale') {
        header('Content-Type:application/json; charset=UTF-8');
        echo Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);
    }
}