<?php

namespace Kolos\Studio\Api\Route;

use Bitrix\Main\SystemException;

class StockGoodRoute extends BaseRoute
{

    public function childProcess()
    {
        foreach ($this->arRequest as $key => $item) {
            if ($this->validateItem($item, $key) === true) {
                try {
                    $class = new \Kolos\Studio\Integration\Prices\TypePrices(
                        $item['code'],
                        $item['name'],
                        $item['title']['ru']
                    );
                    $id = $class->store();

                    if ($id == 0) {
                        $this->isWarning = true;
                        $this->WarninText .= $class->lastError;
                    }
                    unset($class);
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
        if (!isset($item['goodCode']) || !empty($item['goodCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value goodCode" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['goodCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the goodCode value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['quantity']) || !empty($item['quantity'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value quantity" . PHP_EOL;
            return false;
        }

        if (filter_var($item['quantity'], FILTER_VALIDATE_INT) === false) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the quantity value must be an integer" . PHP_EOL;
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
        $keysNeed = ['goodCode', 'quantity'];

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
