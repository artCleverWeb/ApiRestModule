<?php

namespace Kolos\Studio\Api\Route;

class TypePriceRoute extends BaseRoute
{
    public function process()
    {
        $this->logger->status = 'process';
        $this->logger->save();
        $this->data[] = ['test' => 'TEST'];
        $this->setResult();
    }

    protected function validate()
    {

    }
}
