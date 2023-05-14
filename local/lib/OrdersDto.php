<?php

namespace Lib;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Order;
use Bitrix\Main\Entity\Query;
use \Bitrix\Sale\Internals\OrderTable;

class OrdersDto
{
    public function __construct()
    {
    }

    /**
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function getOrders(): array
    {
        Loader::includeModule('sale');

        $latestOrderId = OrdersDto::getLatestOrder();

        $query = new Query(OrderTable::getEntity());
        $query->setSelect(array("ID"));
        $query->setFilter(array('<ID'), $latestOrderId);
        $ordersId = $query->exec();

        $ordersResultDto = [];

        foreach ($ordersId as $orderId) {

            $order = Order::load($orderId['ID']);
            $ordersValues = [
                'dateInsert' => $order->getField('DATE_INSERT')->toString(),
                'dateUpdate' => $order->getField('DATE_UPDATE')->toString(),
                'personTypeId' => (int)$order->getField('PERSON_TYPE_ID'),
                'statusId' => $order->getField('STATUS_ID'),
                'price' => $order->getField('PRICE'),
                'discountValue' => $order->getField('DISCOUNT_VALUE'),
                'userId' => $order->getField('USER_ID'),
                'accountnumber' => $order->getField('ACCOUNT_NUMBER'),
                'payed' => $order->getField('PAYED'),
            ];

            $userProperties = [];
            $orderProperties = [];
            $propertyCollection = $order->getPropertyCollection();

            foreach ($propertyCollection as $property) {
                if ($property->getProperty()['USER_PROPS'] == 'Y') {
                    $userProperties[] = [
                        'code' => $property->getField('CODE'),
                        'value' => $property->getField('VALUE')
                    ];
                } else {
                    $orderProperties[] = [
                        'code' => $property->getField('CODE'),
                        'value' => $property->getField('VALUE')
                    ];
                }
            }

            $basketItems = [];
            $basket = $order->getBasket();
            foreach ($basket as $id => $basketItem) {
                $basketItems[$id] = [
                    'productId' => $basketItem->getField('PRODUCT_ID'),
                    'name ' => $basketItem->getField('NAME'),
                    'price' => $basketItem->getField('PRICE'),
                    'basePrice' => $basketItem->getField('BASE_PRICE'),
                    'quantity' => (int)$basketItem->getField('QUANTITY'),
                    'discountPrice' => $basketItem->getField('DISCOUNT_PRICE')
                ];
            }

            $ordersResultDto[$orderId['ID']] = [
                'ordersValues' => $ordersValues,
                'userProperties' => $userProperties,
                'orderProperties' => $orderProperties,
                'basketItems' => $basketItems
            ];
        }

        return $ordersResultDto;
    }

    public static function setLatestOrder($ordersResult)
    {
        $latestOrderId = array_key_last($ordersResult);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/orders/latest_order.json',
            json_encode(['latestOrderId' => $latestOrderId]));
    }

    public static function getLatestOrder()
    {
        $latestOrder = file_get_contents($_SERVER['DOCUMENT_ROOT'] .
            '/upload/orders/latest_order.json', true);
        return json_decode($latestOrder, true)['latestOrderId'];
    }

}