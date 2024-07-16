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
        'countryCode' => 'Country',
        'specialOffer' => 'SpecialOffer',
        'new' => 'New',
        'quantityInPack' => 'QuantityInPack',
        'lengthCode' => 'LengthCode',
        'weightCode' => 'WeightCode',
        'typeCode' => 'TypeCode',
    ];

    private $preModerate = [
        'specialOffer' => 'convertYesNo',
        'new' => 'convertYesNo',
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
                        } else {
                            $this->isWarning = true;
                            $this->WarninText .= "У записи с ключем $key и кодом = $goodCode не является картинкой {$item['image']}" . PHP_EOL;
                        }
                    } else {
                        $productClass->setDetailPicture('');
                        $productClass->setPreviewPicture();
                    }

                    foreach ($item as $keyProp => $value) {
                        if (isset($this->fields[$keyProp]) && !empty($this->fields[$keyProp])) {
                            if (!isset($value) || empty($value) || $value == 'null') {
                                $value = '';
                            }
                            $fieldName = $this->fields[$keyProp];
                            $methodGet = 'get' . $fieldName;
                            $methodSet = 'set' . $fieldName;

                            if (isset($this->preModerate[$keyProp])) {
                                $fnName = $this->preModerate[$keyProp];
                                $value = $this->$fnName($value);
                            }

                            if ($productClass->$methodGet()) {
                                $productClass->$methodGet()->setValue($value);
                            } else {
                                $productClass->$methodSet($value);
                            }
                        }
                    }

                    $isNew = $productClass->getId() == 0;
                    $result = $productClass->save();

                    if (!$result->isSuccess()) {
                        print_r($result->getErrorMessages());
                        die();
                        throw new SystemException(implode(', ', $result->getErrorMessages()));
                    }

                    if ($isNew) {
                        \Bitrix\Catalog\ProductTable::add([
                            "ID" => $result->getId(),
                            "VAT_ID" => 1,
                            "VAT_INCLUDED" => "Y",
                            "TYPE " => \Bitrix\Catalog\ProductTable::TYPE_PRODUCT,
                        ]);
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

    protected function convertYesNo($value): string
    {
        return (int)$value == 1 ? 'Y' : 'N';
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
        return parent::validateNameValue($value);
    }

    protected function validateItem(array $item, $key): bool
    {
        if (isset($item['length']) && intval($item['length']) < 0) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value length" . PHP_EOL;
            return false;
        }

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

        if (isset($item['specialOffer']) && $this->validateYesNot($item['specialOffer']) === false) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value specialOffer" . PHP_EOL;
            return false;
        }

        if (isset($item['new']) && $this->validateYesNot($item['new']) === false) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value new" . PHP_EOL;
            return false;
        }

        if (isset($item['quantityInPack']) && $this->validateInteger($item['quantityInPack']) === false) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value quantityInPack" . PHP_EOL;
            return false;
        }

        if (isset($item['lengthCode']) && $this->validateCodeValue($item['lengthCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value lengthCode" . PHP_EOL;
            return false;
        }

        if (isset($item['weightCode']) && $this->validateCodeValue($item['weightCode']) !== true) {
            $this->isWarning = true;
            $this->WarninText .= "The $key element contains an empty value weightCode" . PHP_EOL;
            return false;
        }

        return true;
    }

    protected function validateInteger($val): bool
    {
        return preg_match(
                '/^\d+$/m',
                $val,
                $matches,
                PREG_OFFSET_CAPTURE
            ) == 1;
    }

    protected function validateYesNot($val): bool
    {
        return preg_match(
                '/^[0|1]$/m',
                $val,
                $matches,
                PREG_OFFSET_CAPTURE
            ) == 1;
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
