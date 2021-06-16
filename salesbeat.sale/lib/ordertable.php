<?php

namespace Salesbeat\Sale;

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class OrderTable extends Main\Entity\DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'sb_delivery_order';
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SB_TABLE_ID_TITLE'),
            ],
            'ORDER_ID' => [
                'data_type' => 'string',
                'required' => true,
                'validation' => [__CLASS__, 'validateOrderId'],
                'title' => Loc::getMessage('SB_TABLE_ORDER_ID_TITLE'),
            ],
            'SHIPMENT_ID' => [
                'data_type' => 'string',
                'required' => true,
                'validation' => [__CLASS__, 'validateShipmentId'],
                'title' => Loc::getMessage('SB_TABLE_SHIPMENT_ID_TITLE'),
            ],
            'SB_ORDER_ID' => [
                'data_type' => 'string',
                'required' => true,
                'validation' => [__CLASS__, 'validateSbOrderId'],
                'title' => Loc::getMessage('SB_TABLE_SB_ORDER_ID_TITLE'),
            ],
            'TRACK_CODE' => [
                'data_type' => 'string',
                'required' => true,
                'validation' => [__CLASS__, 'validateTrackCode'],
                'title' => Loc::getMessage('SB_TABLE_TRACK_CODE_TITLE'),
            ],
            'DATE_ORDER' => [
                'data_type' => 'datetime',
                'required' => true,
                'title' => Loc::getMessage('SB_TABLE_DATE_ORDER_TITLE'),
            ],
            'SENT_COURIER' => [
                'data_type' => 'string',
                'required' => false,
                'validation' => [__CLASS__, 'validateSentCourier'],
                'title' => Loc::getMessage('SB_TABLE_SENT_COURIER_TITLE'),
            ],
            'DATE_COURIER' => [
                'data_type' => 'datetime',
                'required' => false,
                'title' => Loc::getMessage('SB_TABLE_DATE_COURIER_TITLE'),
            ],
            'TRACKING_STATUS' => [
                'data_type' => 'string',
                'required' => false,
                'validation' => [__CLASS__, 'validateTrackingStatus'],
                'title' => Loc::getMessage('SB_TABLE_TRACKING_STATUS_TITLE'),
            ],
            'DATE_TRACKING' => [
                'data_type' => 'datetime',
                'required' => false,
                'title' => Loc::getMessage('SB_TABLE_DATE_TRACKING_TITLE'),
            ]
        ];
    }

    /**
     * @return array
     */
    public static function validateOrderId(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 100),
        ];
    }

    /**
     * @return array
     */
    public static function validateShipmentId(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 100),
        ];
    }

    /**
     * @return array
     */
    public static function validateSbOrderId(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 100),
        ];
    }

    /**
     * @return array
     */
    public static function validateTrackCode(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 100),
        ];
    }

    /**
     * @return array
     */
    public static function validateSentCourier(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 1),
        ];
    }

    /**
     * @return array
     */
    public static function validateTrackingStatus(): array
    {
        return [
            new Main\Entity\Validator\Length(null, 255),
        ];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public static function add(array $data)
    {
        if (empty($data['DATE_COURIER']))
            $data['DATE_ORDER'] = new Main\Type\DateTime();

        return parent::add($data);
    }

    /**
     * @param int $primary
     * @param array $data
     * @return mixed
     */
    public static function update(int $primary, array $data)
    {
        if (!$data['DATE_COURIER'])
            $data['DATE_COURIER'] = new Main\Type\DateTime();

        return parent::update($primary, $data);
    }

    /**
     * @param int $primary
     * @return mixed
     */
    public static function delete(int $primary)
    {
        return parent::delete($primary);
    }
}