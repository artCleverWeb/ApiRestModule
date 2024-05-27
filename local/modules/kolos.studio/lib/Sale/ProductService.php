<?php

namespace Kolos\Studio\Sale;

use Bitrix\Main\Context;
use Kolos\Studio\Helpers\CacheManager;

class ProductService
{
    public static function getAvail($filter)
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        \Bitrix\Main\Loader::includeModule('sale');

        $elementsQ = \CIBlockElement::GetList(
            ['IBLOCK_SECTION_ID' => 'asc', 'SORT' => 'asc', 'NAME' => 'asc'],
            $filter,
            false,
            false,
            [
                'ID',
                'ACTIVE',
                'AVAILABLE',
                'QUANTITY',
                'QUANTITY_TRACE',
            ]
        );

        while ($element = $elementsQ->GetNext()) {
            $result[$element['ID']] = $element;
        }

        return $result;
    }

}
