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

    private function setRequest(): void
    {
        $this->arRequest = $this->parent->arRequest;
        $this->requestMethod = $this->parent->methodRequest;
    }

    private function validateCodeValue($value): bool
    {
        preg_match('/^([a-zA-Z0-9])+$/m', $value, $matches, PREG_OFFSET_CAPTURE, 0);
        return count($matches) > 0;
    }
}