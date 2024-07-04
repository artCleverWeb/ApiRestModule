<?php

namespace Kolos\Studio\Integration\Sale;

use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Context;

class Basket
{
    protected \Bitrix\Sale\Order $order;
    protected string $siteId = '';
    protected array $saveBasket = [];

    function __construct(\Bitrix\Sale\Order $order)
    {
        $this->order = $order;
        $this->siteId = Context::getCurrent()->getSite();
    }

    public function store(array $basket = []): bool
    {
        try {
            if ($this->prepare($basket) === false) {
                throw new \ErrorException ("Системная ошибка: При подготовке корзины для сохранения возникли ошибки");
            }

            $orderBasket = $this->getBasket();

            $isNewBasket = $orderBasket->isEmpty();

            $basketItems = $orderBasket->getBasketItems();

            foreach ($basketItems as $item) {
                $itemCode = $item->getField('XML_ID');

                if (isset($this->saveBasket[$itemCode])) {
                    $updateInfo = $this->saveBasket[$itemCode];

                    $item->setField('QUANTITY', $updateInfo['quantity']);
                    $item->setField('PRICE', $updateInfo['price']);
                    $item->setField('BASE_PRICE', $updateInfo['price']);
                    $item->setField('NAME', $updateInfo['name']);

                    $item->save();

                    unset($this->saveBasket[$itemCode]);
                }
                else{
                    $item->delete();
                }
            }

            foreach ($this->saveBasket as $item) {
                $basketItem = $orderBasket->createItem('catalog', $item['productId']);
                $basketItem->setFields([
                    'QUANTITY' => $item['quantity'],
                    'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                    'LID' => $this->siteId,
                  //  'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                    'NAME' => $item['name'],
                    'BASE_PRICE' => $item['price'],
                    'PRICE' => $item['price'],
                    'CUSTOM_PRICE' => 'Y',
                    'XML_ID'=> $item['xml_id'],
                ]);
                $basketItem->setField('QUANTITY', $item['quantity']);

                $basketItem->save();
            }


            $orderBasket->save();

            if($isNewBasket === true) {
                $this->order->setBasket($orderBasket);
            }

            return true;
        } catch (\Exception $exception) {
            throw new \ErrorException ($exception->getMessage());
            return false;
        }

        return false;
    }

    protected function prepare(array $basket): bool
    {
        if (count($basket) < 1) {
            throw new \ErrorException ("Ошибка подготовки корзины: Не передана корзина заказа");
            return false;
        }

        foreach ($basket as $item) {
            if ($this->validateCodeValue($item['goodCode']) === false) {
                throw new \ErrorException (
                    "Ошибка подготовки корзины: Код товара {$item['goodCode']} не соответствует маске"
                );
                return false;
            }

            if ($this->validateNameValue($item['name']) === false) {
                throw new \ErrorException (
                    "Ошибка подготовки корзины: Название товара {$item['name']} не соответствует маске"
                );
                return false;
            }

            if ($this->validateQuantityValue($item['quantity']) === false) {
                throw new \ErrorException (
                    "Ошибка подготовки корзины: Кол-во товара {$item['name']} недопустимое значение ({$item['quantity']})"
                );
                return false;
            }

            if ($this->validatePriceValue($item['price']) === false) {
                throw new \ErrorException (
                    "Ошибка подготовки корзины: Цена товара {$item['name']} недопустимое значение ({$item['price']})"
                );
                return false;
            }

            $productId = $this->getProductId($item);

            if ($productId < 1) {
                throw new \ErrorException (
                    "Ошибка подготовки корзины: не удалось найти или создать товар  {$item['name']}"
                );
                return false;
            }

            $this->saveBasket[$item['goodCode']] = [
                'xml_id' => $item['goodCode'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'name' => $item['name'],
                'productId' => $productId,
            ];
        }

        return true;
    }

    protected function getBasket(): \Bitrix\Sale\Basket
    {
        $basket = $this->order->getBasket();

        if ($basket) {
            return $basket;
        }

        $basket = \Bitrix\Sale\Basket::create($this->siteId);
        $basket->setFUserId(\Bitrix\Sale\Fuser::getId());

        return $basket;
    }

    protected function getProductId(array $item): int
    {
        $productClass = Iblock::wakeUp(IBLOCK_ID_CATALOG)->getEntityDataClass();

        $product = $productClass::getList([
            'filter' => ['XML_ID' => $item['goodCode']],
            'cache' => [
                'ttl' => 600
            ],
        ])->fetchObject();

        if ($product) {
            return $product['ID'];
        }

        $productEmptyClass = Iblock::wakeUp(IBLOCK_ID_CATALOG_EMPTY)->getEntityDataClass();

        $product = $productEmptyClass::getList([
            'filter' => ['XML_ID' => $item['goodCode']],
            'cache' => [
                'ttl' => 600
            ],
        ])->fetchObject();

        if ($product) {
            return $product['ID'];
        }

        return $this->createEmptyProduct($item);
    }

    protected function createEmptyProduct(array $item): int
    {
        $productEmptyClass = Iblock::wakeUp(IBLOCK_ID_CATALOG_EMPTY)->getEntityDataClass();

        if ($productEmptyClass) {
            $productClass = $productEmptyClass::createObject();

            $productClass->setName($item['name']);
            $productClass->setXmlId($item['goodCode']);

            $result = $productClass->save();

            if ($result->isSuccess()) {
                Price::add([
                    'PRODUCT_ID' => $result->getId(),
                    'CATALOG_GROUP_ID' => 3,
                    'PRICE' => $item['price'],
                    'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                ]);

                ProductTable::add(
                    [
                        'ID' => $result->getId(),
                        'QUANTITY' => 100000,
                    ]
                );
                return $result->getId();
            }
        }
        return 0;
    }

    private function validateCodeValue($value): bool
    {
        return preg_match('/^([a-fA-F0-9-]){35,37}$/m', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    private function validateNameValue($value): bool
    {
        return preg_match('/^([а-яА-Яa-zA-Z0-9Ёё !&-.`’\/(),+"\']){2,100}$/u', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    private function validateQuantityValue($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value > 0;
    }

    private function validatePriceValue($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false && (float)$value > 0;
    }
}
