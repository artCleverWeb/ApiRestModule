<?php

namespace Kolos\Studio\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class Base
{
    const AVAILABLE_FILE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'bmp',
        'zip',
        'rar',
        'pdf',
        'rtf',
        'doc',
        'docs',
        'xls',
        'xlsx',
        'txt',
    ];

    public static function formattedPhone($phone)
    {
        if (empty($phone))
            return '';

        return preg_replace('/\D/', '', $phone);
    }

    protected function checkModules(array $modules)
    {
        foreach ($modules as $module) {
            if (!Loader::IncludeModule($module)) {
                throw new SystemException("module {$module} is not installed");
            }
        }
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = []; //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass); //turn the array into a string
    }

    public static function getIpAddress()
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?: ($_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['REMOTE_ADDR']);
    }

    public static function getAvailableFileExtensionsForForms($format = false)
    {
        return $format ? '.' . implode(',.', self::AVAILABLE_FILE_EXTENSIONS) : self::AVAILABLE_FILE_EXTENSIONS;
    }

    public static function makeWebp($src, $rewrite = false)
    {
        if (!self::isBot() && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') === false)
            return $src;

        if (DISABLE_WEBP === true)
            return $src;

        if (mb_strpos($src, ASSET_URL) !== false) {
            $newImgPath = str_replace(['.jpg', '.jpeg', '.gif', '.png'], '.webp', $src);

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $newImgPath)) {
                return $newImgPath;
            }

            return $src;
        }

        if ($src && function_exists('imagewebp')) {
            $newImgPath = str_replace(['.jpg', '.jpeg', '.gif', '.png'], '.webp', $src);

            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $newImgPath) || $rewrite) {
                $info = getimagesize($_SERVER['DOCUMENT_ROOT'] . $src);

                if ($info !== false && ($type = $info[2])) {
                    switch ($type) {
                        case IMAGETYPE_JPEG:
                            $newImg = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $src);
                            break;
                        case IMAGETYPE_GIF:
                            $newImg = imagecreatefromgif($_SERVER['DOCUMENT_ROOT'] . $src);
                            break;
                        case IMAGETYPE_PNG:
                            $newImg = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $src);
                            imagepalettetotruecolor($newImg);
                            imagealphablending($newImg, true);
                            imagesavealpha($newImg, true);
                            break;
                    }

                    if ($newImg) {
                        imagewebp($newImg, $_SERVER['DOCUMENT_ROOT'] . $newImgPath, 80);
                        imagedestroy($newImg);
                    }
                }
            }

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $newImgPath)) {
                return $newImgPath;
            }
        }

        return $src;
    }

    public static function isBot(&$botname = '')
    {
        $bots = [
            'rambler', 'googlebot', 'aport', 'yahoo', 'msnbot', 'turtle', 'mail.ru', 'omsktele',
            'yetibot', 'picsearch', 'sape.bot', 'sape_context', 'gigabot', 'snapbot', 'alexa.com',
            'megadownload.net', 'askpeter.info', 'igde.ru', 'ask.com', 'qwartabot', 'yanga.co.uk',
            'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot', 'followsite.com',
            'dataparksearch', 'google-sitemaps', 'appEngine-google', 'feedfetcher-google',
            'liveinternet.ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com', 'h1.hrn.ru',
            'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'yandex',
            'yandexSomething', 'Copyscape.com', 'AdsBot-Google', 'domaintools.com',
            'Nigma.ru', 'bing.com', 'dotnetdotcom', 'Chrome-Lighthouse'
        ];

        foreach ($bots as $bot) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                $botname = $bot;
                return true;
            }
        }

        return false;
    }

    public static function getIblockPropertyCode($id = [], $iblockId = '')
    {
        if (empty($iblockId)) return false;

        $filter = [
            'IBLOCK_ID' => $iblockId,
        ];

        if (!empty($id)) $filter['ID'] = $id;

        $propertyList = \CIBlockProperty::GetList(
            [],
            $filter
        );

        while ($property = $propertyList->Fetch()) {
            $res[$property['CODE']] = [
                'ID' => $property['ID'],
                'CODE' => $property['CODE'],
                'NAME' => $property['NAME'],
            ];
        }

        return !empty($res) ? $res : false;
    }
}