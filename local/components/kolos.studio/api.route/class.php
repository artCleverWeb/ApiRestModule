<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;

class ApiRoute extends \CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [];
    }

    public function executeComponent()
    {
        try {
            \Bitrix\Main\Loader::includeModule('kolos.studio');
            $mainApiClass = \Kolos\Studio\Api\MainApi::getInstance();
            $mainApiClass->route($_REQUEST['method'] ?? '');
            $mainApiClass->showResult();
        } catch (Exception $e) {
            $mainApiClass->setError(500, $e->getMessage());
            $mainApiClass->showResult();
        }
    }
}
