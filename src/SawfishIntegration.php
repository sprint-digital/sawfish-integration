<?php

namespace SprintDigital\SawfishIntegration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration as ModelsSawfishIntegration;
use Exception;

class SawfishIntegration
{
    private $sawfishIntegration;
    private string $apiUrl;
    private string $clientId;
    private string $apiKey;

    public function __construct()
    {
        $this->sawfishIntegration = ModelsSawfishIntegration::first();
        $this->clientId = $this->sawfishIntegration->client_id ?? null;
        $this->apiKey = $this->sawfishIntegration->api_key ?? null;
        $this->apiUrl = config('sawfish-integration.api_url', '');
    }

    protected function withTokenHeaders()
    {
        $apiToken = $this->validateApiToken();

        return Http::baseUrl($this->apiUrl)
            ->acceptJson()
            ->withHeaders([
                'x-client-id'   => $this->clientId,
                'x-jwt-token'   => $apiToken,
            ]);
    }

    protected function withApiKeyHeaders($contentType = 'application/json')
    {
        $ar = [
            'x-client-id'   => $this->clientId,
            'x-api-key'     => $this->apiKey,
            'Content-Type'  => $contentType,
        ];

        return Http::baseUrl($this->apiUrl)
            ->acceptJson()
            ->withHeaders([
                'x-client-id'   => $this->clientId,
                'x-api-key'     => $this->apiKey,
                'Content-Type'  => $contentType,
            ]);
    }


    protected function validateApiToken()
    {
        // Token is expired.
        if (!$this->sawfishIntegration->access_token || $this->sawfishIntegration->expires_in < time()) {
            $this->sawfishIntegration->update([
                'client_id' => $this->clientId,
                'api_key' => $this->apiKey,
            ]);

            $this->sawfishIntegration->refresh();
            $token = $this->sawfishIntegration->access_token;
            return $token;
        }

        return $this->sawfishIntegration->access_token;
    }

    protected function getResponseData(Response $response, $isThrowException = true)
    {
        if (!$response->ok()) {
            $message = $response->json('message') ?? $response->reason();

            if ($isThrowException) {
                throw new Exception($message);
            }

            return $message;
        }

        return $response->json('data') ?? $response->json();
    }

    /**
     * Method: POST.
     */
    // DONE
    public function generateToken()
    {
        $response = $this->withApiKeyHeaders()->post('/token/generate-token');
        $data = $this->getResponseData($response, false);

        if ($data == 'Invalid request') {
            $this->revokeToken();
            return;
        }

        if (isset($data['token']) && isset($data['refresh_token']) && isset($data['expiration'])) {
            $this->sawfishIntegration->access_token = $data['token'];
            $this->sawfishIntegration->refresh_token = $data['refresh_token'];
            $this->sawfishIntegration->expires_in = $data['expiration'];
            $this->sawfishIntegration->save();
        }

        return $data;
    }

    /**
     * Method: POST.
     */
    public function revokeToken()
    {
        $response = $this->withApiKeyHeaders()->post('/token/revoke-token', [
            'refresh_token' => $this->sawfishIntegration->refresh_token,
        ]);

        $this->sawfishIntegration->refresh_token = null;
        $this->sawfishIntegration->access_token = null;
        $this->sawfishIntegration->save();
    }

    /**
     * Method: POST.
     */
    public function refreshToken()
    {
        if (!$this->sawfishIntegration->access_token || !$this->sawfishIntegration->refresh_token) {
            return $this->generateToken();
        }

        if ($this->sawfishIntegration->expires_in > time()) {
            return;
        }

        $refreshTokenResponse = $this->withApiKeyHeaders()->post('/token/refresh-token', [
            'refresh_token' => $this->sawfishIntegration->refresh_token,
        ]);

        $refreshTokenResponse = $this->getResponseData($refreshTokenResponse, false);

        if ($refreshTokenResponse == 'Invalid refresh token') {
            $this->revokeToken();
            return $this->generateToken();
        }

        if (isset($refreshTokenResponse['token'])) {
            $this->sawfishIntegration->access_token = $refreshTokenResponse['token'];
            $this->sawfishIntegration->refresh_token = $refreshTokenResponse['refresh_token'];
            $this->sawfishIntegration->expires_in = $refreshTokenResponse['expiration'];
            $this->sawfishIntegration->save();
        }

        return $refreshTokenResponse;
    }

    /**
     * NEW
     */
    public function getAccounts($type = null)
    {
        $response = $this->withTokenHeaders()->get('/accounts?' . http_build_query([
            'type' => $type,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     * NEW
     */
    public function getClients()
    {
        $response = $this->withTokenHeaders()->get('/clients?' . http_build_query([]));

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     * $data = [
     * 'xero_contact_id' => 'nullable|string',
     * 'name' => 'required|string',
     * 'first_name' => 'nullable|string',
     * 'last_name' => 'required|string',
     * 'email' => 'nullable|string',
     * 'phone' => 'nullable|string',
     * 'address_type' => "required_with:address_line_1|in:'pobox', 'street', 'delivery'",
     * 'address_line_1' => 'nullable|string',
     * 'address_line_2' => 'nullable|string',
     * 'postal_code' => 'required_with:address_line_1|numeric',
     * 'city' => 'required_with:address_line_1|string',
     * 'region' => 'required_with:address_line_1|string',
     * 'country' => 'required_with:address_line_1|string',
     * 'payment_term' => 'nullable|array',
     * 'payment_term.sale' => 'nullable|array',
     * 'payment_term.sale.type' => 'nullable|string',
     * 'payment_term.sale.day' => 'nullable|integer',
     * 'payment_term.bill' => 'nullable|array',
     * 'payment_term.bill.type' => 'nullable|string',
     * 'payment_term.bill.day' => 'nullable|integer'
     * ]
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
        if (!is_string($uuids) && !is_array($uuids)) {
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
