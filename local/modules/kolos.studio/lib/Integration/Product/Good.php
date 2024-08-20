<?php

namespace Kolos\Studio\Integration\Product;

use \Bitrix\Iblock\Iblock;
use \Bitrix\Iblock\Elements\ElementTable;

class Good
{
    public $productClass;
    public $product;

    function __construct()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
    }

    public function getProductEntity(): bool|Iblock
    {
        if (!defined('IBLOCK_ID_CATALOG') || strlen(IBLOCK_ID_CATALOG) == 0) {
            throw new \ErrorException ('Parameters IBLOCK_ID_CATALOG not defined');
            return false;
        }

        $this->productClass = Iblock::wakeUp(IBLOCK_ID_CATALOG)->getEntityDataClass();
        return $this->productClass;
    }

    public function getByCode(string $code)
    {
        $product = $this->productClass::getList([
            'filter' => ['XML_ID' => $code],
            'cache' => [
                'ttl' => 600
            ],
        ])->fetchObject();

        if ($product) {
            return $product['ID'];
        }

        return 0;
    }

    public function findOrCreate(string $code)
    {
        $product = $this->productClass::getList([
            'filter' => ['XML_ID' => $code],
            'select' => ['*', 'COLOR'],
            'cache' => [
                'ttl' => 600
            ],
        ])->fetchObject();

        if ($product) {
            return $product;
        } else {
            return $this->productClass::createObject();
        }
    }
}
