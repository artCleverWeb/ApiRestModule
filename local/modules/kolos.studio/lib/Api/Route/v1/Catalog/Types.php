<?php

namespace Kolos\Studio\Api\Route\v1\Catalog;

require_once __DIR__ . '/../BaseDirectoryRoute.php';

use Kolos\Studio\Api\Route\v1\BaseRoute;

class Types extends BaseRoute
{
    use \BaseDirectoryRoute;

    private $fillKeys = [
        'code' => 'UF_CODE',
        'name' => 'UF_NAME',
    ];

    protected function getTableName(): string
    {
        if (!defined('HL_TABLE_DIRECTORY_TYPES') || strlen(HL_TABLE_DIRECTORY_TYPES) == 0) {
            throw new \ErrorException ('Parameters HL_TABLE_NAME not defined');
            return '';
        }

        return HL_TABLE_DIRECTORY_TYPES;
    }

    public function childProcess()
    {
        return $this->childProcessTrait();
    }

    protected function validate(): bool
    {
        return $this->validateTrait();
    }

    protected function checkPermission(): bool
    {
        return $this->checkPermissionTrait();
    }
}
