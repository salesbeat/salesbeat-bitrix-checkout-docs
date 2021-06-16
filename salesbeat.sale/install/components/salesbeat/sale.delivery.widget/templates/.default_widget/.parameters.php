<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$templateParameterName = Loc::getMessage('SB_SDW_PARAMS_TEMPLATE_NAME_DEFAULT_WIDGET');
$arTemplateParameters = [
    'DISPLAY_VALUE' => [
        'NAME' => Loc::getMessage('SB_SDW_PARAMS_DISPLAY_VALUE_NAME'),
        'TYPE' => 'STRING',
        'VALUES' => '',
        'PARENT' => 'BASE'
    ],
];