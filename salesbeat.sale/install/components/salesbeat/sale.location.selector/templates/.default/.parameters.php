<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arTemplateParameters = [
    'DISPLAY_VALUE' => [
        'NAME' => Loc::getMessage('SB_SCED_PARAMS_DISPLAY_VALUE_NAME'),
        'TYPE' => 'STRING',
        'VALUES' => '',
        'PARENT' => 'BASE'
    ],
];