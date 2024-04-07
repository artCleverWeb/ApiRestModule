<?php

namespace Kolos\Studio\Api\Route;

abstract class BaseRoute
{
    protected \Kolos\Studio\Api\MainApi $parent;
    protected $logger = null;
    protected string $method = '';
    protected $data = null;
    protected $arResult = [];

    abstract public function process();
    abstract protected function  validate();

    function __construct(\Kolos\Studio\Api\MainApi $class)
    {
        $this->parent = $class;
        $this->getLogger();
    }

    protected function setResult(): void
    {
        $this->parent->setResult($this->data);
    }
    
    protected function setError(int $code, string $message): void
    {
        $this->parent->setError($code, $message);
    }

    protected function getLogger()
    {
        try {
            $this->logger = new \Kolos\Studio\Helpers\Logger;
            $this->logger->method = $this->method;
        }
        catch(Exception $e) {
            $this->setError(500, $e->getMessage());
        }

        return $this->logger;
    }

}