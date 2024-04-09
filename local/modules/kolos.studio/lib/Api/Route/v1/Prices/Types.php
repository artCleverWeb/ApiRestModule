<?php

namespace Kolos\Studio\Api\Route\v1\Prices;

use Kolos\Studio\Api\Route\v1\BaseRoute;

class Types extends BaseRoute
{
    public function childProcess()
    {
        foreach ($this->arRequest as $item) {
            $class = new \Kolos\Studio\Integration\Prices\TypePrices(
                $item['code'],
                $item['name'],
                $item['title']['ru']
            );
            $class->store();
            unset($class);
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
        $keysNeed = ['code', 'name', 'title'];

        if (count(array_diff($keysNeed, $keysRequest)) > 0) {
            $this->setError(400, 'The request structure is not valid!');
            return false;
        }

        return true;
    }

    protected function checkPermission(): bool
    {
        return true;
    }
}
