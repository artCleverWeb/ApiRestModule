<?php

namespace Kolos\Studio\Helpers;

use Bitrix\Iblock\ElementTable;

class Elements
{
    public static function isActive($elementId, $iblockId = false)
    {
        $filter = [
            'ID' => $elementId,
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = \CIBlockElement::GetList([], $filter, false, false, ['ID']);

        return $res->fetch();
    }

    public static function filterOnlyActiveIds($ids, $iblockId = false)
    {
        $ids = array_wrap($ids);

        if (empty($ids)) {
            return [];
        }

        $filter = [
            'ID' => array_wrap($ids),
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = \CIBlockElement::GetList([], $filter, false, false, ['ID']);

        $resIds = [];

        while ($el = $res->Fetch()) {
            $resIds[] = $el['ID'];
        }

        return $resIds;
    }

    public static function filterOnlyActive($iblockId = false, int $limit = 0)
    {
        $filter = [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = \CIBlockElement::GetList(["ACTIVE_FROM" => "ASC","SORT" => "DESC"], $filter, false, ($limit > 0 ?  ["nTopCount" => $limit] : false), ['ID', 'SORT', 'NAME']);

        $resIds = [];

        while ($el = $res->Fetch()) {
            $resIds[] = [
                'ID' => $el['ID'],
                'NAME' => $el['NAME'],
            ];
        }

        return $resIds;
    }

    public static function getByXmlCode(string $xml_id, $iblockId = false): array
    {
        $filter = [
            'XML_ID' => $xml_id,

        ];

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = \CIBlockElement::GetList([], $filter, false, false, ['ID']);

        return $res->fetch();
    }

    public static function getXmlCodeById(int $id, $iblockId = false): string
    {
        $filter = [
            'ID' => $id,
        ];

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = \CIBlockElement::GetList([], $filter, false, false, ['XML_ID']);

        if ($item = $res->fetch()) {
            return $item['XML_ID'];
        }

        return $id;
    }
}
