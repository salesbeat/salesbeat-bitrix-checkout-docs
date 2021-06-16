<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Config\Option;

class Api
{
    private static $url = 'https://app.salesbeat.pro';
    private static $url_checkout = 'https://checkout.to.digital';

    /**
     * Проверка правильности токенов
     * @param string $apiToken
     * @param string $secretToken
     * @return array
     */
    public static function postCheckTokens(string $apiToken, string $secretToken): array
    {
        $arFields = [
            'api_token' => $apiToken,
            'secret_token' => $secretToken,
        ];

        return Http::getInstance()->post(self::$url . '/api/v1/check_tokens', $arFields);
    }

    /**
     * Поиск местоположения
     * @param string $token
     * @param array $city
     * @return array
     */
    public static function getCities(string $token, array $city): array
    {
        $fields = [];
        $fields = array_merge($fields, self::validateToken($token));
        $fields = array_merge($fields, self::validateCity($city));

        return Http::getInstance()->get(self::$url . '/api/v1/get_cities', $fields);
    }

    /**
     * Список всех служб доставки
     * @param string $token
     * @return array
     */
    public static function getListDeliveries(string $token): array
    {
        $fields = self::validateToken($token);

        return Http::getInstance()->get(self::$url . '/api/v1/get_all_delivery_methods', $fields);
    }

    /**
     * Список служб доставки в населённом пункте
     * @param string $token
     * @param array $city
     * @param array $profile
     * @param array $price
     * @return array
     */
    public static function getDeliveryByCity(string $token, array $city, array $profile, array $price): array
    {
        $fields = [];
        $fields = array_merge($fields, self::validateToken($token));
        $fields = array_merge($fields, self::validateCity($city));
        $fields = array_merge($fields, self::validateProfile($profile));
        $fields = array_merge($fields, self::validatePrice($price));

        return Http::getInstance()->get(self::$url . '/api/v1/get_delivery_methods_by_city', $fields);
    }

    /**
     * Расчёт стоимости доставки
     * @param string $token
     * @param array $city
     * @param array $delivery
     * @param array $profile
     * @param array $price
     * @return array
     */
    public static function getDeliveryPrice(string $token, array $city, array $delivery, array $profile, array $price): array
    {
        $fields = [];
        $fields = array_merge($fields, self::validateToken($token));
        $fields = array_merge($fields, self::validateCity($city));
        $fields = array_merge($fields, self::validateDelivery($delivery));
        $fields = array_merge($fields, self::validateProfile($profile));
        $fields = array_merge($fields, self::validatePrice($price));

        return Http::getInstance()->get(self::$url . '/api/v1/get_delivery_price', $fields);
    }

    /**
     * Синхронизация способов оплаты
     * @param string $token
     * @param array $paySystems
     * @param array $exPaySystems
     * @return array
     */
    public static function syncDeliveryPaymentTypes(string $token, array $paySystems, array $exPaySystems = []): array
    {
        $paySystemsCash = !empty($exPaySystems['cash']) ? $exPaySystems['cash'] : [];
        $paySystemsCard = !empty($exPaySystems['card']) ? $exPaySystems['card'] : [];
        $paySystemsOnline = !empty($exPaySystems['online']) ? $exPaySystems['online'] : [];

        $fields = [];
        foreach ($paySystems as $paySystem) {
            if (empty($paySystem['NAME'])) continue;
            if (in_array($paySystem['ID'], $paySystemsCash)) continue;
            if (in_array($paySystem['ID'], $paySystemsCard)) continue;
            if (in_array($paySystem['ID'], $paySystemsOnline)) continue;

            $fields[] = [
                'name' => $paySystem['NAME'],
                'code' => $paySystem['ID']
            ];
        }

        $token = self::validateToken($token)['token'];
        return Http::getInstance()->post(self::$url . '/api/v1/sync_delivery_payment_types?token=' . $token, $fields);
    }

    /**
     * Получение способов оплаты
     * @param string $token
     * @return array
     */
    public static function getDeliveryPaymentTypes(string $token): array
    {
        $fields = self::validateToken($token);

        return Http::getInstance()->get(self::$url . '/api/v1/get_delivery_payment_types', $fields);
    }

    /**
     * Создать заказ на доставку
     * @param array $fields
     * @return array
     */
    public static function createOrder(array $fields): array
    {
        return Http::getInstance()->post(self::$url . '/delivery_order/create/', $fields);
    }

    /**
     * Вызвать курьера
     * @param array $fields
     * @return array
     */
    public static function courier(array $fields): array
    {
        return Http::getInstance()->post(self::$url . '/delivery_order/create/', $fields);
    }

    /**
     * Упаковщик товаров
     * @param string $token
     * @param array $fields
     * @return array
     */
    public static function packer(string $token, array $fields): array
    {
        $token = self::validateToken($token)['token'];
        return Http::getInstance()->post(self::$url . '/api/v1/packer?token=' . $token, $fields);
    }

    /**
     * API для создания корзины
     * @param string $secretToken
     * @param array $fields
     * @return array
     */
    public static function createCart(string $secretToken, array $fields): array
    {
        return Http::getInstance()->post(self::$url_checkout . '/api/create_cart?secret_token=' . $secretToken, $fields);
    }

    /**
     * API для обновления корзины
     * @param string $secretToken
     * @param array $fields
     * @return array
     */
    public static function updateCart(string $secretToken, string $sbCartId, array $fields): array
    {
        return Http::getInstance()->post(self::$url_checkout . '/api/update_cart/' . $sbCartId . '?secret_token=' . $secretToken, $fields);
    }

    /**
     * Валидация токена
     * @param string $string
     * @return array
     */
    private static function validateToken(string $string): array
    {
        $string = $string ?: Option::get(System::getModuleId(), 'api_token');
        return ['token' => $string];
    }

    /**
     * Валидация населенного пункта
     * @param array $array
     * @return array
     */
    private static function validateCity(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (in_array($key, ['id', 'city', 'city_id', 'postalcode', 'ip']))
                $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Валидация метода доставки
     * @param array $array
     * @return array
     */
    private static function validateDelivery(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (in_array($key, ['delivery_method_id', 'pvz_id']))
                $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Валидация габаритов
     * @param array $array
     * @return array
     */
    private static function validateProfile(array $array): array
    {
        return [
            'weight' => (int)$array['weight'],
            'x' => (int)$array['x'],
            'y' => (int)$array['y'],
            'z' => (int)$array['z']
        ];
    }

    /**
     * Валидация цены
     * @param array $array
     * @return array
     */
    private static function validatePrice(array $array): array
    {
        return [
            'price_to_pay' => (float)$array['price_to_pay'],
            'price_insurance' => (float)$array['price_insurance']
        ];
    }
}