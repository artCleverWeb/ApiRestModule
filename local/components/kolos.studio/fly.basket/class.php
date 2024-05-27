<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule('kolos.studio');

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\UrlManager;
use Kolos\Studio\Sale\BasketService;

class FlyBasket extends \CBitrixComponent implements Controllerable
{

    public function configureActions()
    {
        return [
            'getBasket' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ],
            'updateItem' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
            ]
        ];
    }

    protected function getResult()
    {
        return [];
    }

    public function getBasketAction()
    {
        return AjaxJson::createSuccess(
            $this->prepareBasket()
        );
    }

    public function updateItemAction(array $fields)
    {
        return AjaxJson::createSuccess(
                self::prepareBasket(
                    BasketService::getInstance()->updateBasket($fields['id'], $fields['quantity'])
                )
        );
    }

    public function prepareBasket(): array
    {
        return [
            'basket' => BasketService::getInstance()->getFormattedBasket(),
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
