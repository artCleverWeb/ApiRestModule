<?php

namespace Kolos\Studio\Api\Route\v1\Orders;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Engine\Bitrix;
use Kolos\Studio\Api\Route\v1\BaseRoute;

class Extraorders extends BaseRoute
{
    public function childProcess()
    {
        Loader::includeModule('sale');

        $statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList([
            'order' => ['STATUS.SORT' => 'ASC'],
            'filter' => ['STATUS.TYPE' => 'O', 'LID' => LANGUAGE_ID],
            'select' => ['STATUS_ID', 'NAME', 'DESCRIPTION', 'NOTIFY' => 'STATUS.NOTIFY'],
        ])->fetchAll();

        $arrStatus = [];

        foreach ($statusResult as $item) {
            $arrStatus[$item['STATUS_ID']] = $item['NAME'];
        }

        $orders = \Bitrix\Sale\Order::loadByFilter([
            'filter' => [
                '>=DATE_INSERT' => ConvertTimeStamp(strtotime($this->arRequest['dateStart'] . ' 00:00:00'), "FULL"),
                '<=DATE_INSERT' => ConvertTimeStamp(strtotime($this->arRequest['dateEnd'] . ' 23:59:59'), "FULL"),
            ]
        ]);

        $result = [];

        foreach ($orders as $order) {
            $basketInfo = [];

            $basket = $order->getBasket();

            foreach ($basket->getBasketItems() as $basketItem) {
                $basketInfo[] = [
                    'goodCode' => \Kolos\Studio\Helpers\Elements::getXmlCodeById($basketItem->getProductId()),
                    'quantity' => $basketItem->getQuantity(),
                    'price' => $basketItem->getPrice(),
                ];
            }
            $propertyCollection = $order->getPropertyCollection();
            $property = $propertyCollection->getItemByOrderPropertyCode('DATE_CREATE');

            $result[] = [
                'orderSiteCode' => $order->getField('XML_ID'),
                'clientCode' => \Kolos\Studio\Helpers\Users::getXmlCodeById($order->getUserId()),
                'date' => ($property && $property->getValue()) ? date(
                    "c",
                    strtotime($property->getValue())
                ) : $order->getField(
                    'DATE_INSERT'
                )->format('c'),
                'sum' => $order->getPrice(),
                'status' => $arrStatus[$order->getField('STATUS_ID')],
                'goods' => $basketInfo,
            ];
        }

        $this->arResult = $result;
    }

    protected function validate(): bool
    {
        if ($this->requestMethod !== 'GET') {
            $this->setError(400, 'Method ' . $this->requestMethod . ' not allowed!');
            return false;
        }

        if (empty($this->arRequest) || !is_array($this->arRequest)) {
            $this->setError(400, 'Request is empty!');
            return false;
        }

        $firstLine = $this->arRequest;

        $keysRequest = array_keys($firstLine);
        $keysNeed = ['dateStart'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            $this->setError(400, 'The request structure is not valid!');
            return false;
        }

        if (!isset($this->arRequest['dateEnd']) || empty($this->arRequest['dateEnd'])) {
            $this->arRequest['dateEnd'] = date('d-m-Y');
        }

        if (preg_match(
                '/^(\d){2}-(\d){2}-(\d){4}$/m',
                $this->arRequest['dateStart'],
                $matches,
                PREG_OFFSET_CAPTURE
            ) != 1) {
            $this->setError(400, 'The DateStart format is not valid!');
            return false;
        }

        if (preg_match(
                '/^(\d){2}-(\d){2}-(\d){4}$/m',
                $this->arRequest['dateEnd'],
                $matches,
                PREG_OFFSET_CAPTURE
            ) != 1) {
            $this->setError(400, 'The DateEnd format is not valid!');
            return false;
        }

        $date1 = \DateTime::createFromFormat('d-m-Y', $this->arRequest['dateStart']);
        $date2 = \DateTime::createFromFormat('d-m-Y', $this->arRequest['dateEnd']);
        $dayDiff = $date1->diff($date2)->format('%a');

        if ($dayDiff > 30) {
            $this->setError(400, 'Разница между датами превышает 30 дней');
            return false;
        }
        return true;
    }

    protected function checkPermission(): bool
    {
        return true;
    }
}
