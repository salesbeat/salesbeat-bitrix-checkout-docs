<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    'PARAMETERS' => [
        'INFO_NOTES' => [
            'PARENT' => 'BASE',
            'TYPE' => 'CUSTOM',
            'JS_FILE' => '/bitrix/js/main/comp_props.js',
            'JS_EVENT' => 'BxShowComponentNotes',
            'JS_DATA' => Loc::getMessage('SB_SCED_JS_DATA'),
        ]
    ],
];