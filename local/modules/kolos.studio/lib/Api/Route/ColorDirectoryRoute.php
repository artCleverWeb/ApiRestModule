<?php

namespace Kolos\Studio\Api\Route;

class ColorDirectoryRoute extends BaseRoute implements BaseDirectoryRoute
{
    public function childProcess()
    {
        die('123');
    }

    protected function validate(): bool
    {

    }
}