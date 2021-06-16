<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Internals\Input\Base;

class SbLocationInput extends Base
{
    protected static $patternDelimiters = ['/', '#', '~'];

    /**
     * @param $name
     * @param array $input
     * @param $value
     * @return string
     */
    public static function getEditHtmlSingle($name, array $input, $value): string
    {
        ob_start();
        $GLOBALS['APPLICATION']->IncludeComponent(
            'salesbeat:sale.location.selector',
            '',
            [
                'INPUT_NAME' => $name,
                'INPUT_VALUE' => $value,
            ],
            false
        );
        return ob_get_clean();
    }

    /**
     * @param $name
     * @param array $input
     * @param $value
     * @return string
     */
    public static function getFilterEditHtml($name, array $input, $value): string
    {
        return static::getEditHtmlSingle($name, $input, $value);
    }

    /**
     * @param array $input
     * @param $value
     * @return array
     */
    public static function getErrorSingle(array $input, $value): array
    {
        $errors = [];

        $value = trim($value);

        if (strval(trim($input['PATTERN'])) != '') {
            $pattern = trim($input['PATTERN']);
            $issetDelimiter = (isset($pattern[0]) && in_array($pattern[0], static::$patternDelimiters) && strrpos($pattern, $pattern[0]) !== false);

            $matchPattern = $pattern;
            if (!$issetDelimiter)
                $matchPattern = "/" . $pattern . "/";

            if (!preg_match($matchPattern, $value))
                $errors['PATTERN'] = Loc::getMessage('INPUT_STRING_PATTERN_ERROR');
        }

        return $errors;
    }

    /**
     * @param array $input
     * @param $reload
     * @return array
     */
    static function getSettings(array $input, $reload): array
    {
        return [];
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isDeletedSingle($value): bool
    {
        return is_array($value) && $value['DELETE'];
    }
}