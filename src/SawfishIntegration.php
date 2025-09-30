<?php

namespace SprintDigital\SawfishIntegration;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration as ModelSawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Accounts;
use SprintDigital\SawfishIntegration\Resources\Clients;
use SprintDigital\SawfishIntegration\Resources\Tokens;
use SprintDigital\SawfishIntegration\Resources\Invoices;
use SprintDigital\SawfishIntegration\Resources\Items;

class SawfishIntegration
{
    protected $sawfishIntegration;
    protected string $apiUrl;
    protected string $clientId;
    protected string $apiKey;

    public function __construct()
    {
        $this->sawfishIntegration = ModelSawfishIntegration::latest()->first();
        $this->clientId = $this->sawfishIntegration->client_id ?? '';
        $this->apiKey = $this->sawfishIntegration->api_key ?? '';
        $this->apiUrl = config('sawfish-integration.api_url');
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
        self::ensureValidToken();
        $this->sawfishIntegration->refresh();
        return $this->sawfishIntegration->access_token;
    }

    protected function getResponseData(Response $response)
    {
        if (!$response->ok()) {
            $message = $response->json('message') ?? $response->reason();

            return [
                'status' => 'ERROR',
                'message' => $message,
            ];
        }

        if(!$response->json('pagination') && $response->json('data')) {
            return $response->json('data');
        } else {
            return $response->json();
        }
    }

    /**
     * Handle static method calls and route them to appropriate resource classes
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        // Check if SawfishIntegration instance exists and has required data
        $sawfishIntegration = ModelSawfishIntegration::latest()->first();
        if (!$sawfishIntegration || !$sawfishIntegration->client_id || !$sawfishIntegration->api_key) {
            return [
                'status' => 'ERROR',
                'message' => 'No SawfishIntegration setup found or configuration is incomplete',
            ];
        }

        // Map methods to their corresponding resource classes
        $methodMap = [
            // Accounts resource methods
            'getAccounts' => Accounts::class,

            // Clients resource methods
            'getClients' => Clients::class,
            'createClient' => Clients::class,
            'getClientByUuids' => Clients::class,
            'addContactPersons' => Clients::class,
            'updateContactPersons' => Clients::class,

            // Tokens resource methods
            'generateToken' => Tokens::class,
            'refreshToken' => Tokens::class,
            'revokeToken' => Tokens::class,
            'ensureValidToken' => Tokens::class,

            // Invoices resource methods
            'getInvoices' => Invoices::class,
            'getInvoiceByUuid' => Invoices::class,
            'createInvoice' => Invoices::class,
            'updateInvoice' => Invoices::class,
            'voidInvoice' => Invoices::class,
            'sendInvoice' => Invoices::class,
            'getPdfInvoiceLink' => Invoices::class,
            'addInvoiceAttachments' => Invoices::class,
            'deleteInvoiceAttachments' => Invoices::class,
            'manualInvoicePayment' => Invoices::class,

            // Items resource methods
            'getItems' => Items::class,
        ];

        if (isset($methodMap[$method])) {
            $resourceClass = $methodMap[$method];
            $resource = app($resourceClass);

            return call_user_func_array([$resource, $method], $arguments);
        }

        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }
}
