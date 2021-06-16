<?php

namespace Sale\Handlers\Delivery;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentTypeException;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Sale;
use \Salesbeat\Sale\Storage;
use \Salesbeat\Sale\Callback;

Loc::loadMessages(__FILE__);

class Salesbeat2Profile extends Sale\Delivery\Services\Base
{
    protected $parent = null;
    protected static $isProfile = true; // Обработчик является профилем доставки
    protected static $isCalculatePriceImmediately = true; // Обработчик проводит расчеты
    protected static $whetherAdminExtraServicesShow = false; // Обработчик использует дополнительные сервисы

    /**
     * Salesbeat2Profile constructor.
     * @param array $initParams
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws SystemException
     */
    public function __construct(array $initParams)
    {
        if (empty($initParams['PARENT_ID']))
            throw new ArgumentNullException('initParams[PARENT_ID]');

        parent::__construct($initParams);
        $this->parent = Sale\Delivery\Services\Manager::getObjectById($this->parentId);

        if (!$this->parent instanceof parent)
            throw new ArgumentNullException(Loc::getMessage('SB_DELIVERY_VER2_ERROR_NOT_PARENT'));

        if (isset($initParams['PROFILE_ID']) && mb_strlen($initParams['PROFILE_ID']) > 0) {
            $this->methodId = $initParams['PROFILE_ID'];
        } elseif (isset($this->config['MAIN']['METHOD_ID']) && mb_strlen($this->config['MAIN']['METHOD_ID']) > 0) {
            $this->methodId = $this->config['MAIN']['METHOD_ID'];
            $this->methodType = $this->config['MAIN']['METHOD_TYPE'];
        }

        $availableProfiles = $this->parent->getAvailableProfiles();
        if (!empty($availableProfiles[$this->methodId])) {
            $this->name = $availableProfiles[$this->methodId]['name'];
            $this->description = $availableProfiles[$this->methodId]['description'];
            $this->methodType = $availableProfiles[$this->methodId]['type'];
        }

        $this->inheritParams();
    }

    /**
     * Указываем название службы доставки
     * @return string
     */
    public static function getClassTitle(): string
    {
        return Loc::getMessage('SB_DELIVERY_VER2_PROFILE_TITLE');
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
     * Указываем является ли профилем доставки
     * @return bool
     */
    public static function isProfile(): bool
    {
        return self::$isProfile;
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
        return [
            'MAIN' => [
                'TITLE' => Loc::getMessage('SB_DELIVERY_VER2_CONFIG_MAIN_TITLE'),
                'DESCRIPTION' => Loc::getMessage('SB_DELIVERY_VER2_CONFIG_MAIN_DESCRIPTION'),
                'ITEMS' => [
                    'METHOD_NAME' => [
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('SB_DELIVERY_VER2_CONFIG_MAIN_METHOD_NAME_NAME'),
                        'READONLY' => true,
                        'DEFAULT' => $this->name
                    ],
                    'METHOD_ID' => [
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('SB_DELIVERY_VER2_CONFIG_MAIN_METHOD_ID_NAME'),
                        'READONLY' => true,
                        'DEFAULT' => $this->methodId
                    ],
                    'METHOD_TYPE' => [
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('SB_DELIVERY_VER2_CONFIG_MAIN_METHOD_TYPE_NAME'),
                        'READONLY' => true,
                        'DEFAULT' => $this->methodType
                    ]
                ]
            ]
        ];
    }

    /**
     * Defines inheritance behavior.
     * @throws ArgumentNullException
     * @throws SystemException
     */
    protected function inheritParams()
    {
        if (mb_strlen($this->name) === 0)
            $this->name = $this->parent->getName();

        if ((int)$this->logotip === 0)
            $this->logotip = $this->parent->getLogotip();

        if (mb_strlen($this->description) === 0)
            $this->description = $this->parent->getDescription();
    }

    /**
     * Рассчитываем стоимость доставки
     * @param Sale\Shipment $shipment
     * @return Sale\Delivery\CalculationResult
     */
    protected function calculateConcrete(Sale\Shipment $shipment): Sale\Delivery\CalculationResult
    {
        $order = $shipment->getCollection()->getOrder();

        $fields = [
            'order_id' => $order->getId(),
            'delivery_id' => $this->id,
            'delivery_method_type' => $this->methodType
        ];

        if (in_array($this->methodType, ['courier', 'post'])) {
            $fields['delivery_method_name'] = $this->name;
            $fields['delivery_method_id'] = $this->methodId;

            Callback::save($fields);
        } elseif ($this->methodType == 'pvz') {
            Storage::getInstance()->append((int)$this->id, $fields);
        }

        $storage = Storage::getInstance()->getByID((int)$this->id);

        $result = new Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(roundEx(!empty($storage['DELIVERY_PRICE']) ? $storage['DELIVERY_PRICE'] : 0, SALE_VALUE_PRECISION));
        $result->setPeriodDescription(!empty($storage['DELIVERY_DAYS']) ? $storage['DELIVERY_DAYS'] : Loc::getMessage('SB_DELIVERY_VER2_NULL_DEYS'));

        return $result;
    }

    /**
     * Родительская доставка
     * @return Sale\Delivery\Services\Base|Salesbeat2Handler
     */
    public function getParentService()
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getEmbeddedExtraServicesList(): array
    {
        $result = [];

        foreach ($this->parent->getEmbeddedExtraServicesList() as $code => $params)
            $result[$code] = $params;

        return $result;
    }

    /**
     * @param Sale\Shipment $shipment
     * @return bool
     */
    public function isCompatible(Sale\Shipment $shipment): bool
    {
        return $this->calculateConcrete($shipment)->isSuccess();
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