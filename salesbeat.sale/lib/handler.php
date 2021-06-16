<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Event;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Sale\Internals\Input\Manager;

class Handler
{
    /**
     * Вызывается в выполняемой части пролога сайта (после события OnPageStart).
     */
    public static function OnBeforeProlog()
    {
        \CJSCore::RegisterExt('input', ['js' => '/bitrix/js/sale/input.js']);
        \CJSCore::RegisterExt('salesbeat.input', ['js' => '/bitrix/js/salesbeat.sale/input.js']);
        \CJSCore::Init(['input', 'salesbeat.input']);

        if ($GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/sale_order.php') {
            $_REQUEST['table_id'] = 'tbl_sale_order';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/services/salesbeat.sale/sale_order_send.php';
        }

        // АБ Тест
        if ($_REQUEST['force_checkout'] === 'salesbeat') {
            System::setAbTest('salesbeat');
        } else if (!empty($_REQUEST['force_checkout'])) {
            System::setAbTest('bitrix');
        } else if (empty($_SESSION['SB_AB_TEST'])) {
            $abTest = (int)Option::get(System::getModuleId(), 'order_ab_test');
            $result = rand(1, 100) <= $abTest ? 'salesbeat' : 'bitrix';
            System::setAbTest($result);
        }
    }

    /**
     * Step: 1
     * Вызывается после получения всех свойств заказа (из значений по умолчанию, из профиля или уже заполненных клиентом)
     * @param array arUserResult Массив содержащий текущие выбранные пользовательские данные
     * @param object $request Объект \Bitrix\Main\HttpRequest
     * @param array $arParams Объект Массив параметров компонента
     * @param array $arResult Массив компонента
     */
    public static function OnSaleComponentOrderProperties(&$arUserResult, $request, &$arParams, &$arResult)
    {
        $locationPropId = Internals::getPropertyIdByCode((int)$arUserResult['PERSON_TYPE_ID'], 'SB_LOCATION');

        if (!empty($locationPropId)) {
            $storage = Storage::getInstance()->getByID((int)$arUserResult['DELIVERY_ID']);

            $currentCity = '';
            if (!empty($storage['CITY_CODE'])) {
                $currentCity = City::transformCityName($storage);
                $arUserResult['ORDER_PROP'][$locationPropId] = $currentCity;

                City::setCity($currentCity);
            }

            $propertyCity = $arUserResult['ORDER_PROP'][$locationPropId];
            if ($currentCity !== $propertyCity) {
                Storage::getInstance()->delete();
                City::setCity($propertyCity);
            }
        }
    }

    /**
     * Step: 2
     * Вызывается после создания и расчета объекта заказа
     * @param object $order Объект заказа \Bitrix\Sale\Order
     * @param array $arUserResult Массив компонента, содержащий текущие выбранные пользовательские данные
     * @param object $request Объект \Bitrix\Main\HttpRequest
     * @param array $arParams Массив параметров компонента
     * @param array $arResult Массив arResult компонента
     * @param array $arDeliveryServiceAll Массив доступных по ограничениям служб доставки
     * @param array $arPaySystemServiceAll Массив доступных по ограничениям платежных систем
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \ReflectionException
     */
    public static function OnSaleComponentOrderCreated($order, &$arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $storage = Storage::getInstance()->getByID((int)$arUserResult['DELIVERY_ID']);
        $basketSum = Basket::getSumItem();

        // Получаем справочник служб доставки в населенном пункте
        $resultDelivery = Api::getDeliveryByCity(
            '',
            ['id' => City::getCity()['ID']],
            [
                'weight' => $basketSum['weight'],
                'x' => $basketSum['x'],
                'y' => $basketSum['y'],
                'z' => $basketSum['z'],
            ],
            [
                'price_to_pay' => $basketSum['price_to_pay'],
                'price_insurance' => $basketSum['price_insurance'],
            ]
        );
        unset($basketSum);

        $resultDeliveryMethod = [];
        if (!empty($resultDelivery['success'])) {
            $deliveryMethods = $resultDelivery['delivery_methods'];
            unset($resultDelivery);

            // Удаляем недоступные способы доставки
            foreach ($arDeliveryServiceAll as $key => $deliveryService) {
                $flag = false;

                $deliveryServiceConfig = Tools::accessProtected($deliveryService, 'config');
                if (in_array($deliveryServiceConfig['MAIN']['METHOD_TYPE'], ['courier', 'post'])) {
                    foreach ($deliveryMethods as $deliveryMethod) {
                        $flag = $deliveryServiceConfig['MAIN']['METHOD_ID'] == $deliveryMethod['id'];
                        if ($flag) break;
                    }

                    if (!$flag) unset($arDeliveryServiceAll[$key]);
                } elseif ($deliveryServiceConfig['MAIN']['METHOD_TYPE'] == 'pvz') {
                    foreach ($deliveryMethods as $deliveryMethod) {
                        $flag = $deliveryMethod['type'] == 'pvz';
                        if ($flag) break;
                    }

                    if (!$flag) unset($arDeliveryServiceAll[$key]);
                }
            }
            unset($key, $deliveryService, $deliveryMethod, $flag);

            // Отбираем выбранную доставку из полученного справочника доставок
            foreach ($deliveryMethods as $deliveryMethod) {
                if ($deliveryMethod['id'] == $storage['DELIVERY_METHOD_ID'])
                    $resultDeliveryMethod = $deliveryMethod;
            }
            unset($deliveryMethods, $deliveryMethod);
        }

        // Получаем справочник платежных систем для выбранной доставки
        $resultPaySystems = [];
        if (!empty($resultDeliveryMethod))
            $resultPaySystems = [
                'success' => true,
                'pay_system_types' => !empty($resultDeliveryMethod['payments']) && is_array($resultDeliveryMethod['payments']) ?
                    $resultDeliveryMethod['payments'] : []
            ];

        if (!empty($resultPaySystems['success'])) {
            $paySystemTypes = $resultPaySystems['pay_system_types'];
            unset($resultPaySystems);

            // Фильтруем на доступность платежных систем из правочника
            $availablePaySystemList = array_keys($paySystemTypes);
            unset($paySystemTypes);

            // Получаем данные из настроек модуля
            $paySystemsCash = unserialize(Option::get(System::getModuleId(), 'pay_systems_cash'));
            $paySystemsCard = unserialize(Option::get(System::getModuleId(), 'pay_systems_card'));
            $paySystemsOnline = unserialize(Option::get(System::getModuleId(), 'pay_systems_online'));

            // Фильтруем платежные системы из системы на доступность
            foreach ($arPaySystemServiceAll as &$paySystem) {
                $paySystem['SB_CODE'] = $paySystem['ID'];

                if (in_array($paySystem['ID'], $paySystemsCash))
                    $paySystem['SB_CODE'] = 'cash';

                if (in_array($paySystem['ID'], $paySystemsCard))
                    $paySystem['SB_CODE'] = 'card';

                if (in_array($paySystem['ID'], $paySystemsOnline))
                    $paySystem['SB_CODE'] = 'online';

                if (!in_array($paySystem['SB_CODE'], $availablePaySystemList))
                    unset($arPaySystemServiceAll[$paySystem['ID']]); // Удаляем все не нужные платежные системы
            }
            unset($paySystem, $paySystemsCash, $paySystemsCard, $paySystemsOnline, $availablePaySystemList);

            // Если платежная система не выбрана, тогда выбераем самую первую
            if (!array_key_exists($arUserResult['PAY_SYSTEM_ID'], $arPaySystemServiceAll))
                $arUserResult['PAY_SYSTEM_ID'] = key($arPaySystemServiceAll);
        }
    }

    /**
     * Step: 3
     * Вызывается после инициализации массива с данными для javascript-обработчика
     * @param array $arResult Массив arResult компонента
     * @param array $arParams Массив параметров компонента
     */
    public static function OnSaleComponentOrderJsData(&$arResult, &$arParams)
    {
        // Определяем выбранную платежную систему
        $checkedPaySystemId = 0;
        foreach ($arResult['JS_DATA']['PAY_SYSTEM'] as $paySystem) {
            if ($paySystem['CHECKED'] == 'Y')
                $checkedPaySystemId = $paySystem['ID'];
        }

        // Если платежная система не выбрана, тогда выбираем самую первую
        if (empty($checkedPaySystemId)) {
            $firsPaySystemId = key($arResult['JS_DATA']['PAY_SYSTEM']);
            $arResult['JS_DATA']['PAY_SYSTEM'][$firsPaySystemId]['CHECKED'] = 'Y';

            if (!empty($arResult['JS_DATA']['DELIVERY'][$firsPaySystemId]))
                $checkedPaySystemId = (int)$arResult['JS_DATA']['PAY_SYSTEM'][$firsPaySystemId]['ID'];
        }

        // Определяем выбранный способ доставки
        $checkedDeliveryId = 0;
        foreach ($arResult['JS_DATA']['DELIVERY'] as $delivery) {
            if ($delivery['CHECKED'] == 'Y')
                $checkedDeliveryId = $delivery['ID'];
        }

        // Если способ доставки не выбран, тогда выбераем самую первую
        if (empty($checkedDeliveryId)) {
            $firsDeliveryId = key($arResult['JS_DATA']['DELIVERY']);
            $arResult['JS_DATA']['DELIVERY'][$firsDeliveryId]['CHECKED'] = 'Y';

            if (!empty($arResult['JS_DATA']['DELIVERY'][$firsDeliveryId]))
                $checkedDeliveryId = (int)$arResult['JS_DATA']['DELIVERY'][$firsDeliveryId]['ID'];
        }

        // Определяем группу свойств заказа Salesbeat
        $propertyGroupId = 0;
        foreach ($arResult['JS_DATA']['ORDER_PROP']['groups'] as $group) {
            if ($group['NAME'] == System::getPropertyGroupName()) {
                $propertyGroupId = $group['ID'];
                break;
            }
        }

        // Устанавливаем значения из хранилища
        $storage = Storage::getInstance()->getByID($checkedDeliveryId);
        foreach ($arResult['JS_DATA']['ORDER_PROP']['properties'] as &$property) {
            if ($property['PROPS_GROUP_ID'] != $propertyGroupId) continue;

            // Очищаем все SB свойства
            if (in_array($property['CODE'], array_column(Internals::getSbPropertyList(), 'CODE')))
                $property['VALUE'] = [''];

            // Устанавливаем регион
            if ($property['CODE'] == 'SB_LOCATION') {
                $property['VALUE'] = [City::transformCityName($storage)];
                continue;
            }

            // Приводим код свойства из хранилища к коду свойства в Bitrix
            $propertyCode = str_replace('SB_', '', $property['CODE']);
            if (array_key_exists($propertyCode, $storage))
                $property['VALUE'] = [$storage[$propertyCode]]; // Устанавливаем значение из хранилища
        }
    }

    /**
     * Step: 4
     * Вызывается после формирования всех данных компонента на этапе заполнения формы заказа, может быть использовано
     * для модификации данных. Аналог устаревшего события OnSaleComponentOrderOneStepProcess
     * @param object $order Объект заказа \Bitrix\Sale\Order
     * @param array $arUserResult Массив arUserResult компонента, содержащий текущие выбранные пользовательские данные
     * @param object $request Объект \Bitrix\Main\HttpRequest
     * @param array &$arParams Массив параметров компонента
     * @param array $arResult Массив arUserResult Массив arResult компонента
     */
    public static function OnSaleComponentOrderResultPrepared($order, &$arUserResult, $request, &$arParams, &$arResult)
    {
        $GLOBALS['APPLICATION']->IncludeComponent(
            'salesbeat:sale.delivery.widget',
            Option::get(System::getModuleId(), 'delivery_template'),
            [],
            false
        );
    }

    /**
     * Вызывается перед отправкой ajax-ответа.
     * @param array &$arResult Массив данных для ответа ajax'ом.
     */
    public static function OnSaleComponentOrderShowAjaxAnswer(&$arResult)
    {
        // Tools::vardump($arResult);
    }

    /**
     * Происходит в конце сохранения, когда заказ и все связанные сущности уже сохранены
     * @param Event $event
     */
    public static function OnSaleOrderSaved(Event $event)
    {
        $order = $event->getParameter('ENTITY'); // Объект заказа
        $oldValues = $event->getParameter('VALUES'); // Старые значения полей заказа
        $isNew = $event->getParameter('IS_NEW'); // True - если заказ новый, false - если нет

        if ($isNew) {
            Storage::getInstance()->delete();
            System::setAbTest('');
        }
    }

    /**
     * @param Event $event
     * @throws SystemException
     */
    public function registerInputTypes(Event $event)
    {
        Manager::register('SBLOCATION', [
            'CLASS' => __NAMESPACE__ . '\SbLocationInput',
            'NAME' => Loc::getMessage('SB_ORDER_PROP_TYPE_LOCATION_NAME')
        ]);
    }

    /**
     * Модифицирование формы заказа в админке
     * @return array
     */
    public function OnAdminSaleOrderEdit(): array
    {
        $saleOrderEdit = new SaleOrderEdit;
        return $saleOrderEdit->onInit();
    }

    /**
     * Модифицирование списка заказа в админке
     * @param object $list
     */
    public function OnAdminSaleOrderList($list)
    {
        $saleOrderList = new SaleOrderList;
        $saleOrderList->onInit($list);
    }
}