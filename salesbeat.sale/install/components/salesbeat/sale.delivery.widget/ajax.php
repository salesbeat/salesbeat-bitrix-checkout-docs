<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Salesbeat\Sale\Callback;

unset($_POST['action']);
if (Loader::includeModule('salesbeat.sale'))
    Callback::save($_POST);