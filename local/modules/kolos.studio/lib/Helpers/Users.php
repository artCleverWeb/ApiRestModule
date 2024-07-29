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

    public static function getIdByXmlCode(string $code): int
    {
        $userInfo = UserTable::getRow([
            'filter' => [
                'XML_ID' => $code,
            ],
            'select' => [
                'ID',
            ],
        ]);

        if ($userInfo) {
            return $userInfo['ID'] ?? 0;
        }

        return 0;
    }

    public static function getUserInfo() :array
    {
        global $USER;

        if($USER->IsAuthorized()){
            return [
                'fullName' => $USER->GetFirstName() . " " . $USER->GetSecondName() . " " . $USER->GetLastName(),
                'email' => $USER->GetEmail(),
                'isAuth' => true,
            ];
        }

        return [
            'isAuth' => false,
        ];
    }
}
