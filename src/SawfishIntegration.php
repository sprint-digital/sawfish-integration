<?php

namespace SprintDigital\SawfishIntegration;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration as ModelSawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\SawfishWebhook;
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

    public function __construct(?string $clientId = null)
    {
        $this->sawfishIntegration = ($clientId
            ? ModelSawfishIntegration::where('client_id', $clientId)->first()
            : null) ?? ModelSawfishIntegration::latest()->first();

        $this->clientId = $this->sawfishIntegration?->client_id ?? '';
        $this->apiKey = $this->sawfishIntegration?->api_key ?? '';
        $this->apiUrl = config('sawfish-integration.api_url') ?? '';
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
        $this->ensureValidToken();
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

    protected function getMethodMap(): array
    {
        return [
            // Accounts resource methods
            'getAccounts' => Accounts::class,

            // Clients resource methods
            'getClients' => Clients::class,
            'getSuppliers' => Clients::class,
            'getClientsByProviderUuids' => Clients::class,
            'getClientByUuids' => Clients::class,
            'verifyClient' => Clients::class,
            'createClient' => Clients::class,
            'updateClient' => Clients::class,
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
            'getInvoicesByProviderUuids' => Invoices::class,
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

            // Bills resource methods
            'getBills' => Bills::class,
            'getBillByUuid' => Bills::class,
            'getBillsByProviderUuid' => Bills::class,
            'createBill' => Bills::class,
            'updateBill' => Bills::class,
            'voidBill' => Bills::class,

            // SawfishWebhook resource methods
            'saveWebhook' => SawfishWebhook::class,
        ];
    }

    protected function configurationError(): array
    {
        return [
            'status' => 'ERROR',
            'message' => 'No SawfishIntegration setup found or configuration is incomplete',
        ];
    }

    /**
     * Route instance method calls to the appropriate resource class.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (!$this->sawfishIntegration || !$this->sawfishIntegration->client_id || !$this->sawfishIntegration->api_key) {
            return $this->configurationError();
        }

        $methodMap = $this->getMethodMap();

        if (isset($methodMap[$method])) {
            $resource = new $methodMap[$method]($this->sawfishIntegration->id);

            return call_user_func_array([$resource, $method], $arguments);
        }

        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }

    /**
     * Route static method calls through a default instance (latest integration).
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return (new static())->$method(...$arguments);
    }
}
