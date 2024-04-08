<?php

namespace Kolos\Studio\Integration\Product;

use Kolos\Studio\Helpers\HighloadBlock;

class ProductDirectory
{
    private string $HL_TABLE_NAME = '';
    private HighloadBlock $storeEntity;

    function __construct(string $tableName)
    {
        $this->HL_TABLE_NAME = $tableName;
        $this->storeEntity = new HighloadBlock($tableName);
    }

    public function store(array $fields): int
    {

        if ($this->validate($fields) === false) {
            return 0;
        }

        $fields['UF_DATE_UPDATE'] = ConvertTimeStamp(time(), "FULL");

        $entityId = $this->findByCode($fields['UF_CODE']);

        if ($entityId == 0) {
            $entityId = $this->storeEntity->add($fields);
        } else {
            $this->storeEntity->update($entityId, $fields);
        }

        return $entityId;
    }

    private function findByCode(string $code): int
    {
        $item = $this->storeEntity->getRow([
            'filter' => [
                '=UF_CODE' => $code,
            ],
            'select' => [
                'ID',
            ],
        ]);

        if (is_array($item) && isset($item['ID']) && !empty($item['ID'])) {
            return $item['ID'];
        }

        return 0;
    }

    private function validate(array $fields): bool
    {
        $keysRequest = array_keys($fields);
        $keysNeed = ['UF_CODE', 'UF_NAME'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            return false;
        }

        return true;
    }
}
