<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Items;

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

    $this->items = new Items();
});

describe('getItems method', function () {
    it('can get items with string UUIDs parameter', function () {
        $uuidString = 'uuid1,uuid2,uuid3';
        $itemsData = [
            'items' => [
                [
                    'uuid' => 'uuid1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Item 1',
                    'description' => 'Item 1 Description',
                    'sale_price' => 100,
                    'account_code' => '260',
                ],
                [
                    'uuid' => 'uuid2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '2',
                    'name' => 'Item 2',
                    'description' => 'Item 2 Description',
                    'sale_price' => 200,
                    'account_code' => '260',
                ],
                [
                    'uuid' => 'uuid3',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '3',
                    'name' => 'Item 3',
                    'description' => 'Item 3 Description',
                    'sale_price' => 300,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidString);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toHaveCount(3);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuidString) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($uuidString));
        });
    });

    it('can get items with array UUIDs parameter', function () {
        $uuidArray = ['uuid1', 'uuid2', 'uuid3'];
        $itemsData = [
            'items' => [
                [
                    'uuid' => 'uuid1',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Item 1',
                    'description' => 'Item 1 Description',
                    'sale_price' => 100,
                    'account_code' => '260',
                ],
                [
                    'uuid' => 'uuid2',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '2',
                    'name' => 'Item 2',
                    'description' => 'Item 2 Description',
                    'sale_price' => 200,
                    'account_code' => '260',
                ],
                [
                    'uuid' => 'uuid3',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '3',
                    'name' => 'Item 3',
                    'description' => 'Item 3 Description',
                    'sale_price' => 300,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidArray);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toHaveCount(3);

        // Verify HTTP was called with correct parameters (array should be converted to comma-separated string)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=uuid1%2Cuuid2%2Cuuid3');
        });
    });

    it('can get items with single UUID string', function () {
        $singleUuid = 'single-uuid-123';
        $itemsData = [
            'items' => [
                [
                    'uuid' => 'single-uuid-123',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Single Item',
                    'description' => 'Single Item Description',
                    'sale_price' => 150,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($singleUuid);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toHaveCount(1);
        expect($result['items'][0]['uuid'])->toBe('single-uuid-123');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($singleUuid) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($singleUuid));
        });
    });

    it('can get items with single UUID array', function () {
        $singleUuidArray = ['single-uuid-456'];
        $itemsData = [
            'items' => [
                [
                    'uuid' => 'single-uuid-456',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Single Item from Array',
                    'description' => 'Single Item from Array Description',
                    'sale_price' => 250,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($singleUuidArray);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toHaveCount(1);
        expect($result['items'][0]['uuid'])->toBe('single-uuid-456');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=single-uuid-456');
        });
    });

    it('throws InvalidArgumentException for null parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->items->getItems(null);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for integer parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->items->getItems(123);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for boolean parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->items->getItems(true);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for object parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->items->getItems(new stdClass());
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('handles empty string parameter', function () {
        $emptyString = '';
        $itemsData = [
            'items' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($emptyString);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=');
        });
    });

    it('handles empty array parameter', function () {
        $emptyArray = [];
        $itemsData = [
            'items' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($emptyArray);

        // Assert the result
        expect($result)->toBe($itemsData);
        expect($result)->toHaveKey('items');
        expect($result['items'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
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
        $result = $this->items->getItems($uuidString);

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
            $this->items->getItems($uuidString);
        })->toThrow(\Exception::class, 'Connection timeout');
    });

    it('properly URL encodes UUIDs with special characters', function () {
        $uuidsWithSpecialChars = ['uuid with spaces', 'uuid-with-dashes', 'uuid_with_underscores'];
        $itemsData = [
            'items' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidsWithSpecialChars);

        // Assert the result
        expect($result)->toBe($itemsData);

        // Verify HTTP was called with properly encoded UUIDs
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
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
        $itemsData = [
            'items' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Winter Green Couch',
                    'description' => 'Winter Green Couch',
                    'sale_price' => 0,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidString);

        // Assert the result
        expect($result)->toBe($itemsData);

        // Verify HTTP was called with correct authentication headers
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
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
        $itemsData = [
            'items' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Winter Green Couch',
                    'description' => 'Winter Green Couch',
                    'sale_price' => 0,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidString);

        // Assert the result
        expect($result)->toBe($itemsData);

        // Verify HTTP was called (token validation should be handled by the base class)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/items?') &&
                   $request->method() === 'GET';
        });
    });
});

describe('URL construction and query parameters', function () {
    it('constructs correct URL with query parameters', function () {
        $uuidString = '52cd3eae-73e1-4b87-a05d-4ee37e96da40,2a07eb1c-df81-4d75-ad14-32a875713f5a';
        $itemsData = [
            'items' => [
                [
                    'uuid' => '52cd3eae-73e1-4b87-a05d-4ee37e96da40',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '1',
                    'name' => 'Winter Green Couch',
                    'description' => 'Winter Green Couch',
                    'sale_price' => 0,
                    'account_code' => '260',
                ],
                [
                    'uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'accounting_provider_uuid' => '2a07eb1c-df81-4d75-ad14-32a875713f5a',
                    'code' => '2',
                    'name' => 'Another Item',
                    'description' => 'Another Item Description',
                    'sale_price' => 100,
                    'account_code' => '260',
                ],
            ],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidString);

        // Assert the result
        expect($result)->toBe($itemsData);

        // Verify the URL construction
        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, '/items?') &&
                   str_contains($url, 'uuids=52cd3eae-73e1-4b87-a05d-4ee37e96da40%2C2a07eb1c-df81-4d75-ad14-32a875713f5a') &&
                   $request->method() === 'GET';
        });
    });

    it('handles UUIDs with query parameter characters', function () {
        $uuidsWithQueryChars = ['uuid&test=1', 'uuid?param=value', 'uuid#fragment'];
        $itemsData = [
            'items' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($itemsData, 200),
        ]);

        // Call the method
        $result = $this->items->getItems($uuidsWithQueryChars);

        // Assert the result
        expect($result)->toBe($itemsData);

        // Verify HTTP was called with properly encoded UUIDs
        Http::assertSent(function ($request) {
            $url = $request->url();
            return str_contains($url, '/items?') &&
                   str_contains($url, 'uuids=') &&
                   str_contains($url, 'uuid%26test%3D1') && // & encoded as %26
                   str_contains($url, 'uuid%3Fparam%3Dvalue') && // ? encoded as %3F
                   str_contains($url, 'uuid%23fragment'); // # encoded as %23
        });
    });
});
