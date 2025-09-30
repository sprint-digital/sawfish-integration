<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Items extends SawfishIntegration
{

    /**
     * Get items from Sawfish API
     * /items?uuids={{ string_split_by_commas }}
     * @return array
     */
    public function getItems($perPage = 200, $page = 1, $uuids = null)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/items?' . http_build_query([
            'uuids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }
}
