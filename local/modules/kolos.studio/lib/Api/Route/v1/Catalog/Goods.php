<?php

namespace Kolos\Studio\Api\Route\v1\Catalog;

use Bitrix\Main\SystemException;
use Kolos\Studio\Api\Route\v1\BaseRoute;

class Goods extends BaseRoute
{
    private $productClass;
    private $fields = [
        'varietieCode' => 'Varientie',
        'plantationCode' => 'Plantation',
        'colorCode' => 'Color',
        'length' => 'Length',
    ];

    public function childProcess()
    {
        $class = new \Kolos\Studio\Integration\Product\Good();

        $class->getProductEntity();
        foreach ($this->arRequest as $key => $item) {
            if ($this->validateItem($item, $key) === true) {
                try {
                    $productClass = $class->findOrCreate($item['code']);

                    $productClass->setName($item['name']);
                    $productClass->setXmlId($item['code']);
                    $goodCode = $item['code'];

                    if (!$productClass->getCode()) {
                        $code = \Cutil::translit($item['name'], "ru", [
                            "replace_space" => "-",
                            "replace_other" => "-",
                        ]);

                        $productClass->setCode($code);
                    }

                    if (!empty($item['image'])) {
                        $arFile = \CFile::MakeFileArray($item['image']);
                        if (is_null(\CFile::CheckImageFile($arFile))) {
                            $fileId = \CFile::SaveFile($arFile, "iblock");
                            if ($fileId > 0) {
                                $productClass->setDetailPicture($fileId);
                                $productClass->setPreviewPicture($fileId);
                            } else {
                                $this->isWarning = true;
                                $this->WarninText .= "У записи с ключем $key и кодом = $goodCode картинка {$item['image']} не может быть сохранена" . PHP_EOL;
                            }
                        }
                        else{
                            $this->isWarning = true;
                            $this->WarninText .= "У записи с ключем $key и кодом = $goodCode не является картинкой {$item['image']}" . PHP_EOL;
                        }
                    } else {
                        $productClass->setDetailPicture('');
                        $productClass->setPreviewPicture();
                    }

                    foreach ($item as $key => $value) {
                        if (isset($this->fields[$key]) && !empty($this->fields[$key])) {
                            if (!isset($value) || empty($value) || $value == 'null') {
                                $value = '';
                            }
                            $fieldName = $this->fields[$key];
                            $methodGet = 'get' . $fieldName;
                            $methodSet = 'set' . $fieldName;

                            if ($productClass->$methodGet()) {
                                $productClass->$methodGet()->setValue($value);
                            } else {
                                $productClass->$methodSet($value);
                            }
                        }
                    }

                    $result = $productClass->save();

                    if (!$result->isSuccess()) {
                        throw new SystemException(implode(', ', $result->getErrorMessages()));
                    }

                    unset($productClass);
                } catch (SystemException $e) {
                    $this->isWarning = true;
                    $this->WarninText .= "Элемент $key: " . $e->getMessage();
                }
            }
        }

        $this->arResult = [
            'status' => true,
        ];
    }

    protected function validate(): bool
    {
        if ($this->requestMethod !== 'POST') {
            $this->setError(400, 'Method ' . $this->requestMethod . ' not allowed!');
            return false;
        }

        if (empty($this->arRequest) || !is_array($this->arRequest)) {
            $this->setError(400, 'Request is empty!');
            return false;
        }

        $firstLine = current($this->arRequest);

        $keysRequest = array_keys($firstLine);
        $keysNeed = ['code', 'name'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            $this->setError(400, 'The request structure is not valid!');
            return false;
        }

        return true;
    }

    protected function validateNameValue($value): bool
    {
        return preg_match('/^([а-яА-Яa-zA-Z0-9Ёё &-])+$/u', $value, $matches, PREG_OFFSET_CAPTURE) == 1;
    }

    protected function validateItem(array $item, $key): bool
    {
        if (!isset($item['code']) || empty($item['code'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value code" . PHP_EOL;
            return false;
        }

        if ($this->validateCodeValue($item['code']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the code value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!isset($item['name']) || empty($item['name'])) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value name" . PHP_EOL;
            return false;
        }

        if ($this->validateNameValue($item['name']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the name value does not satisfy the mask" . PHP_EOL;
            return false;
        }

        if (!empty($item['image']) && $this->validatePictureUrl($item['image']) === false) {
            $this->isWarning = true;
            $this->WarninText .= "In the $key element, the picture value does not image the mask" . PHP_EOL;
            return false;
        }

        return true;
    }

    protected function validatePictureUrl(string $url): bool
    {
        return preg_match(
                '/^http(s)?:\/\/([0-9a-zA-zа-яА-я\.-]+)\.([a-zA-zа-яА-я]+)\/(.)+\.(jp(e)?g|png|svg|webp)$/m',
                $url,
                $matches,
                PREG_OFFSET_CAPTURE
            ) == 1;
    }

    protected function checkPermission(): bool
    {
        return true;
    }

}
