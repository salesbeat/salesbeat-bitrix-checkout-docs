<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;

if (isset($_POST['action']) && $_POST['action'] == 'clear')
    unset($_SESSION['SALESBEAT_ADMIN']);

unset($_POST['action']);
if (Loader::includeModule('salesbeat.sale'))
    $_SESSION['SALESBEAT_ADMIN'] = $_POST;