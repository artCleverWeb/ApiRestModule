<?php

namespace Kolos\Studio\Helpers;

use \Bitrix\Main\UserTable;

class Users
{
    public static function getXmlCodeById(int $id): string
    {
        $userInfo = UserTable::getRow([
            'filter' => [
                'ID' => $id,
            ],
            'select' => [
                'XML_ID',
            ],
        ]);

        if ($userInfo) {
            return $userInfo['XML_ID'] ?? $id;
        }

        return $id;
    }
}
