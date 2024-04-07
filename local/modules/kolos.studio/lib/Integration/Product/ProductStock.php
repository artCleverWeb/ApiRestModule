<?php

namespace Kolos\Studio\Integration\Product;


use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Kolos\Studio\Helpers\HighloadBlock;

Loader::includeModule('catalog');

class ProductStock
{
    private int $productId = 0;
    private string $productCode = '';
    private int $quantity = 0;
    private int $iblockId = 0;
    public string $lastError = '';

    function __construct(string $productCode, int $quantity = 0)
    {
        $this->productCode = $productCode;
        $this->quantity = $quantity;

        if (defined('IBLOCK_ID_CATALOG')) {
            $this->iblockId = IBLOCK_ID_CATALOG;
        } else {
            throw new \ErrorException('The constant with ID Iblock of products is not filled in');
        }
    }

    public function store(): int
    {
        if($this->iblockId == 0)
        {
            $this->lastError = 'The constant with ID Iblock of products is not filled in';
            return 0;
        }

        if($this->getProductId() == 0){
            $this->lastError = 'Product with code '. $this->productCode .' not found';
            return 0;
        }

        $entityId = $this->find();

        $fields = $this->fill();

        if($entityId == 0){
            $result = ProductTable::add($fields);

            if (!$result->isSuccess()) {
                throw new SystemException(implode(', ', $result->getErrorMessages()));
            }

            $entityId = (int)$result->getId();

        }
        else{
            unset($fields['ID']);
            $result = ProductTable::update($entityId, $fields);

            if (!$result->isSuccess()) {
                throw new SystemException(implode(', ', $result->getErrorMessages()));
            }
        }

        return $entityId;
    }

    private function fill(): array
    {
        return [
            'ID' => $this->productId,
            'QUANTITY' => $this->quantity,
        ];
    }

    private function find(): int
    {
        $product = ProductTable::getList([
            'filter' => [
                "ID" => $this->productId,
            ],
            'select' => [
                "ID",
            ],
        ])->fetch();

        if ($product) {
            return $product['ID'];
        }

        return 0;
    }

    private function getProductId(): int
    {
        $product = \Kolos\Studio\Helpers\Elements::getByXmlCode($this->productCode, $this->iblockId);

        if (isset($product['ID']) && $product['ID'] > 0) {
            $this->productId = $product['ID'];

            return $product['ID'];
        }

        return 0;
    }
}