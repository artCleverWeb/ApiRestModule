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
}