<?php

namespace Salesbeat\Sale;

use \Bitrix\Sale;

class SaleOrder
{
    protected $orderIdList = [];
    protected $orders = [];
    protected $sbOrders = [];

    /**
     * @param array $rows
     * @return array
     */
    protected function getOrderIdList(array $rows): array
    {
        $result = [];
        foreach ($rows as $row)
            $result[] = $row->id;

        return $result;
    }

    /**
     * @return array
     */
    protected function getOrders(): array
    {
        $result = [];

        $rsOrders = Sale\Order::getList([
            'filter' => ['ID' => $this->orderIdList],
            'select' => ['ID', 'DELIVERY_ID']
        ]);

        while ($order = $rsOrders->fetch())
            $result[$order['ID']] = $order;

        return $result;
    }

    /**
     * @return array
     */
    protected function getSbOrders(): array
    {
        $result = [];

        $rsOrders = OrderTable::getList([
            'filter' => ['ORDER_ID' => $this->orderIdList],
            'select' => ['ID', 'ORDER_ID', 'SHIPMENT_ID', 'SENT_COURIER']
        ]);

        while ($order = $rsOrders->fetch())
            $result[$order['ORDER_ID']] = $order;

        return $result;
    }
}