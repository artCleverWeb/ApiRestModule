<?php

/** @const HL_TABLE_NAME_LOGS */

namespace Kolos\Studio\Helpers;

use Bitrix\Main\SystemException;

class Logger
{

    private int $id = 0;
    public string $method = '';
    public string $status = '';
    public string $comment = '';
    private const clearTimeAdd = 604800;
    public array $request = [];

    private HighloadBlock $loggerEntity;

    function __construct()
    {
        if (defined('HL_TABLE_NAME_LOGS')) {
            $this->loggerEntity = new HighloadBlock(HL_TABLE_NAME_LOGS);
        } else {
            throw new \ErrorException('Parameters HL_TABLE_NAME_LOGS not defined');
        }
        
        $this->clearOldLogs();
    }

    public function save(): void
    {
        $fields = [
            'UF_METHOD' => $this->method,
            'UF_STATUS' => $this->status,
            'UF_COMMENT' => $this->comment,
            'UF_IP' => Base::getIpAddress(),
            'UF_REQUEST' => json_encode($this->request),

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
                '<UF_DATE_ADD' => ConvertTimeStamp(time() - self::clearTimeAdd, "FULL"),
            ],
            'select' => [
                'ID',
            ],
        ]);

        foreach ($oldList as $item) {
            $this->loggerEntity->delete($item['ID']);
        }
    }
}
