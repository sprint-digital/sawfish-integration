<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Clients extends SawfishIntegration
{
    /**
     * Get clients from Sawfish API
     *
     * @return array
     */
    public function getClients($perPage = 200, $page = 1)
    {
        $response = $this->withTokenHeaders()->get('/clients?' . http_build_query([
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Get suppliers from Sawfish API
     *
     * @return array
     */
    public function getSuppliers($perPage = 200, $page = 1)
    {
        $response = $this->withTokenHeaders()->get('/clients?' . http_build_query([
            'is_supplier' => true,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    public function getClientsByProviderUuids($uuids = null, $perPage = 200, $page = 1)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/clients?' . http_build_query([
            'accounting_provider_ids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     */
    public function getClientByUuids($uuids)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/clients?' . http_build_query([
            'uuids' => $data,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Verify if a client with matching name, abn, or bsb + account number exists.
     * Method: GET.
     */
    public function verifyClient(array $data)
    {
        $response = $this->withTokenHeaders()->get('/clients/verify?' . http_build_query($data));

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function createClient(array $data)
    {
        $response = $this->withTokenHeaders()->post('/clients', $data);
        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateClient(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/clients/' . $uuid, $data);
        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function addContactPersons($uuid, array $data)
    {
        $response = $this->withTokenHeaders()->post('/clients/' . $uuid . '/contact-persons', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateContactPersons($uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/clients/' . $uuid . '/contact-persons', $data);

        return $this->getResponseData($response);
    }
}
