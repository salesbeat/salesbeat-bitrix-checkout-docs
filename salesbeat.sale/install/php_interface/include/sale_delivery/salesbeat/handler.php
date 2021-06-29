<?php

namespace Sale\Handlers\Delivery;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentTypeException;
use \Bitrix\Sale;
use \Salesbeat\Sale\City;
use \Salesbeat\Sale\Storage;
use \Salesbeat\Sale\Internals;

Loc::loadMessages(__FILE__);

class SalesbeatHandler extends Sale\Delivery\Services\Base
{
    protected static $canHasProfiles = false; // Обработчик содержит профиль доставки
    protected static $isCalculatePriceImmediately = true; // Обработчик проводит расчеты
    protected static $whetherAdminExtraServicesShow = false; // Обработчик использует дополнительные сервисы

    /**
     * SalesbeatHandler constructor.
     * @param array $initParams
     * @throws SystemException
     * @throws ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);

        $this->methodType = 'widget';
    }

    /**
     * Указываем название службы доставки
     * @return string
     */
    public static function getClassTitle(): string
    {
        return Loc::getMessage('SB_DELIVERY_VER1_TITLE');
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
     * @return Sale\Delivery\CalculationResult
     */
    protected function calculateConcrete(Sale\Shipment $shipment): Sale\Delivery\CalculationResult
    {
        $fields = [
            'delivery_id' => $this->id,
            'delivery_method_type' => $this->methodType
        ];
        Storage::getInstance()->append((int)$this->id, $fields);

        $storage = Storage::getInstance()->getByID((int)$this->id);

        $result = new Sale\Delivery\CalculationResult();
        $result->setDescription($this->getDescriptionDelivery($storage));
        $result->setDeliveryPrice(roundEx(!empty($storage['DELIVERY_PRICE']) ? $storage['DELIVERY_PRICE'] : 0, SALE_VALUE_PRECISION));
        $result->setPeriodDescription(!empty($storage['DELIVERY_DAYS']) ? $storage['DELIVERY_DAYS'] : Loc::getMessage('SB_DELIVERY_VER1_NULL_DEYS'));

        return $result;
    }

    /**
     * Отображаем в описании доставки данные расчетов
     * @param array $storage
     * @return string
     */
    private function getDescriptionDelivery(array $storage): string
    {
        $result = '';
        $sbPropertyList = Internals::getSbPropertyList();

        if (empty($storage)) return $result;
        if (empty($sbPropertyList)) return $result;

        // Выводим кнопку
        $descButton = isset($storage['DELIVERY_PRICE']) ?
            Loc::getMessage('SB_DELIVERY_VER1_DESC_BUTTON_2') :
            Loc::getMessage('SB_DELIVERY_VER1_DESC_BUTTON_1');

        $result = '<a href="#" id="sb_widget" class="btn btn-default" data-city-code="' . City::getCity()['ID'] . '">' . $descButton . '</a>';

        $result .= '<ul class="bx-soa-pp-list">';
        foreach ($sbPropertyList as $key => $property) {
            if (in_array($key, ['CITY_CODE', 'PVZ_ID', 'DELIVERY_METHOD_ID'])) continue;

            if ($key == 'LOCATION') {
                $value = City::getCity()['NAME'];
            } else {
                $value = $storage[$key];
            }

            if (!empty($value))
                $result .= '<li>
    <div class="bx-soa-pp-list-termin">' . $property['NAME'] . ':</div>
    <div class="bx-soa-pp-list-description">' . $value . '</div>
</li>';
        }
        $result .= '</ul>';

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