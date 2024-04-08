<?php

use Bitrix\Main\SystemException;
use Kolos\Studio\Integration\Product\ProductDirectory;

trait BaseDirectoryRoute
{
    private $classStore;

    public function childProcessTrait()
    {
        foreach ($this->arRequest as $key => $item) {
            print_r($item);
            die();
//            if ($this->validateItem($item, $key) === true) {
//                try {
//                    $class = new \Kolos\Studio\Integration\Prices\TypePrices(
//                        $item['code'],
//                        $item['name'],
//                        $item['title']['ru']
//                    );
//                    $id = $class->store();
//
//                    if ($id == 0) {
//                        $this->isWarning = true;
//                        $this->WarninText .= $class->lastError;
//                    }
//                    unset($class);
//                } catch (SystemException $e) {
//                    $this->isWarning = true;
//                    $this->WarninText .= "Item $key: " . $e->getMessage();
//                }
//            }
        }
    }

    protected function store(array $data): int
    {
        $fields = $this->fill($data);
        if (!is_array($fields) || count($fields) == 0) {
            return 0;
        }

        return $this->getClassStore()->store($fields);
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

    private function validateCodeValueTrait($value): bool
    {
        preg_match('/^([a-zA-Z0-9-]){2,50}$/m', $value, $matches, PREG_OFFSET_CAPTURE, 0);
        return count($matches) > 0;
    }

    private function validateNameValueTrait($value): bool
    {
        preg_match('/^([а-яА-Яa-zA-Z0-9 &-]){2,25}$/m', $value, $matches, PREG_OFFSET_CAPTURE, 0);
        return count($matches) > 0;
    }
}