<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class kolos_studio extends CModule
{
    public const MODULE_ID = "kolos.studio";
    public $MODULE_ID = "kolos.studio";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("kolos.studio_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("kolosv.seotemplate_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("kolos.studio_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("kolos.studio_PARTNER_URI");
    }

    public function installEvents()
    {
        return true;
    }

    public function unInstallEvents()
    {
        return true;
    }

    public function doInstall()
    {
        ModuleManager::registerModule(self::MODULE_ID);
        $this->installEvents();
        return true;
    }

    public function doUninstall()
    {
        $this->unInstallEvents();
        ModuleManager::unRegisterModule(self::MODULE_ID);
        return true;
    }
}
