<?php

class CCatalogProductProviderCustom extends CCatalogProductProvider
{
    public static function GetProductData($arParams)
    {
        $productID = $arParams['PRODUCT_ID'];

        $arResult = parent::GetProductData($arParams);

        if ($price = getUserPrice()) {
            $priceTypeId = $price['ID'];

            if ($priceTypeId) {
                $db_res = CPrice::GetList(
                    [],
                    [
                        "PRODUCT_ID" => $arParams['PRODUCT_ID'],
                        "CATALOG_GROUP_ID" => $priceTypeId,
                    ]
                );

                $ar_res__ = $db_res->Fetch();
                $ar_res_ = [
                    [
                        'ID' => $ar_res__['ID'],
                        'PRICE' => $ar_res__['PRICE'],
                        'CURRENCY' => $ar_res__['CURRENCY'],
                        'CATALOG_GROUP_ID' => $ar_res__['CATALOG_GROUP_ID'],
                    ]
                ];

                $arPrice = CCatalogProduct::GetOptimalPrice(
                    $ar_res__['PRODUCT_ID'],
                    1,
                    $priceTypeId,
                    'N',
                    $ar_res_,
                    Bitrix\Main\Context::getCurrent()->getSite(),
                    $_SESSION['CATALOG_USER_COUPONS']
                );

                print_r($arPrice);

                if ($arPrice) {
                    $arResult['PRICE_TYPE_ID'] = $arPrice['RESULT_PRICE']['PRICE_TYPE_ID'];
                    $arResult['BASE_PRICE'] = $arPrice['RESULT_PRICE']['BASE_PRICE'];
                    $arResult['DISCOUNT_PRICE'] = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
                    $arResult['PRICE'] = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
                }
            }
        }
        return $arResult;
    }
}
