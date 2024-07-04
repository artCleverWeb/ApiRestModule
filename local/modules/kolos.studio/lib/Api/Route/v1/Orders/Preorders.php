<?php

namespace Kolos\Studio\Api\Route\v1\Orders;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Engine\Bitrix;
use Kolos\Studio\Api\Route\v1\BaseRoute;

class Preorders extends BaseRoute
{

    public function childProcess()
    {
        foreach ($this->arRequest as $key => $item) {
            if ($this->validateItem($item, $key) === true) {
                try {
                    $order = new \Kolos\Studio\Integration\Sale\Order($item['orderSiteCode']);

                    if ($order->store($item) !== true) {
                        $this->isWarning = true;
                        $this->WarninText .= "Ошибка создания заказа {$item['orderSiteCode']}";
                    }

                    unset($order);
                } catch (SystemException $e) {
                    $this->isWarning = true;
                    $this->WarninText .= "Ошибка создания заказа {$item['orderSiteCode']}: " . $e->getMessage();
                }
            }
        }

        $this->arResult = [
            'status' => true,
        ];
    }

    protected function validateItem(array &$item, $key): bool
    {
        if (!isset($item['orderSiteCode']) || empty($item['orderSiteCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value orderSiteCode" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['orderSiteCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the orderSiteCode value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['clientCode']) || empty($item['clientCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value clientCode" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['clientCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the clientCode value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['status']) || empty($item['status'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value status" . PHP_EOL;
            return false;
        }

        if (!isset($item['dateAdd']) || empty($item['dateAdd'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value dateAdd" . PHP_EOL;
            return false;
        }

        if ($this->validateDateValue($item['dateAdd']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the dateAdd value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['sum']) || empty($item['sum'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value sum" . PHP_EOL;
            return false;
        }

        if (filter_var($item['sum'], FILTER_VALIDATE_FLOAT) === false) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the sum value must be an float" . PHP_EOL;
            return false;
        }

        if (!isset($item['goods']) || empty($item['goods']) || !is_array($item['goods']) || count($item['goods']) < 1) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value goods" . PHP_EOL;
            return false;
        }
        return true;
    }

    protected function validatePriceValue($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    protected function validateDateValue(string $value): bool
    {
        return preg_match(
                '/^(\d){4}-(\d){2}-(\d){2}T(\d){2}:(\d){2}:(\d){2}$/m',
                $value,
                $matches,
                PREG_OFFSET_CAPTURE
            ) == 1;
    }

    protected function validateNameValue($value): bool
    {
        return parent::validateNameValue($value);
    }

    protected function validateCodeValue($value): bool
    {
        return parent::validateCodeValue($value);
    }

    protected function validate(): bool
    {
        if ($this->requestMethod !== 'POST') {
            $this->setError(400, 'Method ' . $this->requestMethod . ' not allowed!');
            return false;
        }

        if (empty($this->arRequest) || !is_array($this->arRequest)) {
            $this->setError(400, 'Request is empty!');
            return false;
        }

        $firstLine = current($this->arRequest);

        $keysRequest = array_keys($firstLine);
        $keysNeed = ['orderSiteCode', 'clientCode', 'dateAdd', 'status', 'sum', 'goods'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            $this->setError(400, 'The request structure is not valid!');
            return false;
        }

        return true;
    }

    protected function checkPermission(): bool
    {
        return true;
    }
}
