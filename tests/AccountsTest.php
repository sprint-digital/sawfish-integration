<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Accounts;

beforeEach(function () {
    $this->apiUrl = config('sawfish-integration.api_url');

    // Create a test SawfishIntegration model instance
    $this->sawfishIntegration = SawfishIntegration::factory()->create([
        'client_id' => 'test-client-id',
        'api_key' => 'test-api-key',
        'access_token' => 'test-access-token',
        'refresh_token' => 'test-refresh-token',
        'expires_in' => time() + 3600, // 1 hour from now
    ]);

    $this->clientId = $this->sawfishIntegration->client_id;
    $this->apiKey = $this->sawfishIntegration->api_key;

    $this->accounts = new Accounts();
});

describe('getAccounts method', function () {
    it('can get accounts with string UUIDs parameter', function () {
        $uuidString = 'uuid1,uuid2,uuid3';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'uuid1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => null,
                    'name' => 'Account 1',
                    'description' => 'Account 1 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'uuid2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC001',
                    'name' => 'Account 2',
                    'description' => 'Account 2 Description',
                    'type' => 'LIABILITY',
                    'classification' => 'LIABILITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'uuid3',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC002',
                    'name' => 'Account 3',
                    'description' => 'Account 3 Description',
                    'type' => 'EQUITY',
                    'classification' => 'EQUITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(3);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuidString) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($uuidString));
        });
    });

    it('can get accounts with array UUIDs parameter', function () {
        $uuidArray = ['uuid1', 'uuid2', 'uuid3'];
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'uuid1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => null,
                    'name' => 'Account 1',
                    'description' => 'Account 1 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'uuid2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC001',
                    'name' => 'Account 2',
                    'description' => 'Account 2 Description',
                    'type' => 'LIABILITY',
                    'classification' => 'LIABILITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'uuid3',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC002',
                    'name' => 'Account 3',
                    'description' => 'Account 3 Description',
                    'type' => 'EQUITY',
                    'classification' => 'EQUITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidArray);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(3);

        // Verify HTTP was called with correct parameters (array should be converted to comma-separated string)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=uuid1%2Cuuid2%2Cuuid3');
        });
    });

    it('can get accounts with type filter', function () {
        $uuidString = 'uuid1,uuid2';
        $type = 'ASSET';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'uuid1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => null,
                    'name' => 'Asset Account 1',
                    'description' => 'Asset Account 1 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'uuid2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ASSET001',
                    'name' => 'Asset Account 2',
                    'description' => 'Asset Account 2 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString, $type);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters including type filter
        Http::assertSent(function ($request) use ($uuidString, $type) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($uuidString)) &&
                   str_contains($request->url(), 'type=' . urlencode($type));
        });
    });

    it('can get accounts with only type filter (no UUIDs)', function () {
        $type = 'LIABILITY';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'liability-uuid-1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'LIAB001',
                    'name' => 'Liability Account 1',
                    'description' => 'Liability Account 1 Description',
                    'type' => 'LIABILITY',
                    'classification' => 'LIABILITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => 'liability-uuid-2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'LIAB002',
                    'name' => 'Liability Account 2',
                    'description' => 'Liability Account 2 Description',
                    'type' => 'LIABILITY',
                    'classification' => 'LIABILITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts(null, $type);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(2);

        // Verify HTTP was called with only type parameter
        Http::assertSent(function ($request) use ($type) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'type=' . urlencode($type)) &&
                   !str_contains($request->url(), 'uuids=');
        });
    });

    it('can get accounts with single UUID string', function () {
        $singleUuid = 'single-uuid-123';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'single-uuid-123',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'SINGLE001',
                    'name' => 'Single Account',
                    'description' => 'Single Account Description',
                    'type' => 'EQUITY',
                    'classification' => 'EQUITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($singleUuid);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(1);
        expect($result['accounts'][0]['uuid'])->toBe('single-uuid-123');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($singleUuid) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($singleUuid));
        });
    });

    it('can get accounts with single UUID array', function () {
        $singleUuidArray = ['single-uuid-456'];
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'single-uuid-456',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'SINGLE002',
                    'name' => 'Single Account from Array',
                    'description' => 'Single Account from Array Description',
                    'type' => 'REVENUE',
                    'classification' => 'REVENUE',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($singleUuidArray);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(1);
        expect($result['accounts'][0]['uuid'])->toBe('single-uuid-456');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=single-uuid-456');
        });
    });

    it('throws InvalidArgumentException for integer parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->accounts->getAccounts(123);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for boolean parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->accounts->getAccounts(true);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for object parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->accounts->getAccounts(new stdClass());
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('handles empty string parameter', function () {
        $emptyString = '';
        $accountsData = [
            'accounts' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($emptyString);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=');
        });
    });

    it('handles empty array parameter', function () {
        $emptyArray = [];
        $accountsData = [
            'accounts' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($emptyArray);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=');
        });
    });

    it('handles API error response', function () {
        $uuidString = 'invalid-uuid';
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid UUID format'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/*' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid UUID format');
    });

    it('handles network exception', function () {
        $uuidString = 'test-uuid';

        // Mock network exception
        Http::fake([
            $this->apiUrl . '/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () use ($uuidString) {
            $this->accounts->getAccounts($uuidString);
        })->toThrow(\Exception::class, 'Connection timeout');
    });

    it('properly URL encodes UUIDs with special characters', function () {
        $uuidsWithSpecialChars = ['uuid with spaces', 'uuid-with-dashes', 'uuid_with_underscores'];
        $accountsData = [
            'accounts' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidsWithSpecialChars);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify HTTP was called with properly encoded UUIDs
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=') &&
                   str_contains($request->url(), 'uuid+with+spaces') &&
                   str_contains($request->url(), 'uuid-with-dashes') &&
                   str_contains($request->url(), 'uuid_with_underscores');
        });
    });
});

describe('HTTP headers and authentication', function () {
    it('uses correct token headers for authentication', function () {
        $uuidString = '52cd3eae-73e1-4b87-a05d-4ee37e96da40';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC001',
                    'name' => 'Test Account',
                    'description' => 'Test Account Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify HTTP was called with correct authentication headers
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->header('Accept')[0] === 'application/json';
        });
    });

    it('handles expired token scenario', function () {
        // Set up expired token
        $this->sawfishIntegration->update([
            'expires_in' => time() - 3600, // 1 hour ago (expired)
        ]);

        $uuidString = '52cd3eae-73e1-4b87-a05d-4ee37e96da40';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC001',
                    'name' => 'Test Account',
                    'description' => 'Test Account Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify HTTP was called (token validation should be handled by the base class)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/accounts?') &&
                   $request->method() === 'GET';
        });
    });
});

describe('URL construction and query parameters', function () {
    it('constructs correct URL with query parameters', function () {
        $uuidString = '52cd3eae-73e1-4b87-a05d-4ee37e96da40,2a07eb1c-df81-4d75-ad14-32a875713f5a';
        $type = 'ASSET';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC001',
                    'name' => 'Asset Account 1',
                    'description' => 'Asset Account 1 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
                [
                    'uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'ACC002',
                    'name' => 'Asset Account 2',
                    'description' => 'Asset Account 2 Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString, $type);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify the URL construction
        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, '/accounts?') &&
                   str_contains($url, 'uuids=52cd3eae-73e1-4b87-a05d-4ee37e96da40%2C2a07eb1c-df81-4d75-ad14-32a875713f5a') &&
                   str_contains($url, 'type=ASSET') &&
                   $request->method() === 'GET';
        });
    });

    it('handles UUIDs with query parameter characters', function () {
        $uuidsWithQueryChars = ['uuid&test=1', 'uuid?param=value', 'uuid#fragment'];
        $accountsData = [
            'accounts' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidsWithQueryChars);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify HTTP was called with properly encoded UUIDs
        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, '/accounts?') &&
                   str_contains($url, 'uuids=') &&
                   str_contains($url, 'uuid%26test%3D1') && // & encoded as %26
                   str_contains($url, 'uuid%3Fparam%3Dvalue') && // ? encoded as %3F
                   str_contains($url, 'uuid%23fragment'); // # encoded as %23
        });
    });

    it('handles type parameter with special characters', function () {
        $uuidString = 'test-uuid';
        $typeWithSpecialChars = 'ASSET&LIABILITY';
        $accountsData = [
            'accounts' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString, $typeWithSpecialChars);

        // Assert the result
        expect($result)->toBe($accountsData);

        // Verify HTTP was called with properly encoded type parameter
        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, '/accounts?') &&
                   str_contains($url, 'type=ASSET%26LIABILITY'); // & encoded as %26
        });
    });
});

describe('Account data structure validation', function () {
    it('validates account data structure in response', function () {
        $uuidString = 'test-uuid';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'test-uuid',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => 'TEST001',
                    'name' => 'Test Account',
                    'description' => 'Test Account Description',
                    'type' => 'ASSET',
                    'classification' => 'ASSET',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the result structure
        expect($result)->toBe($accountsData);
        expect($result)->toHaveKey('accounts');
        expect($result['accounts'])->toHaveCount(1);

        $account = $result['accounts'][0];
        expect($account)->toHaveKey('uuid');
        expect($account)->toHaveKey('accounting_provider_uuid');
        expect($account)->toHaveKey('code');
        expect($account)->toHaveKey('name');
        expect($account)->toHaveKey('description');
        expect($account)->toHaveKey('type');
        expect($account)->toHaveKey('classification');
        expect($account)->toHaveKey('tax_type');

        expect($account['uuid'])->toBe('test-uuid');
        expect($account['accounting_provider_uuid'])->toBe('2a07eb1c-df81-4d75-ad14-32a875713f5a');
        expect($account['code'])->toBe('TEST001');
        expect($account['name'])->toBe('Test Account');
        expect($account['description'])->toBe('Test Account Description');
        expect($account['type'])->toBe('ASSET');
        expect($account['classification'])->toBe('ASSET');
        expect($account['tax_type'])->toBe('BASEXCLUDED');
    });

    it('handles accounts with null code field', function () {
        $uuidString = 'test-uuid-null-code';
        $accountsData = [
            'accounts' => [
                [
                    'uuid' => 'test-uuid-null-code',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => null,
                    'name' => 'Account with Null Code',
                    'description' => 'Account with Null Code Description',
                    'type' => 'LIABILITY',
                    'classification' => 'LIABILITY',
                    'tax_type' => 'BASEXCLUDED',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($accountsData, 200),
        ]);

        // Call the method
        $result = $this->accounts->getAccounts($uuidString);

        // Assert the result
        expect($result)->toBe($accountsData);
        expect($result['accounts'][0]['code'])->toBeNull();
    });
});
