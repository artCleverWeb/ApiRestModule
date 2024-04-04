<?php

/** @const HL_TABLE_NAME_LOGS */

namespace KolosStudio\Helpers;

class Logger
{

    private int $id = 0;
    public string $method = '';
    public string $status = '';
    public string $comment = '';
    private HighloadBlock $loggerEntity;

    function __construct()
    {
        if (defined('HL_TABLE_NAME_LOGS')) {
            $this->loggerEntity = new HighloadBlock(HL_TABLE_NAME_LOGS);
        }
        $this->clearOldLogs();
    }

    private function save(): void
    {
        $fields = [
            'UF_METHOD' => $this->method,
            'UF_STATUS' => $this->status,
            'UF_COMMENT' => $this->comment,
        ];

        if ($this->id == 0) {
            $this->id = $this->loggerEntity->add($fields);
        } else {
            $this->loggerEntity->update($this->id, $fields);
        }
    }

    private function clearOldLogs(): void
    {
        $oldList = $this->loggerEntity->find([
            'filter' => [
                '<UF_DATE_ADD' => ConvertTimeStamp(false, "FULL"),
            ],
            'select' => [
                'ID',
            ]
        ]);

        foreach ($oldList as $item) {
            $this->loggerEntity->delete($item['ID']);
        }
    }
}
