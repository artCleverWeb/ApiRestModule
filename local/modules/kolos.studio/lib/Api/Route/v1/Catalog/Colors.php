<?php

namespace Kolos\Studio\Api\Route\v1\Catalog;

require_once __DIR__ . '/../BaseDirectoryRoute.php';

use Kolos\Studio\Api\Route\v1\BaseRoute;

class Colors extends BaseRoute
{
    use \BaseDirectoryRoute;

    private $fillKeys = [
        'code' => 'UF_CODE',
        'name' => 'UF_NAME',
        'color1' => 'UF_COLOR1',
        'color2' => 'UF_COLOR2',
    ];

    protected function getTableName(): string
    {
        if (!defined('HL_TABLE_DIRECTORY_COLOR') || strlen(HL_TABLE_DIRECTORY_COLOR) == 0) {
            throw new \ErrorException ('Parameters HL_TABLE_NAME not defined');
            return '';
        }

        return HL_TABLE_DIRECTORY_COLOR;
    }

    public function childProcess()
    {
        foreach ($this->arRequest as $key => $item) {

            if ($item['color1'] == 'null') {
                $item['color1'] = '';
            }
            if ($item['color2'] == 'null') {
                $item['color2'] = '';
            }

            if ($this->validateItem($item, $key) === true) {
                try {
                    $id = $this->store($item);
                    if ($id == 0) {
                        $this->isWarning = true;
                        $this->WarninText .= "Item {$item['code']} don't save" . PHP_EOL;
                    }
                } catch (SystemException $e) {
                    $this->isWarning = true;
                    $this->WarninText .= "Item $key: " . $e->getMessage();
                }
            }
        }

        $this->arResult = [
            'status' => true,
        ];

    }

    protected function validateItem(array $item, $key): bool
    {

        if (!isset($item['code']) || empty($item['code'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value code" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValueTrait($item['code']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the code value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['name']) || empty($item['name'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value name" . PHP_EOL;
            return false;
        }

        if ($this->validateNameValueTrait($item['name']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the name value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!empty($item['color1']) && $this->validateColorValue($item['color1']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the color1 value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!empty($item['color2']) && $this->validateColorValue($item['color2']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the color1 value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        return true;
    }

    private function validateColorValue($value): bool
    {
        preg_match('/^^#([a-zA-Z0-9]){2,15}$/m', $value, $matches, PREG_OFFSET_CAPTURE, 0);
        return count($matches) > 0 && strlen($value) <= 25;
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
