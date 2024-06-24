<?php

namespace Kolos\Studio\Sale;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Sale\Order;
use Bitrix\Main\Grid\Declension;
use Kolos\Studio\Sale\BasketService;

class OrderService
{
    private $basketService;
    private $userId = 0;
    protected int $personType = 1;

    private function canCreateOrder(): bool
    {
        global $USER;

        if ($USER->IsAuthorized()) {
            $this->userId = $USER->GetID();
        }

        return $this->userId > 0;
    }

    public function createOrder(): array
    {
        if ($this->canCreateOrder() !== true) {
            return [
                'status' => false,
                'error' => 'Пользователь не авторизован',
                'reload' => true,
            ];
        }

        $oldBasket = BasketService::getInstance()->getBasket();
        $oldBasketItems = $oldBasket->getBasketItems();

        $oldBasketProducts = [];

        foreach ($oldBasketItems as $item) {
            $oldBasketProducts[$item->getId()] = [
                'ID' => $item->getId(),
                'PRODUCT_ID' => $item->getProductId(),
                'NAME' => $item->getField('NAME'),
                'QUANTITY' => $item->getQuantity(),
                'PRICE' => $item->getPrice(),
            ];
        }

        $actualBasket = BasketService::getInstance()->getBasket();
        $actualBasketItems = $oldBasket->getBasketItems();

        foreach ($actualBasketItems as $item) {
            if (
                isset($oldBasketProducts[$item->getId()]) &&
                $oldBasketProducts[$item->getId()]['QUANTITY'] == $item->getQuantity()
            ) {
                unset($oldBasketProducts[$item->getId()]);
            } elseif (
                isset($oldBasketProducts[$item->getId()]) &&
                $oldBasketProducts[$item->getId()]['QUANTITY'] > $item->getQuantity()
            ) {
                $oldBasketProducts[$item->getId()]['QUANTITY'] -= $item->getQuantity();
            }
        }

        if ($actualBasket->getPrice() <= 0) {
            return [
                'status' => false,
                'error' => 'Корзина пустая!',
                'reload' => true,
            ];
        }

        $siteId = Context::getCurrent()->getSite();
        $currencyCode = CurrencyManager::getBaseCurrency();
        $order = \Bitrix\Sale\Order::create($siteId, $this->userId);
        $order->setPersonTypeId($this->personType);
        $order->setField('XML_ID', $this->orderXmlId);
        $order->setField('CURRENCY', $currencyCode);

        $order->setBasket($actualBasket);
        $order->doFinalAction(true);
        $result = $order->save();

        $clearPrice = 0;
        foreach ($oldBasketProducts as $item) {
            $clearPrice += $item['QUANTITY'] * $item['PRICE'];
        }

        if ($result->isSuccess()) {
            return [
                'status' => true,
                'orderId' => $order->getId(),
                'clearList' => $oldBasketProducts,
                'orderPrice' => price_format($order->getPrice()),
                'clearPrice' => $clearPrice > 0 ? price_format($clearPrice) : 0,
            ];
        } else {
            return [
                'status' => false,
                'error' => 'Системная ошибка, повторите попытку позже',
            ];
        }
    }

    public static function getUserOrder(): array
    {
        global $USER;

        $result = [];

        if ($USER->IsAuthorized()) {
            $orders = Order::getList([
                'filter' => [
                    'USER_ID' => $USER->GetID(),
                ],
                'select' => ['ID'],
            ])->fetchAll();

            $productsDeclension = new Declension(' товар', ' товара', ' товаров');
            foreach ($orders as $order) {

                $order = Order::load($order['ID']);
                echo $order->getField('CREATE_DATE') . PHP_EOL;
                $item = [
                    'ID' => $order->getId(),
                    'DATE' => $order->getField('DATE_INSERT')->format('d.m.Y'),
                    'PRICE' => price_format($order->getPrice()),
                    'PRICE_FORMAT' => number_format($order->getPrice(), 2, '.', ' '),
                    'STATUS_ID' => $order->getField('STATUS_ID'),
                ];

                $basket = $order->getBasket();

                foreach ($basket as $basketItem){
                    $item['basket'][] = [
                        'id' => $basketItem->getProductId(),
                        'name' => $basketItem->getField('NAME'),
                        'price' => number_format($basketItem->getPrice(), 2, '.', ' '),
                        'quantity' => $basketItem->getQuantity(),
                        'amount' => number_format($basketItem->getPrice() * $basketItem->getQuantity(), 2, '.', ' '),
                    ];
                }

                $item['COUNT_ITEMS_FULL'] = count($item['basket']);
                $item['COUNT_ITEMS_FULL_TEXT'] = $item['COUNT_ITEMS_FULL'] . $productsDeclension->get($item['COUNT_ITEMS_FULL']);

                $item['COUNT_ITEMS'] = count($item['basket']) - 1;
                $item['COUNT_ITEMS_TEXT'] = $item['COUNT_ITEMS_FULL'] . $productsDeclension->get($item['COUNT_ITEMS']);

                $item['PRODUCT_TITLE'] = current($item['basket'])['name'];

                $result[] = $item;
            }

            return $result;
        }

        return [];
    }

    public static function getStatuses(): array
    {
        $arrStatus = [];

        $statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList([
            'order' => ['STATUS.SORT' => 'ASC'],
            'filter' => ['STATUS.TYPE' => 'O', 'LID' => LANGUAGE_ID],
            'select' => ['STATUS_ID', 'NAME'],
        ])->fetchAll();

        foreach ($statusResult as $item) {
            $arrStatus[$item['STATUS_ID']] = $item['NAME'];
        }

        return $arrStatus;
    }
}
