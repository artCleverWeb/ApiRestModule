<?php

namespace Kolos\Studio\Api\Route\v1\Prices;

use Kolos\Studio\Api\Route\v1\BaseRoute;
use Bitrix\Main\SystemException;
use Kolos\Studio\Integration\Prices\ProductPrice;

class Prices extends BaseRoute
{

    public function childProcess()
    {
        $productPriceClass = new ProductPrice;
        foreach ($this->arRequest as $key => $item) {


            if ($this->validateItem($item, $key) === true) {
                try {
                    $productPriceClass->updatePriceByCode(
                        $item['priceCode'],
                        $item['goodCode'],
                        floatval($item['price'])
                    );
                } catch (SystemException $e) {
                    $this->isWarning = true;
                    $this->WarninText .= "Item $key: " . $e->getMessage();
                }
            }
        }

        $this->arResult = [
            'status' => true,
        ];
    }

    protected function validateItem(array $item, $key): bool
    {
        if (!isset($item['goodCode']) || empty($item['goodCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value goodCode" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['goodCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the goodCode value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['priceCode']) || empty($item['priceCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value priceCode" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['priceCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the priceCode value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['price']) || empty($item['price'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value price" . PHP_EOL;
            return false;
        }

        if (filter_var($item['price'], FILTER_VALIDATE_FLOAT) === false) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the price value must be an float" . PHP_EOL;
            return false;
        }

        return true;
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
        $keysNeed = [
            'goodCode',
            'priceCode',
            'price',
        ];

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