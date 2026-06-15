<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Bills extends SawfishIntegration
{

    /**
     * Get bills from Sawfish API
     * /bills?uuids={{ string_split_by_commas }}
     * @return array
     */
    public function getBills($uuids = null, $perPage = 200, $page = 1)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/bills?' . http_build_query([
            'uuids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     */
    public function getBillByUuid(string $uuid)
    {
        $response = $this->withTokenHeaders()->get('/bills/' . $uuid);

        return $this->getResponseData($response);
    }

    public function getBillsByProviderUuid($uuids = null, $perPage = 200, $page = 1)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/bills?' . http_build_query([
            'accounting_provider_ids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function createBill(array $data)
    {
        $response = $this->withTokenHeaders()->post('/bills', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateBill(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/bills/' . $uuid, $data);

        return $this->getResponseData($response);
    }

    /**
     * Void a bill by updating it with status "Cancelled"
     * Method: PUT.
     */
    public function voidBill(string $uuid, array $data)
    {
        return $this->updateBill($uuid, array_merge($data, [
            'status' => 'Cancelled',
        ]));
    }
}
