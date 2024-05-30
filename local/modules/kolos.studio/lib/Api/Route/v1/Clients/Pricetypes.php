<?php

namespace Kolos\Studio\Api\Route\v1\Clients;

use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Loader;
use Kolos\Studio\Api\Route\v1\BaseRoute;

class Pricetypes extends BaseRoute
{

    protected array $priceType = [];

    public function childProcess()
    {
        $this->getPriceType();

        foreach ($this->arRequest as $key => $item) {
            if ($this->validateItem($item, $key) === true) {
                $userId = \Kolos\Studio\Helpers\Users::getIdByXmlCode($item['clientCode']);

                if ($userId < 1) {
                    $this->isWarning = true;
                    $this->WarninText .= "Для элемента $key не найден пользователь xmlId = {$item['clientCode']}" . PHP_EOL;
                } else {
                    $CUser = new \CUser();
                    $CUser->Update($userId, [
                        "UF_PRICE_TYPE_ID" => $this->priceType[$item['priceCode']],
                    ]);
                }
            }
        }

        $this->arResult = [
            'status' => true,
        ];
    }

    protected function getPriceType(): void
    {
        Loader::includeModule('catalog');

        $group = GroupTable::getList([
            'select' => [
                "ID",
                "XML_ID"
            ],
        ])->fetchAll();

        foreach ($group as $item) {
            $this->priceType[$item['XML_ID']] = $item['ID'];
        }
    }

    protected function validateItem(array $item, string $key)
    {
        if (!isset($item['clientCode']) || empty($item['clientCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "Для элемента $key значение свойства clientCode пустое" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['clientCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "Для элемента $key значение свойства clientCode не соответствует маске ({$item['clientCode']})" . PHP_EOL;
            return false;
        }

        if (!isset($item['priceCode']) || empty($item['priceCode'])) {
            $this->isWarning = true;
            $this->WarninText .= "Для элемента $key значение свойства priceCode пустое" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['priceCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "Для элемента $key значение свойства priceCode не соответствует маске ({$item['priceCode']})" . PHP_EOL;
            return false;
        }

        if (empty($this->priceType) || count($this->priceType) < 1) {
            $this->isWarning = true;
            $this->WarninText .= "Не созданы типы цен на сайте" . PHP_EOL;
            return false;
        }

        if (!isset($this->priceType[$item['priceCode']])) {
            $this->isWarning = true;
            $this->WarninText .= "Для элемента $key не найдено соответствие типу цены ({$item['priceCode']})" . PHP_EOL;
            return false;
        }

        return true;
    }

    protected function validateCodeValue($value): bool
    {
        return preg_match('/^([a-zA-Z0-9-]){2,37}$/m', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
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
        $keysNeed = ['clientCode', 'priceCode'];

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
