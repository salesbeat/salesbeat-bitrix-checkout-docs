<?php

namespace Salesbeat\Sale;

class City
{
    /**
     * @return array
     */
    public static function getCity(): array
    {
        if (empty($_SESSION['SALESBEAT_CITY']))
            $_SESSION['SALESBEAT_CITY'] = [];

        return $_SESSION['SALESBEAT_CITY'];
    }

    /**
     * @param string $city
     * @return bool
     */
    public static function setCity(string $city): bool
    {
        $array = explode('#', $city);

        if (empty($array[0]) || empty($array[1]))
            return false;

        $_SESSION['SALESBEAT_CITY'] = [
            'ID' => $array[0],
            'NAME' => $array[1]
        ];

        return true;
    }

    /**
     * @param array $fields
     * @return string
     */
    public static function transformCityName(array $fields): string
    {
        if (empty($fields)) return '';

        $value = $fields['CITY_CODE'] . '#';
        $value .= !empty($fields['SHORT_NAME']) ? $fields['SHORT_NAME'] . '. ' : '';
        $value .= !empty($fields['CITY_NAME']) ? $fields['CITY_NAME'] : '';
        $value .= !empty($fields['CITY_NAME']) && !empty($fields['REGION_NAME']) ? ', ' : '';
        $value .= !empty($fields['REGION_NAME']) ? $fields['REGION_NAME'] : '';

        return $value;
    }
}