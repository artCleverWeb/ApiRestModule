<?php

namespace KolosStudio\Integration\Prices;

use Bitrix\Catalog\GroupAccessTable;
use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

Loader::includeModule('catalog');

class TypePrices
{
    private $groupsShow = [1];
    private $groupsBuy = [1];
    private string $code;
    private string $name;
    private string $title;

    function __construct(string $code, string $name, string $title)
    {
        $this->code = $code;
        $this->name = $name;
        $this->title = $title;
    }

    /**
     * @throws SystemException
     */
    public function store(): int
    {
        $entityId = 0;

        $fields = $this->fill();

        $entityId = $this->find();

        if($entityId > 0){
            $result = GroupTable::add($fields);

            if (!$result->isSuccess()) {
                throw new SystemException(implode(', ', $result->getErrorMessages()));
            }

            $entityId = (int)$result->getId();

            $this->storeGroup($entityId);
        }
        else{
            $result = GroupTable::update($entityId, $fields);

            if (!$result->isSuccess()) {
                throw new SystemException(implode(', ', $result->getErrorMessages()));
            }
        }

        return $entityId;
    }

    private function find():int
    {
        $group = GroupTable::getList([
            'filter' => [
                "XML_ID" => $this->code,
            ],
            'select' => [
                "ID",
            ],
        ])->fetch(); 
        
        if($group){
            return $group['ID'];
        }
        
        return 0;
    }
    
    private function fill(): array
    {
        return [
            "NAME" => $this->name,
            "XML_ID" => $this->code,
            "LANG" => [
                "ru" => $this->title,
            ],
        ];
    }

    /**
     * @throws SystemException
     */
    private function storeGroup(int $id):void
    {
        $result = GroupAccessTable::add([
            "CATALOG_GROUP_ID" => $id,
            "GROUP_ID" => $this->groupsShow,
            "ACCESS" => $this->groupsBuy,
        ]);

        if (!$result->isSuccess()) {
            throw new SystemException(implode(', ', $result->getErrorMessages()));
        }
    }
}
