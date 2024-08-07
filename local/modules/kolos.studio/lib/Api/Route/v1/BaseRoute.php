<?php

namespace Kolos\Studio\Api\Route\v1;

use Kolos\Studio\Api\Route\IBaseRoute;

abstract class BaseRoute implements IBaseRoute
{
    protected \Kolos\Studio\Api\MainApi $parent;
    protected $logger = null;
    protected string $method = '';
    protected $arRequest = [];
    protected $arResult = [];
    protected $requestMethod = '';
    protected $isWarning = false;
    protected $WarninText = '';

    abstract protected function childProcess();

    abstract protected function validate(): bool;

    abstract protected function checkPermission(): bool;

    function __construct(\Kolos\Studio\Api\MainApi $class, string $method)
    {
        $this->parent = $class;
        $this->method = $method;
        $this->getLogger();
        $this->setRequest();
    }

    public function process()
    {
        $this->startLogger();

        if ($this->checkPermission() === true) {
            if ($this->validate() === true) {
                $this->childProcess();

                $this->logger->request = $this->arRequest;

                if ($this->isWarning !== false) {
                    $this->logger->status = 'warning';
                    $this->logger->comment = $this->WarninText;
                    $this->logger->save();
                } else {
                    $this->logger->status = 'success';
                    $this->logger->save();
                }
                $this->setResult();
            }
        }
    }

    protected function startLogger()
    {
        $this->logger->status = 'process';
        $this->logger->request = $this->arRequest;
        $this->logger->save();
    }

    protected function setResult(): void
    {
        $this->parent->setResult($this->arResult);
    }

    protected function setError(int $code, string $message): void
    {
        $this->parent->setError($code, $message);
        $this->logger->status = 'fatalErrors';
        $this->logger->comment = $message;
        $this->logger->save();
    }

    protected function getLogger()
    {
        try {
            $this->logger = new \Kolos\Studio\Helpers\Logger;
            $this->logger->method = $this->method;
        } catch (Exception $e) {
            $this->setError(500, $e->getMessage());
        }

        return $this->logger;
    }

    protected function validateNameValue($value): bool
    {
        return preg_match('/^([а-яА-Яa-zA-Z0-9Ёё !&-.`’\/(),+"\']){2,100}$/u', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    protected function validateNameValueLength($value): bool
    {
        return mb_strlen($value) >= 2 && mb_strlen($value) <= 100;
    }

    private function setRequest(): void
    {
        $this->arRequest = $this->parent->arRequest;
        $this->requestMethod = $this->parent->methodRequest;
    }

    protected function validateCodeValue(string $value): bool
    {
        return preg_match('/^([a-fA-F0-9-]){35,37}$/m', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }
}