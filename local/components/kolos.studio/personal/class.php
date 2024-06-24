<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule('kolos.studio');

class Personal extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    public function configureActions()
    {
        return [];
    }

    protected function getResult()
    {
        return [
            'user' => Kolos\Studio\Helpers\Users::getUserInfo(),
            'orders' => Kolos\Studio\Sale\OrderService::getUserOrder(),
            'statuses' => Kolos\Studio\Sale\OrderService::getStatuses(),
        ];
    }


    public function executeComponent()
    {
        try {
            parent::executeComponent();

            $this->arResult = $this->getResult();
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }
}