<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\SystemException;

class Storage
{
    private static $instances = [];

    private $storageName = ''; // Название хранилища
    private $storage = []; // Хранилище

    /**
     * Storage constructor.
     * @param string $storageName
     */
    protected function __construct(string $storageName)
    {
        $this->storageName = $storageName;

        if (!empty($_SESSION[$this->storageName]))
            $this->storage = $_SESSION[$this->storageName];
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
     * @param string $storageName
     * @return Storage
     */
    public static function getInstance(string $storageName = 'SALESBEAT_SALE'): Storage
    {
        $class = static::class;

        if (!isset(self::$instances[$class]))
            self::$instances[$class] = new static($storageName);

        return self::$instances[$class];
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->storage;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getByID(int $id): array
    {
        return !empty($this->storage[$id]) ? $this->storage[$id] : [];
    }

    /**
     * @param int $id
     * @param array $data
     */
    public function set(int $id, array $data)
    {
        if ($id && is_array($data)) {
            if (empty($this->storage[$id])) $this->storage[$id] = [];
            $this->storage[$id] = $this->transform($data);
            $this->update();
        }
    }

    /**
     * @param int $id
     * @param array $data
     */
    public function append(int $id, array $data)
    {
        if (empty($this->storage[$id])) $this->storage[$id] = [];
        $this->storage[$id] = array_merge($this->storage[$id], $this->transform($data));
        $this->update();
    }

    public function delete()
    {
        $this->storage = [];
        $this->update();
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id)
    {
        if (!empty($this->storage[$id])) {
            $this->storage[$id] = [];
            $this->update();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function transform(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value)
            $result[mb_strtoupper($key)] = $value;

        return $result;
    }

    private function update()
    {
        if (!empty($this->storage)) {
            $_SESSION[$this->storageName] = $this->storage;
        } else {
            unset($_SESSION[$this->storageName]);
        }
    }
}