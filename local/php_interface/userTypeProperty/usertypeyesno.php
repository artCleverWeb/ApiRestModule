<?php

IncludeModuleLangFile(__FILE__);

class CUserTypeYesNo
{
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'Checkbox',
            'DESCRIPTION' => 'Да/Нет (Флажок)',
            'GetAdminListViewHTML' => [__CLASS__, 'getTextVal'],
            'GetPublicViewHTML' => [__CLASS__, 'getTextVal'],
            'GetPropertyFieldHtml' => [__CLASS__, 'getPropertyFieldHtml'],
            'GetPropertyFieldHtmlMulty' => [__CLASS__, 'getPropertyFieldHtml'],
            'GetPublicFilterHTML' => [__CLASS__, 'getFilterHTML'],
            'GetAdminFilterHTML' => [__CLASS__, 'getFilterHTML'],
            'ConvertToDB' => [__CLASS__, 'convertToFromDB'],
            'ConvertFromDB' => [__CLASS__, 'convertToFromDB'],
            'GetSearchContent' => [__CLASS__, 'getSearchContent'],
        ];
    }

    public static function getTextVal($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'] == 'Y' ? 'Да' : 'Нет';
    }

    public static function getPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!array_key_exists('VALUE', $value) && $arProperty['MULTIPLE'] == 'Y') {
            $value = array_shift($value);
        }
        return '<input type="hidden" name="' . $strHTMLControlName['VALUE'] . '" value="N" /><input type="checkbox" name="' . $strHTMLControlName['VALUE'] . '" value="Y" ' . ($value['VALUE'] == 'Y' ? 'checked="checked"' : '') . '/>';
    }

    public static function getFilterHTML($arProperty, $strHTMLControlName)
    {
        $select = '<select name="' . $strHTMLControlName['VALUE'] . '">
			<option value="" >(любой)</option>
			<option value="Y" ' . ($_REQUEST[$strHTMLControlName['VALUE']] == 'Y' ? 'selected="selected"' : '') . '>Да</option>
			<option value="N" ' . ($_REQUEST[$strHTMLControlName['VALUE']] == 'N' ? 'selected="selected"' : '') . '>Нет</option>
		</select>';
        return $select;
    }

    public static function getSearchContent($arProperty, $value, $strHTMLControlName)
    {
        $propId = $arProperty;
        $propParams = CIBlockProperty::GetByID($propId)->Fetch();
        return $value['VALUE'] == 'Y' ? $propParams['NAME'] : '';
    }

    public static function convertToFromDB($arProperty, $value)
    {
        $value['VALUE'] = $value['VALUE'] == 'Y' || (int)$value['VALUE'] == 1 ? 'Y' : 'N';
        return $value;
    }

    function GetLength($arProperty, $value)
    {
        return 1;
    }
}
