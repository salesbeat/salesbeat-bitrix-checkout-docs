<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Context;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Sale;

class System
{
    protected static $MODULE_ID = 'salesbeat.sale';
    protected static $PROPERTY_GROUP_NAME = 'Salesbeat';

    /**
     * @return string
     */
    public static function getModuleId(): string
    {
        return self::$MODULE_ID;
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        $module = \CModule::CreateModuleObject(self::$MODULE_ID);
        return is_object($module) ? $module->MODULE_NAME : '';
    }

    /**
     * @return string
     */
    public static function getModuleVersion(): string
    {
        $objModule = \CModule::CreateModuleObject(self::$MODULE_ID);
        return is_object($objModule) ? $objModule->MODULE_VERSION : '';
    }

    /**
     * @return string
     */
    public static function getPropertyGroupName(): string
    {
        return self::$PROPERTY_GROUP_NAME;
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function checkUpdateModule(): bool
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');

        $stableVersionsOnly = Option::get('main', 'stable_versions_only') ?: 'Y';
        $arUpdateList = \CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly, [self::getModuleId()]);

        if (empty($arUpdateList['MODULE'])) return false;

        $accessNewVersion = false;
        foreach ($arUpdateList['MODULE'] as $module) {
            $accessNewVersion = $module['@']['ID'] == self::$MODULE_ID;
            if ($accessNewVersion) break;
        }

        return $accessNewVersion;
    }

    public static function getDeliveryList(): array
    {
        $deliveriesClassName = [
            '\Sale\Handlers\Delivery\SalesbeatHandler',
            '\Sale\Handlers\Delivery\Salesbeat2Profile',
        ];

        $result = [];

        if ($deliveryList = Sale\Delivery\Services\Table::getList()) {
            foreach ($deliveryList as $delivery) {
                if (!in_array($delivery['CLASS_NAME'], $deliveriesClassName))
                    continue;

                $result[] = $delivery;
            }
        }

        return $result;
    }

    /**
     * Получаем справочник ID доставок
     * @return array
     */
    public static function getDeliveryIdList(): array
    {
        $deliveryList = System::getDeliveryList();
        return array_column($deliveryList, 'ID');
    }

    /**
     * Получаем тип AB Test
     */
    public static function getAbTest(): string
    {
        return $_SESSION['SB_AB_TEST'];
    }

    /**
     * Устанавливаем тип AB Test
     * @param string $string
     */
    public static function setAbTest(string $string): void
    {
        $_SESSION['SB_AB_TEST'] = $string;
    }
}