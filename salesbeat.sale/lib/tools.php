<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Context;
use \Bitrix\Main\Text\Encoding;

class Tools
{
    /**
     * @param mixed $data
     */
    public static function printr($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    /**
     * @param mixed $data
     */
    public static function vardump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function phoneToTel(string $phone): string
    {
        return preg_replace('/[^+0-9]+/', '', $phone);
    }

    /**
     * @param $obj
     * @param $prop
     * @return mixed
     * @throws \ReflectionException
     */
    public static function accessProtected($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    /**
     * @param int $number
     * @param array $suffix
     * @return string
     */
    public static function suffixToNumber(int $number, array $suffix): string
    {
        $keys = [2, 0, 1, 1, 1, 2];
        $mod = $number % 100;
        $suffixKey = ($mod > 7 && $mod < 20) ? 2 : $keys[min($mod % 10, 5)];
        return $number . ' ' . $suffix[$suffixKey];
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function utfDecode($data)
    {
        if (strtolower(SITE_CHARSET) != 'utf-8')
            $data = Encoding::convertEncoding($data, 'UTF-8', SITE_CHARSET);

        return $data;
    }

    /**
     * @return string
     */
    public static function getShopUrl(): string
    {
        global $APPLICATION;

        $protocol = Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http';
        $url = parse_url($_SERVER['HTTP_REFERER'] <> '' ? $_SESSION['SESS_HTTP_REFERER'] : $_SERVER['HTTP_REFERER']);
        $site = $APPLICATION->GetSiteByDir($url['path'], $url['host'])['SERVER_NAME'];

        return $protocol . '://' . $site;
    }
}