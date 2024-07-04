<?php

namespace Kolos\Studio\Integration\Prices;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Kolos\Studio\Integration\Product\Good;
use \Bitrix\Catalog\Model\Price;

Loader::includeModule('catalog');

class ProductPrice
{
    public function updatePriceByCode(string $priceCode, string $productCode, float $price): bool
    {
        try {
            $productClass = new Good;
            $productClass->getProductEntity();
            $priceTypeClass = new TypePrices($priceCode, '', '');

            $productId = $productClass->getByCode($productCode);

            if ($productId < 1) {
                throw new SystemException("Product $productCode not found!");
                return false;
            }

            $priceTypeId = $priceTypeClass->find();

            if ($priceTypeId < 1) {
                throw new SystemException("Price Type  $priceCode not found!");
                return false;
            }

            $priceArr = $this->getPriceByProductCode($priceTypeId, $productId);

            if (empty($priceArr) || $priceArr['ID'] < 0) {
                $result = Price::add([
                    'PRODUCT_ID' => $productId,
                    'CATALOG_GROUP_ID' => $priceTypeId,
                    'PRICE' => $price,
                    'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                ]);
            } else {
                $result = Price::update($priceArr['ID'], [
                    'PRICE' => $price,
                ]);
            }

            if (!$result->isSuccess()) {
                throw new SystemException(implode(', ', $result->getErrorMessages()));
                return false;
            }

            return true;
        } catch (\Exception $error) {
            throw new SystemException($error->getMessage());
            return false;
        }
    }

    private function getPriceByProductCode(int $priceTypeId, int $productId): array
    {
        $price = Price::getList([
            'select' => [
                'ID'
            ],
            'filter' => [
                'CATALOG_GROUP_ID' => $priceTypeId,
                'PRODUCT_ID' => $productId
            ]
        ])->fetch();

        return $price ? $price : [];
    }

}
