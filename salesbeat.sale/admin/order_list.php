<?php
/** @global CMain $APPLICATION */

use \Bitrix\Main;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\OrderTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

Loader::includeModule('salesbeat.sale');
Loader::includeModule('sale');

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$moduleId = System::getModuleId();
$adminListTableID = 'tbl_salesbeat_sale'; // Id таблицы
$adminSort = new CAdminSorting($adminListTableID, 'ID', 'DESC'); // Объект сортировки
$adminList = new CAdminUiList($adminListTableID, $adminSort); // Основной объект списка

// События действий
if ($arListId = $adminList->GroupAction()) {
    ob_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/services/salesbeat.sale/sale_order_send.php';
    $request = ob_get_clean();

    $request = Main\Web\Json::decode($request);
    if ($request && $request['status'] == 'error')
        $adminList->AddGroupError($request['message'], $request['order_id']);

    unset($request);

    if ($adminList->hasGroupErrors()) {
        $adminSidePanelHelper->sendJsonErrorResponse($adminList->getGroupErrors());
    } else {
        $adminSidePanelHelper->sendSuccessResponse();
    }
}

// URL
$selfFolderUrl = $adminPage->getSelfFolderUrl();
$adminPageUrl = $selfFolderUrl . 'sb_delivery_order_list.php';

// Формируем фильтр
$arFilterFields = [
    'id' => [
        'id' => 'ID',
        'name' => Loc::getMessage('SB_FILTER_ID_NAME'),
        'type' => 'number',
        'filterable' => '=',
        'default' => true
    ],
    'date_insert' => [
        'id' => 'DATE_INSERT',
        'name' => Loc::getMessage('SB_FILTER_DATE_INSERT_NAME'),
        'type' => 'date',
        'filterable' => ''
    ],
];
$filter = [];
$adminList->AddFilter($arFilterFields, $filter);

// Кнопки над таблицей
$adminContext = [
    [
        'TEXT' => Loc::getMessage('SB_CONTEXT_SETTING_TEXT'),
        'TITLE' => Loc::getMessage('SB_CONTEXT_SETTING_TITLE'),
        'LINK' => 'settings.php?lang=' . LANG . '&mid=' . System::getModuleId() . '&mid_menu=1',
        'GLOBAL_ICON' => 'adm-menu-setting',
    ],
    [
        'TEXT' => 'Excel',
        'TITLE' => Loc::getMessage('admin_lib_excel'),
        'LINK' => \CHTTP::urlAddParams($APPLICATION->GetCurPageParam(), ['mode' => 'excel']),
        'GLOBAL_ICON' => 'adm-menu-excel'
    ]
];

$adminList->AddAdminContextMenu($adminContext, false, false);
unset($adminContext);

// Заголовки таблицы
$headerList = [
    'id' => [
        'id' => 'ID',
        'content' => Loc::getMessage('SB_HEADERS_ID_CONTENT'),
        'sort' => 'ID',
        'default' => true
    ],
    'date_insert' => [
        'id' => 'DATE_INSERT',
        'content' => Loc::getMessage('SB_HEADERS_DATE_INSERT_CONTENT'),
        'sort' => 'DATE_INSERT',
        'default' => true
    ],
    'customer' => [
        'id' => 'CUSTOMER',
        'content' => Loc::getMessage('SB_HEADERS_CUSTOMER_CONTENT'),
        'sort' => 'USER_ID',
        'default' => true,
    ],
    'price' => [
        'id' => 'PRICE',
        'content' => Loc::getMessage('SB_HEADERS_PRICE_CONTENT'),
        'sort' => 'PRICE',
        'default' => true,
    ],
    'price_delivery' => [
        'id' => 'PRICE_DELIVERY',
        'content' => Loc::getMessage('SB_HEADERS_PRICE_DELIVERY_CONTENT'),
        'sort' => 'PRICE_DELIVERY',
        'default' => false,
    ],
    'type_delivery' => [
        'id' => 'TYPE_DELIVERY',
        'content' => Loc::getMessage('SB_HEADERS_TYPE_DELIVERY_CONTENT'),
        'default' => true,
    ],
    'tracking_number' => [
        'id' => 'TRACKING_NUMBER',
        'content' => Loc::getMessage('SB_HEADERS_TRACKING_NUMBER_CONTENT'),
        'default' => true,
    ],
    'sb_order_id' => [
        'id' => 'SB_ORDER_ID',
        'content' => Loc::getMessage('SB_HEADERS_SB_ORDER_ID_CONTENT'),
        'default' => false,
    ],
    'sb_order_date' => [
        'id' => 'SB_ORDER_DATE',
        'content' => Loc::getMessage('SB_HEADERS_SB_ORDER_DATE_CONTENT'),
        'default' => false,
    ],
    'sb_sent_courier' => [
        'id' => 'SB_SENT_COURIER',
        'content' => Loc::getMessage('SB_HEADERS_SB_SENT_COURIER_CONTENT'),
        'default' => false,
    ],
    'sb_date_courier' => [
        'id' => 'SB_DATE_COURIER',
        'content' => Loc::getMessage('SB_HEADERS_SB_DATE_COURIER_CONTENT'),
        'default' => false,
    ],
    'sb_tracking_status' => [
        'id' => 'SB_TRACKING_STATUS',
        'content' => Loc::getMessage('SB_HEADERS_SB_TRACKING_STATUS_CONTENT'),
        'default' => true,
    ],
    'sb_date_tracking' => [
        'id' => 'SB_DATE_TRACKING',
        'content' => Loc::getMessage('SB_HEADERS_SB_DATE_TRACKING_CONTENT'),
        'default' => false,
    ]
];
$adminList->AddHeaders($headerList);
unset($headerList);

// Для корректной работы сортировки
global $sortBy, $sortOrder;
if (!$sortBy) $sortBy = 'ID';
if (!$sortOrder) $sortOrder = 'ASC';

// Подготавливаем первоначальный фильтр для запроса
$getListParams = [
    'order' => [$sortBy => $sortOrder],
    'filter' => $filter
];

$getListParams['filter']['=DELIVERY_ID'] = System::getDeliveryIdList();

// Работаем с навигацией
$countQuery = new Entity\Query(Sale\Internals\OrderTable::getEntity());
$countQuery->addSelect(new Entity\ExpressionField('CNT', 'COUNT(1)'));
$countQuery->setFilter($getListParams['filter']);

$totalCount = (int)$countQuery->setLimit(null)->setOffset(null)->exec()->fetch()['CNT'];
unset($countQuery);

$navyParams = CDBResult::GetNavParams(CAdminUiResult::GetNavSize($adminListTableID));
if ($totalCount) {
    $totalPages = ceil($totalCount / $navyParams['SIZEN']);
    if ($navyParams['PAGEN'] > $totalPages) $navyParams['PAGEN'] = $totalPages;
} else {
    $totalPages = '1';
    $navyParams['PAGEN'] = 1;
}
$getListParams['limit'] = $navyParams['SIZEN'];
$getListParams['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);

$data = new CAdminUiResult(Sale\Internals\OrderTable::getList($getListParams), $adminListTableID); // Получаем список заказов
$data->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
$data->NavRecordCount = $totalCount;
$data->NavPageCount = $totalPages;
$data->NavPageNomer = $navyParams['PAGEN'];
unset($getListParams, $navyParams, $totalCount);

$adminList->SetNavigationParams($data, ['BASE_LINK' => $adminPageUrl]);

// Работаем со списком заказов
while ($aOrder = $data->GetNext()) {
    $orderId = (int)$aOrder['ID'];
    if (empty($orderId)) continue;

    $order = Sale\Order::load((int)$aOrder['ID']); // Получаем сущность заказа

    // Получаем список отгрузок из колекции
    $orderShipment = [];
    foreach ($order->getShipmentCollection() as $shipment) {
        if ($shipment->isSystem()) continue;

        $orderShipment = $shipment->getFieldValues();
        break;
    }

    // Получаем свойства заказа из коллекции
    $orderProperties = [];
    foreach ($order->getPropertyCollection() as $property) {
        if (empty($property->getField('CODE'))) continue;

        $orderProperties[$property->getField('CODE')] = $property->getValue();
    }

    // Получаем данные из нашей таблицы заказов
    $sbOrder = OrderTable::getList([
        'select' => ['*'],
        'filter' => ['ORDER_ID' => $orderId],
        'order' => ['ID' => 'ASC'],
        'limit' => 1
    ])->Fetch();

    // Формируем список согласно заголовку таблицы
    $aRow = [
        'DATE_INSERT' => $aOrder['DATE_INSERT'],
        'TYPE_DELIVERY' => $orderProperties['SB_DELIVERY_METHOD_NAME'],
        'TRACKING_NUMBER' => !empty($orderShipment['TRACKING_NUMBER']) ? $orderShipment['TRACKING_NUMBER'] : $sbOrder['TRACK_CODE'],
        'SB_ORDER_ID' => !empty($sbOrder['SB_ORDER_ID']) ? $sbOrder['SB_ORDER_ID'] : '',
        'SB_ORDER_DATE' => !empty($sbOrder['ORDER_DATE']) ? $sbOrder['ORDER_DATE'] : '',
        'SB_DATE_COURIER' => !empty($sbOrder['DATE_COURIER']) ? $sbOrder['DATE_COURIER'] : '',
        'SB_TRACKING_STATUS' => !empty($sbOrder['TRACKING_STATUS']) ? $sbOrder['TRACKING_STATUS'] : '',
        'SB_DATE_TRACKING' => !empty($sbOrder['DATE_TRACKING']) ? $sbOrder['DATE_TRACKING'] : '',
    ];

    // Применяем запись строку из бд к строке в таблице
    $row =& $adminList->AddRow($orderId, $aRow);
    unset($aRow);

    // Добавляем покупателя
    $row->AddViewField('ID', '<a href="sale_order_view.php?ID=' . $orderId . '&lang=' . LANGUAGE_ID . GetFilterParams('filter_') . '">№' . $orderId . '</a>');
    $row->AddViewField('CUSTOMER', \GetFormatedUserName($aOrder['USER_ID'], false, false));
    $row->AddViewField('PRICE', \CCurrencyLang::currencyFormat($aOrder['PRICE'], $aOrder['CURRENCY'], true));
    $row->AddViewField('PRICE_DELIVERY', \CCurrencyLang::currencyFormat($aOrder['PRICE_DELIVERY'], $aOrder['CURRENCY'], true));
    $row->AddViewField('SB_SENT_COURIER', !empty($sbOrder['SENT_COURIER']) && $sbOrder['SENT_COURIER'] == 'Y' ? 'Да' : 'Нет');

    // Действия над строкой
    $actionsList = [
        [
            'ICON' => '',
            'DEFAULT' => true,
            'TEXT' => Loc::getMessage('SB_ACTIONS_ORDER_VIEW_TEXT'),
            'ACTION' => $adminList->ActionRedirect('sale_order_view.php?ID=' . $orderId . '&lang=' . LANGUAGE_ID . GetFilterParams('filter_'))
        ]
    ];

    if (empty($sbOrder['SENT_COURIER']) || $sbOrder['SENT_COURIER'] != 'Y') {
        // Добавляем действие на строку
        $actionsList[] = [
            'ICON' => '',
            'TEXT' => '<span style="color:#008007">' . Loc::getMessage('SB_ACTIONS_SEND_ORDER_TEXT') . '</span>',
            'ACTION' => 'if (confirm(\'' . Loc::getMessage('SB_ACTIONS_SEND_ORDER_ACTION') . '\')) ' .
                $adminList->ActionDoGroup($orderId, 'send_order'),
        ];
    }

    $row->AddActions($actionsList);
    unset($orderShipment, $orderProperties, $sbOrder, $actionsList);
}
unset($data, $aOrder, $row);

// Массовые действия
$groupActionList = [
    'send_orders' => Loc::getMessage('SB_GROUP_ACTION_SEND_ORDERS'),
];
$adminList->AddGroupActionTable($groupActionList);
unset($groupActionList);

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('SB_TITLE'));
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if (System::checkUpdateModule()) {
    echo BeginNote(),
    Loc::getMessage('SB_NOTE_CURRENT_VERSION', ['#MODULE_VERSION#' => System::getModuleVersion()]),
    Loc::getMessage('SB_NOTE_NOT_LAST_VERSION'),
    Loc::getMessage('SB_NOTE_LINK_UPDATE', ['#MODULE_ID#' => System::getModuleId()]),
    EndNote();
} else {
    echo BeginNote(),
    Loc::getMessage('SB_NOTE_CURRENT_VERSION', ['#MODULE_VERSION#' => System::getModuleVersion()]),
    Loc::getMessage('SB_NOTE_LAST_VERSION'),
    EndNote();
}

// Ответы на действия
$statusAction = '';
if (!empty($_REQUEST['mess'])) $statusAction = htmlspecialcharsbx($_REQUEST['mess']);

if ($statusAction == 'ok') {
    $message = new CAdminMessage(['MESSAGE' => Loc::getMessage('SB_MESSAGE_OK'), 'TYPE' => 'OK']);
    echo $message->Show();
} elseif ($statusAction == 'error') {
    $message = new CAdminMessage(['MESSAGE' => Loc::getMessage('SB_MESSAGE_ERROR'), 'DETAILS' => $_REQUEST['code'], 'TYPE' => 'ERROR']);
    echo $message->Show();
}

$adminList->DisplayFilter($arFilterFields); // Отображаем фильтр
$adminList->DisplayList(); // Отображаем таблицу

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';