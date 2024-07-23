<?php

namespace Kolos\Studio\Integration\Sale;

use Bitrix\Calendar\Core\Queue\Exception\Exception;
use \Bitrix\Main\Context,
    \Bitrix\Currency\CurrencyManager;

class Order
{
    protected \Bitrix\Sale\Order $order;
    protected int $userId = 0;
    protected int $personType = 1;
    protected Basket $basket;
    protected string $orderXmlId = '';

    function __construct(string $orderXmlId)
    {
        if (!defined('IBLOCK_ID_CATALOG') || strlen(IBLOCK_ID_CATALOG) == 0) {
            throw new \ErrorException ('Не определена константа IBLOCK_ID_CATALOG');
            return false;
        }

        if (!defined('IBLOCK_ID_CATALOG_EMPTY') || strlen(IBLOCK_ID_CATALOG_EMPTY) == 0) {
            throw new \ErrorException ('Не определена константа IBLOCK_ID_CATALOG_EMPTY');
            return false;
        }

        \Bitrix\Main\Loader::includeModule("sale");

        $this->orderXmlId = $orderXmlId;
    }

    public function store(array $data): bool
    {
        try {
            $statusId = $this->getStatusCode($data['status']);

            if (strlen($statusId) < 1) {
                throw new \ErrorException (
                    "Заказ {$this->orderXmlId}: не найден статус заказа {$data['status']}"
                );
                return false;
            }

            $userId = \Kolos\Studio\Helpers\Users::getIdByXmlCode($data['clientCode']);

            if ($userId < 1) {
                throw new \ErrorException (
                    "Заказ {$this->orderXmlId}: не найден пользователь {$data['clientCode']}"
                );
                return false;
            }

            $this->userId = $userId;

            $this->order = $this->getOrder();

            if (!$this->order) {
                throw new \ErrorException (
                    "Заказ {$this->orderXmlId}: при получении объекта заказа возникла критическая ошибка"
                );
                return false;
            }

            $basketClass = new Basket($this->order);

            if ($basketClass->store($data['goods']) === false) {
                throw new \ErrorException (
                    "Заказ {$this->orderXmlId}: при сохранении корзины возникли ошибки"
                );
                return false;
            }

            $this->order->setField('STATUS_ID', $statusId);

            $propertyCollection = $this->order->getPropertyCollection();
            $property = $propertyCollection->getItemByOrderPropertyCode('DATE_CREATE');
            if ($property) {
                $date = $data['dateAdd'] ? strtotime($data['dateAdd']) : time();
                $property->setValue(date('d.m.Y H:i:s', $date));
            }

            if($this->order->isNew()){
                $activeId = 0;

                $active = current(\Kolos\Studio\Helpers\Elements::filterOnlyActive(IBLOCK_ID_SUPPLIES, 3));
                if(is_array($active) && isset($active)){
                    $activeId = $active['ID'];
                }

                $property = $propertyCollection->getItemByOrderPropertyCode('ID_SUPPLIES');
                $property?->setValue($activeId);

                $property = $propertyCollection->getItemByOrderPropertyCode('PREORDER');
                $property?->setValue('Y');
            }

            $result = $this->order->save();

            if ($result->isSuccess()) {
                return true;
            }
        } catch (\Exception $exception) {
            throw new \ErrorException ($exception->getMessage());
            return false;
        }

        return false;
    }

    protected function getOrder(): \Bitrix\Sale\Order
    {
        $orders = \Bitrix\Sale\Order::loadByFilter([
            'filter' => [
                'XML_ID' => $this->orderXmlId,
            ],
            'limit' => 1,
        ]);

        if (count($orders) > 0) {
            return $orders[0];
        }

        return $this->createOrder();
    }

    protected function createOrder(): \Bitrix\Sale\Order
    {
        $siteId = Context::getCurrent()->getSite();
        $currencyCode = CurrencyManager::getBaseCurrency();
        $order = \Bitrix\Sale\Order::create($siteId, $this->userId);
        $order->setPersonTypeId($this->personType);
        $order->setField('XML_ID', $this->orderXmlId);
        $order->setField('CURRENCY', $currencyCode);

        return $order;
    }

    protected function getStatusCode(string $statusName): string
    {
        if (strlen($statusName) < 0) {
            return "";
        }

        $statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList([
            'order' => ['STATUS.SORT' => 'ASC'],
            'filter' => ['STATUS.TYPE' => 'O', 'LID' => LANGUAGE_ID],
            'select' => ['STATUS_ID', 'NAME', 'DESCRIPTION', 'NOTIFY' => 'STATUS.NOTIFY'],
        ])->fetchAll();

        foreach ($statusResult as $item) {
            $arrStatus[$item['NAME']] = $item['STATUS_ID'];
        }

        if (isset($arrStatus[$statusName])) {
            return $arrStatus[$statusName];
        }

        return "";
    }
}
