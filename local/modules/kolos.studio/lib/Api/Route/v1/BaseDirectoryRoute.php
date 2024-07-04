<?php

use Bitrix\Main\SystemException;
use Kolos\Studio\Integration\Product\ProductDirectory;

trait BaseDirectoryRoute
{
    private $classStore;

    public function childProcessTrait()
    {
        foreach ($this->arRequest as $key => $item) {
            if ($this->validateItem($item, $key) === true) {
                try {
                    $id = $this->store($item);
                    if ($id == 0) {
                        $this->isWarning = true;
                        $this->WarninText .= "Item {$item['code']} don't save" . PHP_EOL;
                    }
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

    protected function store(array $data): int
    {
        $fields = $this->fill($data);
        if (!is_array($fields) || count($fields) == 0) {
            return 0;
        }

        return $this->getClassStore()->store($fields);
    }

    protected function validateItem(array $item, $key): bool
    {

        if (!isset($item['code']) || empty($item['code'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value code" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValueTrait($item['code']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the code value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['name']) || empty($item['name'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value name" . PHP_EOL;
            return false;
        }

        if ($this->validateNameValueTrait($item['name']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the name value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        return true;
    }

    protected function validateTrait(): bool
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
        $keysNeed = ['code', 'name'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            $this->setError(400, 'The request structure is not valid!');
            return false;
        }

        return true;
    }

    private function validateCodeValueTrait($value): bool
    {
        return preg_match('/^([a-fA-F0-9-]){35,37}$/m', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    private function validateNameValueTrait($value): bool
    {
        return preg_match('/^([а-яА-Яa-zA-Z0-9Ёё !&-.`’\/(),+"\']){2,100}$/u', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    protected function fill(array $data): array
    {
        $fillKeys = $this->fillKeys;

        $ret = [];

        foreach ($data as $key => $datum) {
            if (isset($fillKeys[$key])) {
                $ret[$fillKeys[$key]] = $datum;
            }
        }
        return $ret;
    }

    private function getClassStore(): ProductDirectory
    {
        if (!($this->classStore instanceof ProductDirectory)) {
            $this->classStore = new ProductDirectory($this->getTableName());
        }

        return $this->classStore;
    }

    protected function checkPermissionTrait(): bool
    {
        return true;
    }

}
