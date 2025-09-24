<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Clients;

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

    $this->clients = new Clients();
});

describe('getClients method', function () {
    it('can get all clients successfully', function () {
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'client-uuid-1',
                    'client_no' => 'CLI-001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'full_name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'abn' => '12345678901',
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => [
                            'day' => 30,
                            'type' => 'days'
                        ],
                        'sale' => [
                            'day' => 14,
                            'type' => 'days'
                        ]
                    ],
                    'addresses' => [
                        [
                            'type' => 'street',
                            'line_1' => '123 Main St',
                            'line_2' => 'Suite 100',
                            'city' => 'Sydney',
                            'region' => 'NSW',
                            'postal_code' => '2000',
                            'country' => 'Australia'
                        ]
                    ],
                    'phones' => [
                        [
                            'type' => 'mobile',
                            'number' => '+1234567890'
                        ]
                    ],
                    'contacts' => [
                        [
                            'uuid' => 'contact-uuid-1',
                            'first_name' => 'Jane',
                            'last_name' => 'Smith',
                            'email' => 'jane.smith@example.com',
                            'phone' => '+0987654321'
                        ]
                    ],
                    'accounting_provider_uuid' => 'provider-uuid-1'
                ],
                [
                    'uuid' => 'client-uuid-2',
                    'client_no' => null,
                    'first_name' => 'Alice',
                    'last_name' => 'Johnson',
                    'full_name' => 'Alice Johnson',
                    'email' => 'alice.johnson@example.com',
                    'phone' => null,
                    'abn' => null,
                    'is_customer' => false,
                    'payment_terms' => [
                        'bill' => [
                            'day' => null,
                            'type' => null
                        ],
                        'sale' => [
                            'day' => null,
                            'type' => null
                        ]
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-2'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClients();

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/clients') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles empty clients response', function () {
        $clientsData = [
            'clients' => []
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClients();

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toBeEmpty();
    });

    it('handles API error response', function () {
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Access denied'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/*' => Http::response($errorResponse, 403),
        ]);

        // Call the method
        $result = $this->clients->getClients();

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Access denied');
    });

    it('handles network exception', function () {
        // Mock network exception
        Http::fake([
            $this->apiUrl . '/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () {
            $this->clients->getClients();
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('createClient method', function () {
    it('can create a client with valid data', function () {
        $clientData = [
            'name' => 'Test Company',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'address_type' => 'street',
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Suite 100',
            'postal_code' => '2000',
            'city' => 'Sydney',
            'region' => 'NSW',
            'country' => 'Australia',
            'payment_term' => [
                'sale' => [
                    'type' => 'days',
                    'day' => 30
                ],
                'bill' => [
                    'type' => 'days',
                    'day' => 14
                ]
            ]
        ];

        $responseData = [
            'uuid' => 'new-client-uuid',
            'name' => 'Test Company',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'SUCCESS',
            'message' => 'Client created successfully'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/clients' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->clients->createClient($clientData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('uuid');
        expect($result)->toHaveKey('name');
        expect($result['uuid'])->toBe('new-client-uuid');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($clientData) {
            return $request->url() === $this->apiUrl . '/clients' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->header('Content-Type')[0] === 'application/json' &&
                   $request->data() === $clientData;
        });
    });

    it('can create a client with minimal required data', function () {
        $clientData = [
            'name' => 'Minimal Company',
            'last_name' => 'Smith'
        ];

        $responseData = [
            'uuid' => 'minimal-client-uuid',
            'name' => 'Minimal Company',
            'last_name' => 'Smith',
            'status' => 'SUCCESS'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/clients' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->clients->createClient($clientData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe('minimal-client-uuid');

        // Verify HTTP was called
        Http::assertSent(function ($request) use ($clientData) {
            return $request->url() === $this->apiUrl . '/clients' &&
                   $request->method() === 'POST' &&
                   $request->data() === $clientData;
        });
    });

    it('handles API validation error response', function () {
        $invalidClientData = [
            'name' => '', // Invalid: empty string
            'last_name' => '', // Invalid: empty string
            'email' => 'invalid-email', // Invalid: bad email format
            'postal_code' => 'invalid-postal' // Invalid: not numeric
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Validation failed'
        ];

        // Mock the HTTP response for validation error
        Http::fake([
            $this->apiUrl . '/clients' => Http::response($errorResponse, 422),
        ]);

        // Call the method
        $result = $this->clients->createClient($invalidClientData);

        // Assert the error response (createClient returns just the message string on error)
        expect($result)->toBe('Validation failed');
    });

    it('handles network exception during client creation', function () {
        $clientData = [
            'name' => 'Test Company',
            'last_name' => 'Doe'
        ];

        // Mock network exception
        Http::fake([
            $this->apiUrl . '/clients' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () use ($clientData) {
            $this->clients->createClient($clientData);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('getClientByUuids method', function () {
    it('can get clients with string UUIDs parameter', function () {
        $uuidString = 'uuid1,uuid2,uuid3';
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'uuid1',
                    'client_no' => 'CLI-001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'full_name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'abn' => '12345678901',
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => ['day' => 30, 'type' => 'days'],
                        'sale' => ['day' => 14, 'type' => 'days']
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-1'
                ],
                [
                    'uuid' => 'uuid2',
                    'client_no' => 'CLI-002',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'full_name' => 'Jane Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+0987654321',
                    'abn' => null,
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => ['day' => null, 'type' => null],
                        'sale' => ['day' => null, 'type' => null]
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-2'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClientByUuids($uuidString);

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuidString) {
            return str_contains($request->url(), '/clients?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($uuidString));
        });
    });

    it('can get clients with array UUIDs parameter', function () {
        $uuidArray = ['uuid1', 'uuid2', 'uuid3'];
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'uuid1',
                    'client_no' => 'CLI-001',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'full_name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'abn' => '12345678901',
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => ['day' => 30, 'type' => 'days'],
                        'sale' => ['day' => 14, 'type' => 'days']
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-1'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClientByUuids($uuidArray);

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toHaveCount(1);

        // Verify HTTP was called with correct parameters (array should be converted to comma-separated string)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/clients?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=uuid1%2Cuuid2%2Cuuid3');
        });
    });

    it('can get clients with single UUID string', function () {
        $singleUuid = 'single-uuid-123';
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'single-uuid-123',
                    'client_no' => 'CLI-SINGLE',
                    'first_name' => 'Single',
                    'last_name' => 'Client',
                    'full_name' => 'Single Client',
                    'email' => 'single@example.com',
                    'phone' => '+1111111111',
                    'abn' => null,
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => ['day' => null, 'type' => null],
                        'sale' => ['day' => null, 'type' => null]
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-single'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClientByUuids($singleUuid);

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toHaveCount(1);
        expect($result['clients'][0]['uuid'])->toBe('single-uuid-123');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($singleUuid) {
            return str_contains($request->url(), '/clients?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($singleUuid));
        });
    });

    it('throws InvalidArgumentException for null parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->clients->getClientByUuids(null);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for integer parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->clients->getClientByUuids(123);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for boolean parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->clients->getClientByUuids(true);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for object parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->clients->getClientByUuids(new stdClass());
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('handles empty string parameter', function () {
        $emptyString = '';
        $clientsData = [
            'clients' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClientByUuids($emptyString);

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/clients?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=');
        });
    });

    it('handles empty array parameter', function () {
        $emptyArray = [];
        $clientsData = [
            'clients' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClientByUuids($emptyArray);

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/clients?') &&
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
        $result = $this->clients->getClientByUuids($uuidString);

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
            $this->clients->getClientByUuids($uuidString);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('addContactPersons method', function () {
    it('can add contact persons to a client successfully', function () {
        $uuid = 'client-uuid-123';
        $contactData = [
            'contact_persons' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Contact',
                    'email' => 'john.contact@example.com',
                    'phone' => '+1234567890',
                    'position' => 'Manager'
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Assistant',
                    'email' => 'jane.assistant@example.com',
                    'phone' => '+0987654321',
                    'position' => 'Assistant'
                ]
            ]
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Contact persons added successfully',
            'contact_person_ids' => ['contact-1', 'contact-2']
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->clients->addContactPersons($uuid, $contactData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('contact_person_ids');
        expect($result['contact_person_ids'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $contactData) {
            return $request->url() === $this->apiUrl . '/clients/' . $uuid . '/contact-persons' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $contactData;
        });
    });

    it('handles API error response for adding contact persons', function () {
        $uuid = 'invalid-client-uuid';
        $contactData = [
            'contact_persons' => [
                [
                    'first_name' => '', // Invalid: empty string
                    'last_name' => 'Contact',
                    'email' => 'invalid-email', // Invalid: bad email format
                    'phone' => '+1234567890',
                    'position' => 'Manager'
                ]
            ]
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid contact person data or client not found'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->clients->addContactPersons($uuid, $contactData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid contact person data or client not found');
    });

    it('handles network exception during contact person addition', function () {
        $uuid = 'client-uuid-123';
        $contactData = [
            'contact_persons' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Contact',
                    'email' => 'john.contact@example.com',
                    'phone' => '+1234567890',
                    'position' => 'Manager'
                ]
            ]
        ];

        // Mock network exception
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () use ($uuid, $contactData) {
            $this->clients->addContactPersons($uuid, $contactData);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('updateContactPersons method', function () {
    it('can update contact persons for a client successfully', function () {
        $uuid = 'client-uuid-123';
        $contactData = [
            'contact_persons' => [
                [
                    'uuid' => 'contact-uuid-1',
                    'first_name' => 'Updated John',
                    'last_name' => 'Updated Contact',
                    'email' => 'updated.john@example.com',
                    'phone' => '+1111111111',
                    'position' => 'Senior Manager'
                ],
                [
                    'uuid' => 'contact-uuid-2',
                    'first_name' => 'Updated Jane',
                    'last_name' => 'Updated Assistant',
                    'email' => 'updated.jane@example.com',
                    'phone' => '+2222222222',
                    'position' => 'Senior Assistant'
                ]
            ]
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Contact persons updated successfully',
            'updated_contact_person_ids' => ['contact-uuid-1', 'contact-uuid-2']
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->clients->updateContactPersons($uuid, $contactData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('updated_contact_person_ids');
        expect($result['updated_contact_person_ids'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $contactData) {
            return $request->url() === $this->apiUrl . '/clients/' . $uuid . '/contact-persons' &&
                   $request->method() === 'PUT' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $contactData;
        });
    });

    it('handles API error response for updating contact persons', function () {
        $uuid = 'invalid-client-uuid';
        $contactData = [
            'contact_persons' => [
                [
                    'uuid' => 'invalid-contact-uuid',
                    'first_name' => 'Updated John',
                    'last_name' => 'Updated Contact',
                    'email' => 'updated.john@example.com',
                    'phone' => '+1111111111',
                    'position' => 'Senior Manager'
                ]
            ]
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Contact person not found or client not found'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => Http::response($errorResponse, 404),
        ]);

        // Call the method
        $result = $this->clients->updateContactPersons($uuid, $contactData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Contact person not found or client not found');
    });

    it('handles network exception during contact person update', function () {
        $uuid = 'client-uuid-123';
        $contactData = [
            'contact_persons' => [
                [
                    'uuid' => 'contact-uuid-1',
                    'first_name' => 'Updated John',
                    'last_name' => 'Updated Contact',
                    'email' => 'updated.john@example.com',
                    'phone' => '+1111111111',
                    'position' => 'Senior Manager'
                ]
            ]
        ];

        // Mock network exception
        Http::fake([
            $this->apiUrl . '/clients/' . $uuid . '/contact-persons' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () use ($uuid, $contactData) {
            $this->clients->updateContactPersons($uuid, $contactData);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('HTTP headers and authentication', function () {
    it('uses correct token headers for all client methods', function () {
        $uuid = 'test-client-uuid';
        $testData = ['test' => 'data'];

        // Mock responses for all methods
        Http::fake([
            $this->apiUrl . '/*' => Http::response(['status' => 'SUCCESS'], 200),
        ]);

        // Test all methods
        $this->clients->getClients();
        $this->clients->createClient($testData);
        $this->clients->getClientByUuids('test-uuid');
        $this->clients->addContactPersons($uuid, $testData);
        $this->clients->updateContactPersons($uuid, $testData);

        // Verify all requests used correct headers
        Http::assertSent(function ($request) {
            return $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->header('Accept')[0] === 'application/json';
        });
    });
});

describe('Client data structure validation', function () {
    it('validates client data structure in response', function () {
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'test-client-uuid',
                    'client_no' => 'CLI-TEST',
                    'first_name' => 'Test',
                    'last_name' => 'Client',
                    'full_name' => 'Test Client',
                    'email' => 'test@example.com',
                    'phone' => '+1234567890',
                    'abn' => '12345678901',
                    'is_customer' => true,
                    'payment_terms' => [
                        'bill' => ['day' => 30, 'type' => 'days'],
                        'sale' => ['day' => 14, 'type' => 'days']
                    ],
                    'addresses' => [
                        [
                            'type' => 'street',
                            'line_1' => '123 Test St',
                            'line_2' => 'Suite 100',
                            'city' => 'Sydney',
                            'region' => 'NSW',
                            'postal_code' => '2000',
                            'country' => 'Australia'
                        ]
                    ],
                    'phones' => [
                        [
                            'type' => 'mobile',
                            'number' => '+1234567890'
                        ]
                    ],
                    'contacts' => [
                        [
                            'uuid' => 'contact-uuid-1',
                            'first_name' => 'Contact',
                            'last_name' => 'Person',
                            'email' => 'contact@example.com',
                            'phone' => '+0987654321'
                        ]
                    ],
                    'accounting_provider_uuid' => 'provider-uuid-test'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClients();

        // Assert the result structure
        expect($result)->toBe($clientsData);
        expect($result)->toHaveKey('clients');
        expect($result['clients'])->toHaveCount(1);

        $client = $result['clients'][0];
        expect($client)->toHaveKey('uuid');
        expect($client)->toHaveKey('client_no');
        expect($client)->toHaveKey('first_name');
        expect($client)->toHaveKey('last_name');
        expect($client)->toHaveKey('full_name');
        expect($client)->toHaveKey('email');
        expect($client)->toHaveKey('phone');
        expect($client)->toHaveKey('abn');
        expect($client)->toHaveKey('is_customer');
        expect($client)->toHaveKey('payment_terms');
        expect($client)->toHaveKey('addresses');
        expect($client)->toHaveKey('phones');
        expect($client)->toHaveKey('contacts');
        expect($client)->toHaveKey('accounting_provider_uuid');

        expect($client['uuid'])->toBe('test-client-uuid');
        expect($client['client_no'])->toBe('CLI-TEST');
        expect($client['first_name'])->toBe('Test');
        expect($client['last_name'])->toBe('Client');
        expect($client['full_name'])->toBe('Test Client');
        expect($client['email'])->toBe('test@example.com');
        expect($client['phone'])->toBe('+1234567890');
        expect($client['abn'])->toBe('12345678901');
        expect($client['is_customer'])->toBeTrue();
        expect($client['accounting_provider_uuid'])->toBe('provider-uuid-test');
    });

    it('handles clients with null optional fields', function () {
        $clientsData = [
            'clients' => [
                [
                    'uuid' => 'minimal-client-uuid',
                    'client_no' => null,
                    'first_name' => 'Minimal',
                    'last_name' => 'Client',
                    'full_name' => 'Minimal Client',
                    'email' => null,
                    'phone' => null,
                    'abn' => null,
                    'is_customer' => false,
                    'payment_terms' => [
                        'bill' => ['day' => null, 'type' => null],
                        'sale' => ['day' => null, 'type' => null]
                    ],
                    'addresses' => [],
                    'phones' => [],
                    'contacts' => [],
                    'accounting_provider_uuid' => 'provider-uuid-minimal'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($clientsData, 200),
        ]);

        // Call the method
        $result = $this->clients->getClients();

        // Assert the result
        expect($result)->toBe($clientsData);
        expect($result['clients'][0]['client_no'])->toBeNull();
        expect($result['clients'][0]['email'])->toBeNull();
        expect($result['clients'][0]['phone'])->toBeNull();
        expect($result['clients'][0]['abn'])->toBeNull();
        expect($result['clients'][0]['is_customer'])->toBeFalse();
    });
});
