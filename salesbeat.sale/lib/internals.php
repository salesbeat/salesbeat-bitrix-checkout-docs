<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Internals\PersonTypeTable;
use \Bitrix\Sale\Internals\OrderPropsGroupTable;
use \Bitrix\Sale\Internals\OrderPropsTable;

Loc::loadMessages(__FILE__);

class Internals
{
    /**
     * @return array
     */
    private static function sbPropertyList(): array
    {
        return [
            'LOCATION' => ['NAME' => Loc::getMessage('SB_PROP_CODE_LOCATION'), 'CODE' => 'SB_LOCATION', 'TYPE' => 'SBLOCATION', 'REQUIRED' => 'Y'],
            'DELIVERY_METHOD_NAME' => ['NAME' => Loc::getMessage('SB_PROP_CODE_DELIVERY_METHOD_NAME'), 'CODE' => 'SB_DELIVERY_METHOD_NAME', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'DELIVERY_METHOD_ID' => ['NAME' => Loc::getMessage('SB_PROP_CODE_DELIVERY_METHOD_ID'), 'CODE' => 'SB_DELIVERY_METHOD_ID', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'DELIVERY_PRICE' => ['NAME' => Loc::getMessage('SB_PROP_CODE_DELIVERY_PRICE'), 'CODE' => 'SB_DELIVERY_PRICE', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'DELIVERY_DAYS' => ['NAME' => Loc::getMessage('SB_PROP_CODE_DELIVERY_DAYS'), 'CODE' => 'SB_DELIVERY_DAYS', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'PVZ_ID' => ['NAME' => Loc::getMessage('SB_PROP_CODE_PVZ_ID'), 'CODE' => 'SB_PVZ_ID', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'PVZ_ADDRESS' => ['NAME' => Loc::getMessage('SB_PROP_CODE_PVZ_ADDRESS'), 'CODE' => 'SB_PVZ_ADDRESS', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'STREET' => ['NAME' => Loc::getMessage('SB_PROP_CODE_STREET'), 'CODE' => 'SB_STREET', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'HOUSE' => ['NAME' => Loc::getMessage('SB_PROP_CODE_HOUSE'), 'CODE' => 'SB_HOUSE', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'HOUSE_BLOCK' => ['NAME' => Loc::getMessage('SB_PROP_CODE_HOUSE_BLOCK'), 'CODE' => 'SB_HOUSE_BLOCK', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'FLAT' => ['NAME' => Loc::getMessage('SB_PROP_CODE_FLAT'), 'CODE' => 'SB_FLAT', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'INDEX' => ['NAME' => Loc::getMessage('SB_PROP_CODE_INDEX'), 'CODE' => 'SB_INDEX', 'TYPE' => 'STRING', 'REQUIRED' => 'N'],
            'COMMENT' => ['NAME' => Loc::getMessage('SB_PROP_CODE_COMMENT'), 'CODE' => 'SB_COMMENT', 'TYPE' => 'STRING', 'REQUIRED' => 'N']
        ];
    }

    /**
     * @return array
     */
    public static function getSbPropertyList(): array
    {
        return self::sbPropertyList();
    }

    /**
     * @return bool
     */
    public static function createProperties(): bool
    {
        $moduleName = System::getModuleName();

        $personTypeList = self::getPersonTypeList();
        if (empty($personTypeList)) return false;

        $propertyGroupList = self::getPropertyGroupList([
            'filter' => ['NAME' => $moduleName]
        ]);

        $filterPersonTypeList = array_filter($personTypeList, function ($personType) use ($propertyGroupList) {
            return !in_array($personType['ID'], array_column($propertyGroupList, 'PERSON_TYPE_ID'));
        });

        // Если найдены типы пользователей без группы Salesbeat, то создаем
        if (!empty($filterPersonTypeList)) {
            foreach ($filterPersonTypeList as $personType) {
                self::addPropertyGroup([
                    'PERSON_TYPE_ID' => $personType['ID'],
                    'NAME' => $moduleName,
                ]);
            }

            $propertyGroupList = self::getPropertyGroupList([
                'filter' => ['NAME' => $moduleName]
            ]);
        }

        $existPropertyList = self::getPropertyList([
            'order' => ['ID' => 'ASC'],
            'filter' => ['CODE' => array_column(self::getSbPropertyList(), 'CODE')]
        ]);

        $newExistPropertyList = [];
        foreach ($existPropertyList as $existProperty)
            $newExistPropertyList[$existProperty['CODE'] . '_' . $existProperty['PERSON_TYPE_ID']] = $existProperty;

        foreach ($propertyGroupList as $propertyGroup) {
            $sort = 0;
            foreach (self::getSbPropertyList() as $sbProperty) {
                if (!empty($newExistPropertyList[$sbProperty['CODE'] . '_' . $propertyGroup['PERSON_TYPE_ID']]))
                    continue;

                self::addProperty([
                    'PERSON_TYPE_ID' => $propertyGroup['PERSON_TYPE_ID'],
                    'NAME' => $sbProperty['NAME'],
                    'TYPE' => $sbProperty['TYPE'],
                    'CODE' => $sbProperty['CODE'],
                    'REQUIRED' => $sbProperty['REQUIRED'],
                    'PROPS_GROUP_ID' => $propertyGroup['ID'],
                    'SORT' => $sort += 100
                ]);
            }
        }

        return true;
    }

    /**
     * @param array $fields
     * @return array
     */
    public static function getPersonTypeList(array $fields = []): array
    {
        $personTypeList = [];

        $rsPersonTypes = PersonTypeTable::getList($fields);
        while ($personType = $rsPersonTypes->fetch())
            $personTypeList[] = $personType;

        return $personTypeList;
    }

    /**
     * @param array $fields
     * @return array
     */
    public static function getPropertyGroupList(array $fields = []): array
    {
        $propertyGroupList = [];

        $rsPropertyGroups = OrderPropsGroupTable::getList($fields);
        while ($propertyGroup = $rsPropertyGroups->fetch())
            $propertyGroupList[] = $propertyGroup;

        return $propertyGroupList;
    }

    /**
     * @param array $fields
     */
    public static function addPropertyGroup(array $fields)
    {
        OrderPropsGroupTable::add([
            'PERSON_TYPE_ID' => $fields['PERSON_TYPE_ID'],
            'NAME' => $fields['NAME'],
            'SORT' => 1000
        ]);
    }

    /**
     * @param array $fields
     * @return array
     */
    public static function getPropertyList(array $fields = []): array
    {
        $propertyList = [];

        $rsProperties = OrderPropsTable::GetList($fields);
        while ($property = $rsProperties->fetch())
            $propertyList[] = $property;

        return $propertyList;
    }

    /**
     * Получаем ID свойства
     * @param int $personTypeId
     * @param string $propertyCode
     * @return int
     */
    public static function getPropertyIdByCode(int $personTypeId, string $propertyCode): int
    {
        $propertyList = self::getPropertyList([
            'order' => ['ID' => 'ASC'],
            'filter' => [
                'PERSON_TYPE_ID' => $personTypeId,
                'CODE' => $propertyCode
            ]
        ]);

        return !empty($propertyList) ? $propertyList[key($propertyList)]['ID'] : 0;
    }

    /**
     * @param array fields
     */
    public static function addProperty(array $fields)
    {
        OrderPropsTable::add([
            'PERSON_TYPE_ID' => $fields['PERSON_TYPE_ID'],
            'NAME' => $fields['NAME'],
            'TYPE' => $fields['TYPE'],
            'REQUIRED' => $fields['REQUIRED'],
            'DEFAULT_VALUE' => '',
            'SORT' => $fields['SORT'],
            'PROPS_GROUP_ID' => $fields['PROPS_GROUP_ID'],
            'DESCRIPTION' => '',
            'CODE' => $fields['CODE'],
            'SETTINGS' => ['SIZE' => '40'],
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);
    }

    /**
     * @param int $propertyId
     * @param array $fields
     */
    public static function updateProperty(int $propertyId, array $fields)
    {
        OrderPropsTable::update($propertyId, $fields);
    }

    /**
     * @return bool
     */
    public static function activateLocation(): bool
    {
        $propertyList = self::getPropertyList([
            'order' => ['ID' => 'ASC'],
            'filter' => ['ACTIVE' => 'N', 'CODE' => 'SB_LOCATION']
        ]);

        if (empty($propertyList)) return false;

        foreach ($propertyList as $property) {
            $property['ACTIVE'] = 'Y';
            $property['TYPE'] = 'SBLOCATION';

            self::updateProperty((int)$property['ID'], $property);
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function deActivateLocation(): bool
    {
        $propertyList = self::getPropertyList([
            'order' => ['ID' => 'ASC'],
            'filter' => ['ACTIVE' => 'Y', 'CODE' => 'SB_LOCATION']
        ]);

        if (empty($propertyList)) return false;

        foreach ($propertyList as $property) {
            $property['ACTIVE'] = 'N';
            $property['TYPE'] = 'STRING';

            self::updateProperty((int)$property['ID'], $property);
        }

        return true;
    }
}