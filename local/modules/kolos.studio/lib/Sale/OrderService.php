<?php

namespace Kolos\Studio\Sale;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
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
}
