<?php

namespace Salesbeat\Sale;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Order;

class SaleOrderList extends SaleOrder
{
    /**
     * @param object $list
     */
    public function onInit($list)
    {
        if ($list->table_id !== 'tbl_sale_order') return;

        $this->orderIdList = $this->getOrderIdList($list->aRows);
        if (empty($this->orderIdList)) return;

        $this->orders = $this->getOrders();
        if (empty($this->orders)) return;

        $this->sbOrders = $this->getSbOrders();

        $this->addActionsRows($list);
        $this->addActionsTable($list);
    }

    /**
     * @param object $list
     */
    private function addActionsRows(&$list)
    {
        $deliveryIdList = System::getDeliveryIdList();

        foreach ($list->aRows as &$row) {
            if (empty($this->orders[$row->id])) continue;
            if (!in_array($this->orders[$row->id]['DELIVERY_ID'], $deliveryIdList)) continue;
            if (!empty($this->sbOrders[$row->id]['SENT_COURIER']) && $this->sbOrders[$row->id]['SENT_COURIER'] == 'Y') continue;

            $actionLink = $GLOBALS['APPLICATION']->GetCurPage();
            $newActions = [
                ['SEPARATOR' => true],
                [
                    'ICON' => '',
                    'HTML' => '<span style="color:#008007">' . Loc::getMessage('SB_ACTIONS_SEND_ORDER_TEXT') . '</span>',
                    'ACTION' => 'if (confirm(\'' . Loc::getMessage('SB_ACTIONS_SEND_ORDER_ACTION') . '\')) 
                            ' . $list->table_id . '.GetAdminList(\'' . $actionLink . '?ID=' . $row->id . '&lang=' . LANGUAGE_ID . '&action_button=send_order\');'
                ],
            ];

            // Добавляем действие на строку
            $row->aActions = array_merge($row->aActions, $newActions);
        }
    }

    /**
     * @param object $list
     */
    private function addActionsTable(&$list)
    {
        // Добавляем массовое действие
        $list->arActions['send_orders'] = [
            'name' => Loc::getMessage('SB_GROUP_ACTION_SEND_ORDERS'),
            'value' => 'send_orders',
            'action' => 'if (window.confirm(\'' . Loc::getMessage('SB_GROUP_ACTION_SEND_ORDERS_ACTION') . '\')
                    BX(' . $list->table_id . ')) BX.submit( BX(form_' . $list->table_id . '), \'send_orders\');'
        ];
    }
}