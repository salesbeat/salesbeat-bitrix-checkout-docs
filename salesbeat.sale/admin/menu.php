<?php

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!defined('SALESBEAT')) {
    define('SALESBEAT', true);

    return [
        'parent_menu' => 'global_menu_store',
        'section' => 'salesbeat',
        'sort' => 100,
        'text' => 'Salesbeat',
        'title' => 'Salesbeat',
        'icon' => 'salesbeat_menu_icon',
        'page_icon' => 'salesbeat_page_icon',
        'items_id' => 'menu_salesbeat',
        'items' => [
            'order_list' => [
                'text' => Loc::getMessage('SB_ITEMS_ORDER_LIST_TEXT'),
                'title' => Loc::getMessage('SB_ITEMS_ORDER_LIST_TITLE'),
                'url' => 'sb_delivery_order_list.php?lang=' . LANGUAGE_ID,
                'items_id' => 'menu_salesbeat_sale_order_list',
                'items' => []
            ]
        ],
    ];
}