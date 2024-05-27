<?php

use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Kolos\Studio\Helpers\HighloadBlock;


if (!function_exists('pre')) {
    function pre($print, $dump = false)
    {
        $style = !is_admin() ? 'display:none;' : '';

        if (!empty($print)) {
            echo '<pre style="' . $style . '">';

            if ($dump) {
                var_dump($print);
            } else {
                print_r($print);
            }

            echo '</pre>';
        }
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap($value)
    {
        return is_array($value) ? $value : [$value];
    }
}

if (!function_exists('user')) {
    function user()
    {
        global $USER;
        return $USER;
    }
}

if (!function_exists('user_id')) {
    function user_id()
    {
        return user()->GetID();
    }
}

if (!function_exists('is_authorized')) {
    function is_authorized(): bool
    {
        return user()->IsAuthorized();
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return user()->IsAdmin();
    }
}

if (!function_exists('get_ip')) {
    function get_ip(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (strpos($ip, ',')) {
            $ip = explode(",", $ip)[0];
        }

        return $ip;
    }
}

if (!function_exists('application')) {
    function application()
    {
        global $APPLICATION;
        return $APPLICATION;
    }
}

if (!function_exists('assets')) {
    function assets()
    {
        return Asset::getInstance();
    }
}

if (!function_exists('request')) {
    function request()
    {
        return Application::getInstance()->getContext()->getRequest();
    }
}

if (!function_exists('event_log')) {
    function event_log($code, $value, $severity = 'DEBUG', $module = 'none', $id = 0)
    {
        \CEventLog::Log($severity, $code, $module, $id, !is_string($value) ? print_r($value, true) : $value);
    }
}

if (!function_exists('event_log_by_ip')) {
    function event_log_by_ip($code, $value, $ip = '127.0.0.1', $severity = 'DEBUG', $module = 'none', $id = 0)
    {
        if (get_ip() == $ip) {
            event_log($code, $value, $severity, $module, $id);
        }
    }
}

if (!function_exists('event_log_by_user')) {
    function event_log_by_user($code, $value, $user_id, $severity = 'DEBUG', $module = 'none', $id = 0)
    {
        if (user_id() == $user_id) {
            event_log($code, $value, $severity, $module, $id);
        }
    }
}

if (!function_exists('event_log_admin')) {
    function event_log_admin($code, $value, $severity = 'DEBUG', $module = 'none', $id = 0)
    {
        if (is_admin()) {
            event_log($code, $value, $severity = 'DEBUG', $module = 'none', $id = 0);
        }
    }
}

if (!function_exists('get_highload_block_entity')) {
    function get_highload_block_entity($filter)
    {
        include_modules('highloadblock');

        $hlBlock = HighloadBlockTable::getList(['filter' => $filter])->fetch();
        $entity = HighloadBlockTable::compileEntity($hlBlock);

        return $entity->getDataClass();
    }
}

if (!function_exists('get_highload_block_entity_by_name')) {
    function get_highload_block_entity_by_name($hlBlockName)
    {
        return get_highload_block_entity(['NAME' => $hlBlockName]);
    }
}

if (!function_exists('get_highload_block_entity_by_id')) {
    function get_highload_block_entity_by_id($hlBlockId)
    {
        return get_highload_block_entity(['ID' => $hlBlockId]);
    }
}

if (!function_exists('get_section_id')) {
    function get_section_id($code, $iBlock = false)
    {
        include_modules('iblock');

        $filter = ['=CODE' => $code];

        if ($iBlock) {
            $filter['=IBLOCK_ID'] = $iBlock;
        }

        $section = SectionTable::getRow([
            'filter' => $filter,
            'select' => ['ID'],
        ]);

        return !empty($section) ? $section['ID'] : 0;
    }
}

if (!function_exists('get_iblock_id')) {
    function get_iblock_id($code)
    {
        $iblocksId = session_get('iblock_id');

        if ($iblocksId && array_key_exists($code, $iblocksId) && !empty($iblocksId[$code])) {
            return $iblocksId[$code];
        }

        include_modules('iblock');

        $iBlock = IblockTable::getRow([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
        ]);

        $iblockId = $iBlock['ID'];

        $iblocksId[$code] = $iblockId;
        session_set('iblock_id', $iblocksId);

        return $iblockId;
    }
}

if (!function_exists('session_get')) {
    function session_get($code)
    {
        return $_SESSION[SESSION_DATA_CONTAINER][$code];
    }
}

if (!function_exists('session_set')) {
    function session_set($code, $value)
    {
        if (!array_key_exists(SESSION_DATA_CONTAINER, $_SESSION)) {
            $_SESSION[SESSION_DATA_CONTAINER] = [];
        }

        $_SESSION[SESSION_DATA_CONTAINER][$code] = $value;
    }
}

if (!function_exists('array_get')) {
    /**
     * Получает элемент массива по ключу.
     * Имеется возможность использовать dot-нотацию (т.е. по ключу `foo.bar` из массива `['foo' => ['bar' => 100]]` достанется 100)
     *
     * @param array $array
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function array_get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $key = explode('.', $key);
        $innerKey = array_shift($key);

        if (array_key_exists($innerKey, $array)) {
            $array = $array[$innerKey];
        } else {
            return $default;
        }

        return array_get($array, implode('.', $key));
    }
}

if (!function_exists('array_key_to_lower')) {
    function array_key_to_lower($array)
    {
        $result = [];

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $val = array_key_to_lower($val);
            }

            $result[strtolower($key)] = $val;
        }

        return $result;
    }
}

if (!function_exists('is_absolute_link')) {
    function is_absolute_link($url = '')
    {
        return (bool)preg_match('/https?:\/\//', $url, $matches);
    }
}

if (!function_exists('get_absolute_link')) {
    function get_absolute_link($url = '', $subDomain = '')
    {
        if (!is_absolute_link($url)) {
            $url = sprintf('%s://%s%s', 'https', $_SERVER['SERVER_NAME'] ?: 'shop.severnaya.ru', $url);
        }

        if ($subDomain) {
            $url = str_replace('www', $subDomain, $url);
        }

        return $url;
    }
}

if (!function_exists('only_digit')) {
    function only_digit($value)
    {
        return preg_replace('/[^\d\.\,]/', '', $value);
    }
}

if (!function_exists('yes')) {
    function yes($value)
    {
        return in_array(mb_strtolower($value), ['yes', 'y', 'да', '1', 'true'], false);
    }
}

if (!function_exists('no')) {
    function no($value)
    {
        return in_array(mb_strtolower($value), ['no', 'n', 'нет', '0', 'false'], false);
    }
}

if (!function_exists('get_setting_bool')) {
    function get_setting_bool($parameter, $default = false)
    {
        return yes(get_setting($parameter, $default));
    }
}

if (!function_exists('include_file_simple')) {
    function include_file_simple($file, $params, $functionParams, $includeSiteTemplate = false)
    {
        application()->IncludeFile(($includeSiteTemplate ? SITE_TEMPLATE_PATH : '') . $file, $params, $functionParams);
    }
}

if (!function_exists('include_file')) {
    function include_file($file, $includeSiteTemplate = false, $parameters = [])
    {
        include_file_simple($file, $parameters, ['SHOW_BORDER' => false], $includeSiteTemplate);
    }
}

if (!function_exists('include_edit_file_html')) {
    function include_edit_file_html($file, $parameters = [], $includeSiteTemplate = false)
    {
        include_file_simple($file, $parameters, ['SHOW_BORDER' => true, 'MODE' => 'html'], $includeSiteTemplate);
    }
}

if (!function_exists('include_edit_file_text')) {
    function include_edit_file_text($file, $includeSiteTemplate = false)
    {
        include_file_simple($file, [], ['SHOW_BORDER' => true, 'MODE' => 'text'], $includeSiteTemplate);
    }
}

if (!function_exists('include_edit_file_php')) {
    function include_edit_file_php($file, $parameters = [], $includeSiteTemplate = false)
    {
        include_file_simple($file, $parameters, ['SHOW_BORDER' => true, 'MODE' => 'php'], $includeSiteTemplate);
    }
}

if (!function_exists('price_format')) {
    function price_format($value, $decimals = 2)
    {
        return number_format(round(floatval($value), 2), $decimals, ',', '') . " ₽";
    }
}

if (!function_exists('box_format')) {
    function box_format($value)
    {
        $isKg = $value >= 1000;

        return ($isKg ? price_format($value / 1000, 2) : (intval($value) == $value ? price_format(
                intval($value),
                0
            ) : price_format(floatval($value), 2))) . ($isKg ? ' кг' : ' г');
    }
}


if (!function_exists('get_initials')) {
    function get_initials($userId = false)
    {
        if ($userId) {
            $user = CUser::GetByID($userId)->Fetch();

            return mb_strtoupper(
                mb_substr($user['NAME'], 0, 1) . mb_substr($user['LAST_NAME'], 0, 1)
            );
        } else {
            return mb_strtoupper(mb_substr(user()->GetFirstName(), 0, 1) . mb_substr(user()->GetLastName(), 0, 1));
        }
    }
}

if (!function_exists('get_user_fi')) {
    function get_user_fi($userId = false)
    {
        $fio = '';

        if ($userId) {
            $user = CUser::GetByID($userId)->Fetch();
            $fio = $user['NAME'] . ' ' . $user['LAST_NAME'];
        }

        return $fio;
    }
}

if (!function_exists('get_russian_month_name_rod')) {
    function get_russian_month_name_rod($month)
    {
        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря',
        ];

        return $months[$month];
    }
}

if (!function_exists('get_short_week_day_name')) {
    function get_short_week_day_name($weekDay)
    {
        $weekDays = [
            0 => 'ВС',
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
            7 => 'ВС',
        ];

        return $weekDays[$weekDay];
    }
}

if (!function_exists('russian_date_format')) {
    function russian_date_format($time, $withYear = true)
    {
        $time = is_numeric($time) ? $time : strtotime($time);

        return $withYear ? sprintf(
            '%s %s %s',
            date('j', $time),
            get_russian_month_name_rod(date('n', $time)),
            date('Y', $time)
        ) : sprintf(
            '%s %s',
            date('j', $time),
            get_russian_month_name_rod(date('n', $time)),
        );
    }
}

/*
Окончания слов, в зависимости от числа
*/
if (!function_exists('plural_form')) {
    function plural_form($n, $forms)
    {
        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }
}

if (!function_exists('reformat_form_files')) {
    function reformat_form_files($filesArr)
    {
        $resArr = [];

        foreach ($filesArr as $key => $values) {
            if (!is_array($values)) {
                $resArr[] = $filesArr;
                break;
            }

            foreach ($values as $i => $val) {
                $resArr[$i][$key] = $val;
            }
        }

        return $resArr;
    }
}

if (!function_exists('get_date_parsed')) {
    function get_date_parsed($strDate)
    {
        return date_parse($strDate);
    }
}

if (!function_exists('get_next_week_date')) {
    function get_next_week_date($date, $settingValue = 6)
    {
        return date('Y-m-d', strtotime($date . ' + ' . $settingValue . ' days'));
    }
}

if (!function_exists('array_some')) {
    function array_some($array, $fn)
    {
        foreach ($array as $value) {
            if ($fn($value)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('array_unshift_assoc')) {
    function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr = array_reverse($arr, true);
        return count($arr);
    }
}

if (!function_exists('diverse_array')) {
    function diverse_array($vector): array
    {
        $result = [];
        foreach ($vector as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }
        }

        return $result;
    }
}

if (!function_exists('phone_mask')) {
    function phone_mask($phoneNumber)
    {
        return preg_replace('/^7(\d{3})(\d{3})(\d{2})(\d{2})$/', '7 ($1) $2-$3-$4', $phoneNumber);
    }
}

if (!function_exists('getColorProduct')) {
    function getColorProduct(string $value)
    {
        $color = [];

        $obCache = new CPHPCache;
        if ($obCache->InitCache(360000, $value, "/" . SITE_ID . "/product_color")) {
            $color = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            \Bitrix\Main\Loader::includeModule('kolos.studio');
            $entityClass = new HighloadBlock(HL_TABLE_DIRECTORY_COLOR);
            $color = $entityClass->getRow([
                'filter' => [
                    '=UF_XML_ID' => $value,
                ],
                'select' => [
                    'ID',
                    'UF_NAME',
                    'UF_COLOR1',
                    'UF_COLOR2',
                ],
            ]);

            $obCache->EndDataCache($color);
        }

        return $color;
    }
}

if(!function_exists('getUserPrice')) {
    function getUserPrice()
    {
        global $USER;

        Loader::includeModule('catalog');

        $price = [];
        $obCache = new CPHPCache;

        $identCache = $USER->IsAuthorized() ? $USER->GetID() : 'base';
        if ($obCache->InitCache(360000, $identCache , "/" . SITE_ID . "/getUserPrice")) {
            $price = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $price = [];

            if ($identCache != 'base') {
                $filter = [
                    'BASE' => 'Y',
                ];

                $userInfo = CUser::GetByID($identCache)->Fetch();
                if (isset($userInfo['UF_PRICE_TYPE_ID']) && $userInfo['UF_PRICE_TYPE_ID'] > 0) {
                    $filter = [
                        'ID' => $userInfo['UF_PRICE_TYPE_ID'],
                    ];
                }


                $price = GroupTable::getRow([
                    'filter' => $filter,
                    'select' => [
                        "ID",
                        'NAME',
                    ],
                ]);
            }
            $obCache->EndDataCache($price);
        }
        return $price;
    }
}
