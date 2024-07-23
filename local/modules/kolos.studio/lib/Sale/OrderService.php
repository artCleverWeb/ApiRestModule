<?php

namespace Kolos\Studio\Sale;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Main\Grid\Declension;
use Kolos\Studio\Sale\BasketService;
use Bitrix\Catalog\ProductTable;

Loader::includeModule('catalog');

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

        $actualBasket = BasketService::getInstance()->actualBasket();
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

        $propertyCollection = $order->getPropertyCollection();
        $property = $propertyCollection->getItemByOrderPropertyCode('DATE_CREATE');
        $property?->setValue(date('d.m.Y H:i:s'));

        $property = $propertyCollection->getItemByOrderPropertyCode('PREORDER');
        $property?->setValue('N');

        $active = current(\Kolos\Studio\Helpers\Elements::filterOnlyActive(IBLOCK_ID_SUPPLIES, 3));
        if (is_array($active) && isset($active)) {
            $activeId = $active['ID'];
        }

        $property = $propertyCollection->getItemByOrderPropertyCode('ID_SUPPLIES');
        $property?->setValue($activeId);

        $order->setBasket($actualBasket);
        $order->doFinalAction(true);
        $result = $order->save();

        $clearPrice = 0;
        foreach ($oldBasketProducts as $item) {
            $clearPrice += $item['QUANTITY'] * $item['PRICE'];
        }

        if ($result->isSuccess()) {
            foreach ($actualBasket as $item) {
                $qnt = $item->getQuantity();
                $oldValue = current(
                    ProductTable::getRow([
                        'filter' => [
                            'ID' => $item->getProductId(),
                        ],
                        'select' => ['QUANTITY'],
                    ])
                );
                $newVal = $oldValue;

//                $newVal = $oldValue - $qnt;

//                ProductTable::update(
//                    $item->getProductId(),
//                    ['QUANTITY' => $newVal < 0 ? 0 : $newVal]
//                );

                $productsAmount[] = [
                    'productId' => $item->getProductId(),
                    'amount' => $newVal < 0 ? 0 : $newVal,
                ];
            }

            return [
                'status' => true,
                'orderId' => $order->getId(),
                'clearList' => $oldBasketProducts,
                'orderPrice' => price_format($order->getPrice()),
                'clearPrice' => $clearPrice > 0 ? price_format($clearPrice) : 0,
                'productsAmount' => $productsAmount,
            ];
        } else {
            return [
                'status' => false,
                'error' => 'Системная ошибка, повторите попытку позже',
            ];
        }
    }

    protected static function formattedOrder(int $orderId): array
    {
        $productsDeclension = new Declension(' товар', ' товара', ' товаров');

        $order = Order::load($orderId);

        $propertyCollection = $order->getPropertyCollection();
        $property = $propertyCollection->getItemByOrderPropertyCode('DATE_CREATE');

        $item = [
            'ID' => $order->getId(),
            'DATE' => ($property && $property->getValue()) ? date(
                "d.m.Y",
                strtotime($property->getValue())
            ) : $order->getField(
                'DATE_INSERT'
            )->format('d.m.Y'),
            'PRICE' => price_format($order->getPrice()),
            'PRICE_FORMAT' => number_format($order->getPrice(), 2, '.', ' '),
            'STATUS_ID' => $order->getField('STATUS_ID'),
            'IS_PREORDER' => $propertyCollection->getItemByOrderPropertyCode('PREORDER')?->getValue() == 'Y',
            'SUPPLIES_ID' => $propertyCollection->getItemByOrderPropertyCode('ID_SUPPLIES')?->getValue(),
            'SUPPLIES_NAME' => \Kolos\Studio\Helpers\Elements::getNameById($propertyCollection->getItemByOrderPropertyCode('ID_SUPPLIES')?->getValue()),
        ];

        $basket = $order->getBasket();

        foreach ($basket as $basketItem) {
            $item['basket'][] = [
                'id' => $basketItem->getProductId(),
                'name' => $basketItem->getField('NAME'),
                'price' => number_format($basketItem->getPrice(), 2, '.', ' '),
                'quantity' => $basketItem->getQuantity(),
                'amount' => number_format($basketItem->getPrice() * $basketItem->getQuantity(), 2, '.', ' '),
            ];
        }

        $item['COUNT_ITEMS_FULL'] = count($item['basket']);
        $item['COUNT_ITEMS_FULL_TEXT'] = $item['COUNT_ITEMS_FULL'] . $productsDeclension->get(
                $item['COUNT_ITEMS_FULL']
            );

        $item['COUNT_ITEMS'] = count($item['basket']) - 1;
        $item['COUNT_ITEMS_TEXT'] = $item['COUNT_ITEMS'] . $productsDeclension->get($item['COUNT_ITEMS']);

        $item['PRODUCT_TITLE'] = current($item['basket'])['name'];

        return $item;
    }

    public static function getOrderList(array $filter,  int $limit = 50): array
    {
        return Order::getList([
            'filter' => $filter,
            'select' => ['ID'],
            'limit' => $limit,
            'group' => ['ID'],
            'order' => [
                'ID' => 'DESC'
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField(
                    'PROPERTY_VAL',
                    '\Bitrix\sale\Internals\OrderPropsValueTable',
                    ["=this.ID" => "ref.ORDER_ID"],
                    ["join_type" => "left"]
                ),
                new \Bitrix\Main\Entity\ReferenceField(
                    'PROPERTY_VAL2',
                    '\Bitrix\sale\Internals\OrderPropsValueTable',
                    ["=this.ID" => "ref.ORDER_ID"],
                    ["join_type" => "left"]
                ),
            ],
            "cache" => [
                "ttl" => 60,
            ],
        ])->fetchAll();
    }

    public static function getUserOrder(): array
    {
        global $USER;

        $result = [
            'active' => [],
            'unActive' => [],
        ];

        if ($USER->IsAuthorized()) {
            $active = current(\Kolos\Studio\Helpers\Elements::filterOnlyActive(IBLOCK_ID_SUPPLIES, 3));
            if (is_array($active) && isset($active)) {
                $activeId = $active['ID'];
            }
            if ($activeId > 0) {
                $orders = self::getOrderList(
                    [
                        'USER_ID' => $USER->GetID(),
                        '=PROPERTY_VAL.CODE' => 'ID_SUPPLIES',
                        '=PROPERTY_VAL.VALUE' => $activeId,
                        '=PROPERTY_VAL2.CODE' => 'PREORDER',
                        '=PROPERTY_VAL2.VALUE' => 'Y',
                    ],
                    100
                );

                foreach ($orders as $order) {
                    $result['active'][] = self::formattedOrder($order['ID']);
                }

                $orders = self::getOrderList(
                    [
                        'USER_ID' => $USER->GetID(),
                        '=PROPERTY_VAL.CODE' => 'ID_SUPPLIES',
                        '=PROPERTY_VAL.VALUE' => $activeId,
                        '=PROPERTY_VAL2.CODE' => 'PREORDER',
                        '!PROPERTY_VAL2.VALUE' => 'Y',
                    ],
                    50
                );

                foreach ($orders as $order) {
                    $result['active'][] = self::formattedOrder($order['ID']);
                }
            }


            $orders = self::getOrderList(
                [
                    'USER_ID' => $USER->GetID(),
                    '=PROPERTY_VAL.CODE' => 'ID_SUPPLIES',
                    '!PROPERTY_VAL.VALUE' => $activeId,
                    '=PROPERTY_VAL2.CODE' => 'PREORDER',
                    '=PROPERTY_VAL2.VALUE' => 'Y',
                ],
                100
            );

            foreach ($orders as $order) {
                $result['unActive'][] = self::formattedOrder($order['ID']);
            }

            $orders = self::getOrderList(
                [
                    'USER_ID' => $USER->GetID(),
                    '=PROPERTY_VAL.CODE' => 'ID_SUPPLIES',
                    '!PROPERTY_VAL.VALUE' => $activeId,
                    '=PROPERTY_VAL2.CODE' => 'PREORDER',
                    '!PROPERTY_VAL2.VALUE' => 'Y',
                ],
                50
            );

            foreach ($orders as $order) {
                $result['unActive'][] = self::formattedOrder($order['ID']);
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
