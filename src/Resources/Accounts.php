<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Accounts extends SawfishIntegration
{

    /**
     * Get accounts from Sawfish API
     * /accounts?uuids={{ string_split_by_commas }}&type={{ type }}
     * @param string|null $uuids Account uuids filter
     * @param string|null $type Account type filter
     * @return array
     */
    public function getAccounts($uuids = null, $type = null)
    {
        if ($uuids !== null && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/accounts?' . http_build_query([
            'uuids' => $data,
            'type' => $type,
        ]));

        return $this->getResponseData($response);
    }
}
