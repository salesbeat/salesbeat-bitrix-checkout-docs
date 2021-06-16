<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$templateParameterName = Loc::getMessage('SB_SOA_PARAMS_TEMPLATE_NAME_DEFAULT');
$arTemplateParameters = [
    'DISPLAY_VALUE' => [
        'NAME' => Loc::getMessage('SB_SOA_PARAMS_DISPLAY_VALUE_NAME'),
        'TYPE' => 'STRING',
        'VALUES' => '',
        'PARENT' => 'BASE'
    ],
];