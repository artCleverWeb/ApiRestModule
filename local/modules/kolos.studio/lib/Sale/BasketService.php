<?php

namespace Kolos\Studio\Sale;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;
use CSaleBasket;

use Bitrix\Sale;
use \Bitrix\Main\Context;

class BasketService
{
    protected static ?BasketService $instance = null;
    private ?\Bitrix\Sale\BasketBase $basket = null;

    private function __construct()
    {
        \Bitrix\Main\Loader::includeModule('sale');
        \Bitrix\Main\Loader::includeModule('catalog');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private static function getItemValueByCode($item, $code)
    {
        $basketPropertyCollection = $item->getPropertyCollection()->getPropertyValues();

        return $basketPropertyCollection[$code]['VALUE'] ?? false;
    }

    public static function getFuserId($userId = 0)
    {
        return $userId == 0 ? Fuser::getId() : Fuser::getIdByUserId($userId);
    }

    public function getFormattedBasket()
    {
        $result = [];

        $basket = $this->actualBasket();
        $basketItems = $basket->getBasketItems();

        foreach ($basketItems as $item) {
            $productIds[] = $item->getProductId();
            $result['basket'][] = [
                'ID' => $item->getId(),
                'PRODUCT_ID' => $item->getProductId(),
                'NAME' => $item->getField('NAME'),
                'QUANTITY' => $item->getQuantity(),
                'PRICE' => price_format($item->getPrice()),
            ];
        }

        $result['totalPrice'] = $this->getBasketPrice();

        return $result;
    }

    public function actualBasket(): \Bitrix\Sale\BasketBase
    {
        \Bitrix\Main\Loader::includeModule('sale');

        $basket = $this->getBasket();

        $basketItems = $basket->getBasketItems();
        $productIds = [];

        foreach ($basketItems as $item) {
            $productIds[] = $item->getProductId();
        }

        if (!empty($productIds)) {
            $productInfo = ProductService::getAvail([
                'ID' => $productIds,
                'IBLOCK_ID' => IBLOCK_ID_CATALOG,
            ]);

            /** @var BasketItem $item */
            foreach ($basketItems as $item) {
                $product = $productInfo[$item->getProductId()];

//                if ($product['AVAILABLE'] != 'Y' || $product['ACTIVE'] != 'Y') {
//                    $item->delete();
//                }
            }

            $basket->save();
        }

        return $basket;
    }

    public static function getItemByProductId($id, $basket = null)
    {
        if ($basket == null) {
            $basket = self::getInstance()->getBasket();
        }

        $basketItem = $basket->getExistsItem('catalog', $id);

        if ($basketItem === null) {
            foreach ($basket as $item) {
                if ($item->getField('PRODUCT_ID') == $id) {
                    return $item;
                }
            }
        }

        return $basketItem;
    }

    public function getQuantityInBasket()
    {
        $response = [];
        $basket = $this->getBasketCustom();
        $currentBasketItems = $basket['BASKET'] ?? [];

        foreach ($currentBasketItems as $key => $item) {
            $response[] = [
                'PRODUCT_ID' => $item['PRODUCT_ID'],
                'QUANTITY' => $item['QUANTITY'],
            ];
        }

        return [
            $response
        ];
    }

    public function addToBasket($id, $quantity)
    {
        \Bitrix\Main\Loader::includeModule('sale');

        $basket = self::getInstance()->getBasket();

        $basketItem = self::getItemByProductId($id, $basket);

        if ($basketItem === null) {
            $basketItem = $basket->createItem('catalog', $id);

            $fields = [
                'QUANTITY' => $quantity,
                'CURRENCY' => CurrencyManager::getBaseCurrency(),
                'LID' => Context::getCurrent()->getSite(),
                'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProviderCustom',
            ];
//            print_r($fields);
//            die();
            $basketItem->setFields($fields);

            $basket->addItem($basketItem);
        }

        $res = $basket->save();

        return $res->isSuccess()
            ? ['success' => true, 'error' => '']
            : ['success' => false, 'error' => $res->getErrorMessages()];
    }

    public function updateBasket($id, $quantity)
    {
        if($quantity <= 0){
            return $this->deleteItem($id, $quantity);
        }

        $basket = $this->getBasket();
        if ($item = $basket->getExistsItem('catalog', $id)) {
            $item->setField('QUANTITY', $quantity);
            $res = $basket->save();


            return $res->isSuccess()
                ? ['success' => true, 'error' => '']
                : ['success' => false, 'error' => $res->getErrorMessages()];
        }
        else{
            return $this->addToBasket($id, $quantity);
        }

    }

    public function deleteItem($id, $quantity)
    {
        $basket = $this->getBasket();

        if ($quantity < 1) {
            $item = $basket->getExistsItem('catalog', $id);
            $item->delete();
        } else {
            if ($item = $basket->getExistsItem('catalog', $id)) {
                $newQty = $item->getQuantity();
                $newQty -= $quantity;

                if ($newQty <= 0) {
                    $item->delete();
                } else {
                    $item->setField('QUANTITY', $newQty);
                }
            } else {
                self::clearBasket();
            }
        }

        $res = $basket->save();

        return $res->isSuccess()
            ? ['success' => true, 'error' => '']
            : ['success' => false, 'error' => $res->getErrorMessages()];
    }

    public function getBasket($update = false)
    {
        if (is_null($this->basket) || $update) {
            $this->basket = \Bitrix\Sale\Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        }

        return $this->basket;
    }

    public static function createNewBasket(): Sale\BasketBase
    {
        return \Bitrix\Sale\Basket::create(Context::getCurrent()->getSite());
    }

    public static function clearBasket()
    {
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
            \Bitrix\Sale\Fuser::getId(),
            \Bitrix\Main\Context::getCurrent()->getSite()
        );
        $basketItems = $basket->getBasketItems();
        $ids = [];
        foreach ($basketItems as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }

        CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

        return ['status' => true];
    }

    public function getBasketPrice()
    {
        return price_format($this->getBasket()->getPrice());
    }
}
