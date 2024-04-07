<?php

namespace Kolos\Studio\Api;

use Bitrix\Main\Application;

class MainApi
{
    static private $instance = null;
    protected $siteID = null;
    protected $arResult = [];
    protected $arErrors = [];
    protected $CodeStatus = 200;
    private $curDir = '/';
    public $methodRequest = '';
    public $arRequest = [];

    private $routeClass = null;
    private $methodClass = [
        'v1' => [
            'prices.types' => Route\TypePriceRoute::class,
            'prices.stocks' => Route\StockGoodRoute::class,
        ],
    ];

    private $methodCall = '';
    private $versionCall = '';

    function __construct($arParams = [])
    {
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

    public function route(string $method)
    {

        $this->methodRequest = $_SERVER['REQUEST_METHOD'];
        $this->setRequest();

        $isChecked = $this->checkMethod($method);

        if ($isChecked) {
            if(is_a($this->routeClass, '\Kolos\Studio\Api\Route\BaseRoute')) {
                $this->routeClass->process();
            }
            else
            {
                $this->setError(500, 'No route class found to process the request');
            }
        }

    }

    private function checkMethod(string $method): bool
    {
        if (strlen($method) < 0) {
            $this->setError(404, 'Page not found');
            return false;
        }

        $arrMethod = array_values(array_diff(explode("/", $method), ['', null]));

        if (!is_array($arrMethod) || count($arrMethod) < 2) {
            $this->setError(404, 'Page not found');
            return false;
        }

        $this->versionCall = $arrMethod[0];

        if (!isset($this->methodClass[$this->versionCall])) {
            $this->setError(403, 'Version not allowed');
            return false;
        }

        unset($arrMethod[0]);
        $this->methodCall = implode('.', $arrMethod);


        if (!isset($this->methodClass[$this->versionCall][$this->methodCall])) {
            $this->setError(403, 'Method not allowed');
            return false;
        }

        $this->routeClass = new $this->methodClass[$this->versionCall][$this->methodCall]($this, $this->methodCall);
        return true;
    }

    public function setError(int $code, string $message): void
    {
        $this->CodeStatus = $code;
        $this->arErrors[] = [
            $message,
        ];
    }

    public function setResult(array $arData = []): bool
    {
        if (is_array($arData)) {
            $this->arResult = array_merge_recursive($this->arResult, $arData);
        }

        return true;
    }

    public function showResult(): bool
    {
        global $APPLICATION;

        http_response_code($this->CodeStatus);
        Header("Content-Type: application/json; charset=utf-8", true);

        if (empty($this->arErrors)) {
            echo json_encode(($this->isUTF() ? $this->getLowerKeys($this->arResult) : $APPLICATION->ConvertCharsetArray($this->getLowerKeys($this->arResult), 'WINDOWS-1251', 'UTF-8')));
        } else {
            echo json_encode(
                [
                'answer' => 'Error',
                'message' => $this->isUTF() ? $this->getLowerKeys($this->arErrors[0]) : $APPLICATION->ConvertCharsetArray($this->getLowerKeys($this->arErrors[0]), 'WINDOWS-1251', 'UTF-8')
                ]
            );
        }

        return true;
    }

    public function isUTF()
    {
        return (defined('BX_UTF') && BX_UTF === true);
    }

    private function getLowerKeys($ar)
    {
        return $ar;
    }

    public function setRequest(): void
    {
        switch ($this->methodRequest) {
            case 'GET':
                $val = $_GET;
                break;
            case 'POST':
                if (!count($_POST)) {
                    if ($_SERVER["CONTENT_TYPE"] == 'application/json') {
                        $POST = json_decode(file_get_contents('php://input'), true);
                    } else {
                        parse_str(file_get_contents('php://input'), $POST);
                    }

                    if (!$POST) {
                        $POST = $_POST;
                    }
                }

                $val = $POST;
                break;
            case 'PUT':

                if ($_SERVER["CONTENT_TYPE"] == 'application/json') {
                    $_PUT = json_decode(file_get_contents('php://input'), true);
                } else {
                    parse_str(file_get_contents('php://input'), $_PUT);
                }
                $val = $_PUT;
                break;
        }

        $this->arRequest = $val;
    }
}
