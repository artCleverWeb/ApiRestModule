<?php

namespace KolosStudio\Api;

use Bitrix\Main\Application;

class MainApi
{
    static private $instance = null;
    protected $siteID = null;
    protected $arResult = [];
    protected $arErrors = [];
    protected $CodeStatus = 200;
    private $req_srv_time = '';
    private $curDir = '/';

    function __construct($arParams = [])
    {
        $this->curDir = rtrim($_SERVER['DOCUMENT_ROOT'] . '/stock_1capi/', '/\\') . '/';
        $this->req_srv_time = date("d.m.Y H:i:s");
        $this->SeedTokenAuth();
    }

    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    public function getCurDir(): string
    {
        return $this->curDir;
    }

    public function setCurDir(string $path): void
    {
        $this->curDir = rtrim($path, '/\\') . '/';
    }

    public function getOption(string $name, string $default_value = null)
    {
        return $this->oOption->get($this->module_id, $name, $default_value, $this->getCurrentSiteId());
    }

    public function getCurrentSiteId(): string
    {
        if (defined('ADMIN_SECTION')) {
            if (!$this->siteID) {
                $host = Application::getInstance()->getContext()->getRequest()->getHttpHost();
                $host = preg_replace('/(:[\d]+)/', '', $host);

                $oSite = new \CSite();
                $dbr = $oSite->GetList($by = 'sort', $order = 'asc', [
                    'ACTIVE' => 'Y',
                    'DOMAIN' => $host,
                ]);

                if ($ar = $dbr->Fetch()) {
                    $this->siteID = $ar['LID'];
                } else {
                    $dbr = $oSite->GetList($by = 'sort', $order = 'asc', [
                        'DEFAULT' => 'Y',
                    ]);

                    if ($ar = $dbr->Fetch()) {
                        $this->siteID = $ar['LID'];
                    }
                }
            }
            return $this->siteID;
        }

        return SITE_ID;
    }
}
