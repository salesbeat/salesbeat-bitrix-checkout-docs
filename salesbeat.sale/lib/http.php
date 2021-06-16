<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;

class Http
{
    private static $instances = null;
    private $httpClient = null;

    /**
     * Storage constructor.
     */
    protected function __construct()
    {
        $this->httpClient = new HttpClient([
            'redirect' => false,
            'socketTimeout' => 15,
            'streamTimeout' => 30,
            'version' => HttpClient::HTTP_1_1,
            'charset' => 'UTF-8',
            'disableSslVerification' => false
        ]);
    }

    protected function __clone()
    {

    }

    /**
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new SystemException('Cannot unserialize a singleton');
    }

    /**
     * @return Http
     */
    public static function getInstance(): Http
    {
        $class = static::class;

        if (!isset(self::$instances[$class]))
            self::$instances[$class] = new static();

        return self::$instances[$class];
    }

    /**
     * Get запрос
     * @param string $url
     * @param array $data
     * @return array
     */
    public function get(string $url, array $data = []): array
    {
        if (!$url)
            return ['status' => 'error', 'message' => 'Empty url'];

        if (!is_array($data))
            return ['status' => 'error', 'message' => 'Data not array'];

        return $this->send('get', $url, $data);
    }

    /**
     * Post запрос
     * @param string $url
     * @param array $data
     * @return array
     */
    public function post(string $url, array $data = []): array
    {
        if (empty($url))
            return ['status' => 'error', 'message' => 'Empty url'];

        if (!is_array($data))
            return ['status' => 'error', 'message' => 'Data not array'];

        return $this->send('post', $url, $data);
    }

    /**
     * Отправка запроса
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array
     */
    private function send(string $method, string $url, array $data): array
    {
        $query = '';
        if ($method == 'get') {
            if (!empty($data)) $query = '?' . http_build_query($data);
            $result = $this->httpClient->get($url . $query);
        } elseif ($method == 'post') {
            if (!empty($data)) $query = Json::encode($data);
            $result = $this->httpClient->post($url, $query);
        }

        return !empty($result) ? Json::decode($result) : ['error' => 'Нет ответа'];
    }
}