<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Loader;
use \Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(
    'salesbeat.sale',
    [
        '\Salesbeat\Sale\System' => 'lib/system.php',
        '\Salesbeat\Sale\Handler' => 'lib/handler.php',
        '\Salesbeat\Sale\Callback' => 'lib/callback.php',
        '\Salesbeat\Sale\Internals' => 'lib/internals.php',
        '\Salesbeat\Sale\Storage' => 'lib/storage.php',
        '\Salesbeat\Sale\OrderTable' => 'lib/ordertable.php',
        '\Salesbeat\Sale\Api' => 'lib/api.php',
        '\Salesbeat\Sale\Tools' => 'lib/tools.php',
        '\Salesbeat\Sale\SbLocationInput' => 'lib/internals/input.php',
        '\Salesbeat\Sale\Http' => 'lib/http.php',
        '\Salesbeat\Sale\City' => 'lib/city.php',
        '\Salesbeat\Sale\Basket' => 'lib/basket.php',
        '\Salesbeat\Sale\SaleOrder' => 'lib/sale/saleOrder.php',
        '\Salesbeat\Sale\SaleOrderList' => 'lib/sale/saleOrderList.php',
        '\Salesbeat\Sale\SaleOrderEdit' => 'lib/sale/saleOrderEdit.php',
        '\Salesbeat\Sale\CreateOrder' => 'lib/createOrder.php',
    ]
);

EventManager::getInstance()->addEventHandler(
    'sale',
    'registerInputTypes',
    [
        __NAMESPACE__ . '\Handler',
        'registerInputTypes'
    ]
);