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
     * Method: POST.
     */
    public function createClient(array $data)
    {
        $response = $this->withTokenHeaders()->post('/clients', $data);

        if (!$response->ok()) {
            $message = $response->json('message') ?? $response->reason();
            return $message;
        }
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
