<?php

namespace Sale\Handlers\Delivery;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentTypeException;
use \Bitrix\Sale;
use \Salesbeat\Sale\Api;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('salesbeat.sale')) die();

Loader::registerAutoLoadClasses(
    null,
    [ '\Sale\Handlers\Delivery\Salesbeat2Profile' => '/local/php_interface/include/sale_delivery/salesbeat2/profile.php']
);

class Salesbeat2Handler extends Sale\Delivery\Services\Base
{
    protected static $canHasProfiles = true; // Обработчик содержит профиль доставки
    protected static $isCalculatePriceImmediately = false; // Обработчик проводит расчеты
    protected static $whetherAdminExtraServicesShow = false; // Обработчик использует дополнительные сервисы

    /**
     * Salesbeat2Handler constructor.
     * @param array $initParams
     * @throws SystemException
     * @throws ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * Указываем название службы доставки
     * @return string
     */
    public static function getClassTitle(): string
    {
        return Loc::getMessage('SB_DELIVERY_VER2_TITLE');
    }

    /**
     * Указываем описание службы доставки
     * @return string
     */
    public static function getClassDescription(): string
    {
        return '';
    }

    /**
     * Указываем существуют ли профили доставки
     * @return bool
     */
    public static function canHasProfiles(): bool
    {
        return self::$canHasProfiles;
    }

    /**
     * Указываем проводит ли расчеты доставка
     * @return bool
     */
    public function isCalculatePriceImmediately(): bool
    {
        return self::$isCalculatePriceImmediately;
    }

    /**
     * Указываем используются ли дополнительные сервисы
     * @return bool
     */
    public static function whetherAdminExtraServicesShow(): bool
    {
        return self::$whetherAdminExtraServicesShow;
    }

    /**
     * Получаем настройки службы доставки
     * @return array
     */
    protected function getConfigStructure(): array
    {
        return [];
    }

    /**
     * Рассчитываем стоимость доставки
     * @param Sale\Shipment $shipment
     * @throws SystemException
     * @return void
     */
    protected function calculateConcrete(Sale\Shipment $shipment)
    {
        throw new SystemException(Loc::getMessage('SB_DELIVERY_VER2_NO_CALC'));
    }

    /**
     * Указываем именя классов для профилей
     * @return array
     */
    public static function getChildrenClassNames()
    {
        return ['\Sale\Handlers\Delivery\Salesbeat2Profile'];
    }

    /**
     * Список профилей code => name
     * @param $shipment
     * @return array
     */
    public function getProfilesList($shipment = []): array
    {
        $result = [];

        $availableProfiles = $this->getAvailableProfiles();
        if (empty($availableProfiles)) return $result;

        foreach ($availableProfiles as $id => $availableProfile)
            $result[$id] = $availableProfile['name'];

        return $result;
    }

    /**
     * Получаем список всех доступных профилей
     * @return array
     */
    public function getAvailableProfiles(): array
    {
        $result = [];

        $apiDeliveryList = Api::getListDeliveries('');
        if (empty($apiDeliveryList['success'])) return $result;

        foreach ($apiDeliveryList['delivery_methods'] as $apiDelivery) {
            if (in_array($apiDelivery['type'], ['courier', 'post'])) {
                $result[$apiDelivery['id']] = $apiDelivery;
            } elseif (empty($result['pvz']) && $apiDelivery['type'] == 'pvz') {
                $result['pvz'] = [
                    'name' => Loc::getMessage('SB_DELIVERY_VER2_PROFILE_PVZ_NAME'),
                    'id' => 'pvz',
                    'type' => 'pvz'
                ];
            }
        }

        return $result;
    }

    /**
     * Вызываем после добавления доставки
     * @param int $serviceId
     * @param array $fields
     * @return bool
     * @throws \Exception
     */
    public static function onAfterAdd(int $serviceId, array $fields = []): bool
    {
        $result = Sale\Delivery\Services\Manager::update($serviceId, ['CODE' => $serviceId]);
        return $result->isSuccess();
    }
}