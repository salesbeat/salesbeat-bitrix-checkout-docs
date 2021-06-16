<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Loader;

if (empty(Loader::includeModule('salesbeat.sale'))) return;

$request = Main\Application::getInstance()->getContext()->getRequest();

ob_start();
$GLOBALS['APPLICATION']->IncludeComponent(
    'salesbeat:sale.location.selector',
    '',
    [
        'INPUT_NAME' => $request->get('INPUT_NAME'),
        'INPUT_VALUE' => $request->get('INPUT_VALUE'),
        'IS_PUB' => 'Y'
    ],
    false
);

$result = [
    'status' => 'success',
    'data' => ob_get_clean()
];

/** @noinspection PhpVariableNamingConventionInspection */
global $APPLICATION;
$APPLICATION->restartBuffer();
header('Content-Type:application/json; charset=UTF-8');

echo Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();