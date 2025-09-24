<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Invoices;

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

    $this->invoices = new Invoices();
});

describe('getInvoices method', function () {
    it('can get invoices with string UUIDs parameter', function () {
        $uuidString = 'uuid1,uuid2,uuid3';
        $invoicesData = [
            'invoices' => [
                [
                    'uuid' => 'uuid1',
                    'invoice_number' => 'INV-001',
                    'reference' => 'REF-001',
                    'invoice_status' => 'Draft',
                    'xero_status' => 'DRAFT',
                    'status' => 'SUCCESS',
                    'issue_date' => '2025-06-20T00:00:00.000000Z',
                    'due_date' => '2025-07-20T00:00:00.000000Z',
                    'subtotal' => 100,
                    'total' => 110,
                    'amount_paid' => 0,
                    'amount_due' => 110,
                    'total_tax' => 10,
                    'total_discount' => 0,
                    'is_email_sent' => false,
                    'is_overpaid' => false,
                    'client' => [
                        'uuid' => 'client-uuid-1',
                        'client_no' => null,
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'full_name' => 'John Doe',
                        'email' => 'john@example.com',
                        'phone' => '+1234567890',
                        'abn' => null,
                        'is_customer' => true,
                        'payment_terms' => [
                            'bill' => ['day' => null, 'type' => null],
                            'sale' => ['day' => null, 'type' => null]
                        ],
                        'accounting_provider_uuid' => 'provider-uuid-1'
                    ],
                    'line_items' => [
                        [
                            'description' => 'Test Item 1',
                            'quantity' => 1,
                            'unit_amount' => 100,
                            'discount_amount' => 0,
                            'total' => 100
                        ]
                    ],
                    'sawfish_link' => 'https://sawfish.2mm.io/invoice/uuid1',
                    'accounting_provider_uuid' => 'provider-uuid-1'
                ],
                [
                    'uuid' => 'uuid2',
                    'invoice_number' => 'INV-002',
                    'reference' => 'REF-002',
                    'invoice_status' => 'Paid',
                    'xero_status' => 'PAID',
                    'status' => 'SUCCESS',
                    'issue_date' => '2025-06-21T00:00:00.000000Z',
                    'due_date' => '2025-07-21T00:00:00.000000Z',
                    'subtotal' => 200,
                    'total' => 220,
                    'amount_paid' => 220,
                    'amount_due' => 0,
                    'total_tax' => 20,
                    'total_discount' => 0,
                    'is_email_sent' => true,
                    'is_overpaid' => false,
                    'client' => [
                        'uuid' => 'client-uuid-2',
                        'client_no' => 'CLI-002',
                        'first_name' => 'Jane',
                        'last_name' => 'Smith',
                        'full_name' => 'Jane Smith',
                        'email' => 'jane@example.com',
                        'phone' => '+0987654321',
                        'abn' => '12345678901',
                        'is_customer' => true,
                        'payment_terms' => [
                            'bill' => ['day' => 30, 'type' => 'days'],
                            'sale' => ['day' => 14, 'type' => 'days']
                        ],
                        'accounting_provider_uuid' => 'provider-uuid-2'
                    ],
                    'line_items' => [
                        [
                            'description' => 'Test Item 2',
                            'quantity' => 2,
                            'unit_amount' => 100,
                            'discount_amount' => 0,
                            'total' => 200
                        ]
                    ],
                    'sawfish_link' => 'https://sawfish.2mm.io/invoice/uuid2',
                    'accounting_provider_uuid' => 'provider-uuid-2'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($invoicesData, 200),
        ]);

        // Call the method
        $result = $this->invoices->getInvoices($uuidString);

        // Assert the result
        expect($result)->toBe($invoicesData);
        expect($result)->toHaveKey('invoices');
        expect($result['invoices'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuidString) {
            return str_contains($request->url(), '/invoices?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=' . urlencode($uuidString));
        });
    });

    it('can get invoices with array UUIDs parameter', function () {
        $uuidArray = ['uuid1', 'uuid2', 'uuid3'];
        $invoicesData = [
            'invoices' => [
                [
                    'uuid' => 'uuid1',
                    'invoice_number' => 'INV-001',
                    'reference' => 'REF-001',
                    'invoice_status' => 'Draft',
                    'xero_status' => 'DRAFT',
                    'status' => 'SUCCESS',
                    'issue_date' => '2025-06-20T00:00:00.000000Z',
                    'due_date' => '2025-07-20T00:00:00.000000Z',
                    'subtotal' => 100,
                    'total' => 110,
                    'amount_paid' => 0,
                    'amount_due' => 110,
                    'total_tax' => 10,
                    'total_discount' => 0,
                    'is_email_sent' => false,
                    'is_overpaid' => false,
                    'client' => [
                        'uuid' => 'client-uuid-1',
                        'client_no' => null,
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'full_name' => 'John Doe',
                        'email' => 'john@example.com',
                        'phone' => '+1234567890',
                        'abn' => null,
                        'is_customer' => true,
                        'payment_terms' => [
                            'bill' => ['day' => null, 'type' => null],
                            'sale' => ['day' => null, 'type' => null]
                        ],
                        'accounting_provider_uuid' => 'provider-uuid-1'
                    ],
                    'line_items' => [
                        [
                            'description' => 'Test Item 1',
                            'quantity' => 1,
                            'unit_amount' => 100,
                            'discount_amount' => 0,
                            'total' => 100
                        ]
                    ],
                    'sawfish_link' => 'https://sawfish.2mm.io/invoice/uuid1',
                    'accounting_provider_uuid' => 'provider-uuid-1'
                ]
            ]
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($invoicesData, 200),
        ]);

        // Call the method
        $result = $this->invoices->getInvoices($uuidArray);

        // Assert the result
        expect($result)->toBe($invoicesData);
        expect($result)->toHaveKey('invoices');
        expect($result['invoices'])->toHaveCount(1);

        // Verify HTTP was called with correct parameters (array should be converted to comma-separated string)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/invoices?') &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   str_contains($request->url(), 'uuids=uuid1%2Cuuid2%2Cuuid3');
        });
    });

    it('throws InvalidArgumentException for null parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->invoices->getInvoices(null);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for integer parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->invoices->getInvoices(123);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for boolean parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->invoices->getInvoices(true);
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('throws InvalidArgumentException for object parameter', function () {
        // Expect exception to be thrown
        expect(function () {
            $this->invoices->getInvoices(new stdClass());
        })->toThrow(\InvalidArgumentException::class, 'The $uuids parameter must be a string or an array.');
    });

    it('handles empty string parameter', function () {
        $emptyString = '';
        $invoicesData = [
            'invoices' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($invoicesData, 200),
        ]);

        // Call the method
        $result = $this->invoices->getInvoices($emptyString);

        // Assert the result
        expect($result)->toBe($invoicesData);
        expect($result)->toHaveKey('invoices');
        expect($result['invoices'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/invoices?') &&
                   $request->method() === 'GET' &&
                   str_contains($request->url(), 'uuids=');
        });
    });

    it('handles empty array parameter', function () {
        $emptyArray = [];
        $invoicesData = [
            'invoices' => [],
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($invoicesData, 200),
        ]);

        // Call the method
        $result = $this->invoices->getInvoices($emptyArray);

        // Assert the result
        expect($result)->toBe($invoicesData);
        expect($result)->toHaveKey('invoices');
        expect($result['invoices'])->toBeEmpty();

        // Verify HTTP was called with empty uuids parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/invoices?') &&
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
        $result = $this->invoices->getInvoices($uuidString);

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
            $this->invoices->getInvoices($uuidString);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('createInvoice method', function () {
    it('can create an invoice with valid data', function () {
        $invoiceData = [
            'client_uuid' => 'client-uuid-123',
            'invoice_number' => 'INV-001',
            'reference' => 'REF-001',
            'type' => 'ACCREC',
            'date' => '2025-06-20',
            'due_date' => '2025-07-20',
            'currency_code' => 'AUD',
            'line_amount_type' => 'Exclusive',
            'status' => 'Draft',
            'line_items' => [
                [
                    'description' => 'Test Item 1',
                    'quantity' => 1,
                    'unit_amount' => 100,
                    'item_code' => 'ITEM-001',
                    'account_code' => 'ACC-001',
                    'discount_rate' => 0
                ],
                [
                    'description' => 'Test Item 2',
                    'quantity' => 2,
                    'unit_amount' => 50,
                    'item_code' => 'ITEM-002',
                    'account_code' => 'ACC-002',
                    'discount_rate' => 10
                ]
            ]
        ];

        $responseData = [
            'uuid' => 'new-invoice-uuid',
            'invoice_number' => 'INV-001',
            'reference' => 'REF-001',
            'status' => 'SUCCESS',
            'message' => 'Invoice created successfully'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->createInvoice($invoiceData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('uuid');
        expect($result)->toHaveKey('invoice_number');
        expect($result['uuid'])->toBe('new-invoice-uuid');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($invoiceData) {
            return $request->url() === $this->apiUrl . '/invoices' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->header('Content-Type')[0] === 'application/json' &&
                   $request->data() === $invoiceData;
        });
    });

    it('can create an invoice with minimal required data', function () {
        $invoiceData = [
            'client_uuid' => 'client-uuid-123',
            'invoice_number' => 'INV-002',
            'reference' => 'REF-002',
            'status' => 'Draft',
            'line_items' => [
                [
                    'description' => 'Minimal Item',
                    'quantity' => 1,
                    'unit_amount' => 50
                ]
            ]
        ];

        $responseData = [
            'uuid' => 'minimal-invoice-uuid',
            'invoice_number' => 'INV-002',
            'reference' => 'REF-002',
            'status' => 'SUCCESS'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->createInvoice($invoiceData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe('minimal-invoice-uuid');

        // Verify HTTP was called
        Http::assertSent(function ($request) use ($invoiceData) {
            return $request->url() === $this->apiUrl . '/invoices' &&
                   $request->method() === 'POST' &&
                   $request->data() === $invoiceData;
        });
    });

    it('can create an invoice with tracking data', function () {
        $invoiceData = [
            'client_uuid' => 'client-uuid-123',
            'invoice_number' => 'INV-003',
            'reference' => 'REF-003',
            'status' => 'Draft',
            'line_items' => [
                [
                    'description' => 'Item with Tracking',
                    'quantity' => 1,
                    'unit_amount' => 100,
                    'tracking' => [
                        'category_id' => ['tracking-cat-1'],
                        'category_name' => ['Category 1'],
                        'category_item_name' => ['Item 1']
                    ]
                ]
            ]
        ];

        $responseData = [
            'uuid' => 'tracking-invoice-uuid',
            'invoice_number' => 'INV-003',
            'status' => 'SUCCESS'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->createInvoice($invoiceData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe('tracking-invoice-uuid');
    });

    it('handles API validation error response', function () {
        $invalidInvoiceData = [
            'client_uuid' => 'invalid-client-uuid',
            'invoice_number' => '', // Invalid: empty string
            'reference' => 'REF-001',
            'status' => 'InvalidStatus', // Invalid status
            'line_items' => [] // Invalid: empty array
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Validation failed',
            'errors' => [
                'invoice_number' => ['The invoice number field is required.'],
                'status' => ['The selected status is invalid.'],
                'line_items' => ['The line items field is required.']
            ]
        ];

        // Mock the HTTP response for validation error
        Http::fake([
            $this->apiUrl . '/invoices' => Http::response($errorResponse, 422),
        ]);

        // Call the method
        $result = $this->invoices->createInvoice($invalidInvoiceData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Validation failed');
    });

    it('handles network exception during invoice creation', function () {
        $invoiceData = [
            'client_uuid' => 'client-uuid-123',
            'invoice_number' => 'INV-001',
            'reference' => 'REF-001',
            'status' => 'Draft',
            'line_items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_amount' => 100
                ]
            ]
        ];

        // Mock network exception
        Http::fake([
            $this->apiUrl . '/invoices' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        // Expect exception to be thrown
        expect(function () use ($invoiceData) {
            $this->invoices->createInvoice($invoiceData);
        })->toThrow(\Exception::class, 'Connection timeout');
    });
});

describe('updateInvoice method', function () {
    it('can update an invoice with valid data', function () {
        $uuid = 'invoice-uuid-123';
        $updateData = [
            'client_uuid' => 'client-uuid-123',
            'is_line_items_updated' => true,
            'reference' => 'Updated REF-001',
            'status' => 'Pending',
            'line_items' => [
                [
                    'description' => 'Updated Item 1',
                    'quantity' => 2,
                    'unit_amount' => 75,
                    'item_code' => 'ITEM-001',
                    'account_code' => 'ACC-001'
                ]
            ]
        ];

        $responseData = [
            'uuid' => $uuid,
            'reference' => 'Updated REF-001',
            'status' => 'SUCCESS',
            'message' => 'Invoice updated successfully'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->updateInvoice($uuid, $updateData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe($uuid);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $updateData) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid &&
                   $request->method() === 'PUT' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $updateData;
        });
    });

    it('handles API error response for update', function () {
        $uuid = 'invalid-invoice-uuid';
        $updateData = [
            'client_uuid' => 'client-uuid-123',
            'is_line_items_updated' => true,
            'reference' => 'Updated REF-001',
            'status' => 'InvalidStatus',
            'line_items' => []
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invoice not found'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid => Http::response($errorResponse, 404),
        ]);

        // Call the method
        $result = $this->invoices->updateInvoice($uuid, $updateData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invoice not found');
    });
});

describe('voidInvoice method', function () {
    it('can void an invoice successfully', function () {
        $uuid = 'invoice-uuid-123';
        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Invoice voided successfully'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/void' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->voidInvoice($uuid);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe($uuid);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/void' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles API error response for void', function () {
        $uuid = 'invalid-invoice-uuid';
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invoice not found or cannot be voided'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/void' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->invoices->voidInvoice($uuid);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invoice not found or cannot be voided');
    });
});

describe('sendInvoice method', function () {
    it('can send an invoice successfully', function () {
        $uuid = 'invoice-uuid-123';
        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Invoice sent successfully',
            'email_sent' => true
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/send' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->sendInvoice($uuid);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['uuid'])->toBe($uuid);
        expect($result['email_sent'])->toBeTrue();

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/send' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles API error response for send', function () {
        $uuid = 'invalid-invoice-uuid';
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invoice not found or cannot be sent'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/send' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->invoices->sendInvoice($uuid);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invoice not found or cannot be sent');
    });
});

describe('getPdfInvoiceLink method', function () {
    it('can get PDF invoice link successfully', function () {
        $uuid = 'invoice-uuid-123';
        $responseData = [
            'uuid' => $uuid,
            'pdf_url' => 'https://sawfish.2mm.io/invoices/' . $uuid . '/pdf/download',
            'expires_at' => '2025-12-31T23:59:59.000000Z'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/pdf' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->getPdfInvoiceLink($uuid);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('pdf_url');
        expect($result)->toHaveKey('expires_at');
        expect($result['pdf_url'])->toContain('/pdf/download');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/pdf' &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles API error response for PDF link', function () {
        $uuid = 'invalid-invoice-uuid';
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invoice not found or PDF not available'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/pdf' => Http::response($errorResponse, 404),
        ]);

        // Call the method
        $result = $this->invoices->getPdfInvoiceLink($uuid);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invoice not found or PDF not available');
    });
});

describe('addInvoiceAttachments method', function () {
    it('can add attachments to an invoice successfully', function () {
        $uuid = 'invoice-uuid-123';
        $attachmentData = [
            'attachments' => [
                [
                    'name' => 'receipt.pdf',
                    'content_type' => 'application/pdf',
                    'data' => 'base64-encoded-data'
                ],
                [
                    'name' => 'contract.docx',
                    'content_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'data' => 'base64-encoded-data'
                ]
            ]
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Attachments added successfully',
            'attachment_ids' => ['attachment-1', 'attachment-2']
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/attachments' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->addInvoiceAttachments($uuid, $attachmentData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('attachment_ids');
        expect($result['attachment_ids'])->toHaveCount(2);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $attachmentData) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/attachments' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $attachmentData;
        });
    });

    it('handles API error response for adding attachments', function () {
        $uuid = 'invalid-invoice-uuid';
        $attachmentData = [
            'attachments' => [
                [
                    'name' => 'invalid-file.exe',
                    'content_type' => 'application/x-executable',
                    'data' => 'base64-encoded-data'
                ]
            ]
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid file type or invoice not found'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/attachments' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->invoices->addInvoiceAttachments($uuid, $attachmentData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid file type or invoice not found');
    });
});

describe('deleteInvoiceAttachments method', function () {
    it('can delete invoice attachment successfully', function () {
        $uuid = 'invoice-uuid-123';
        $attachmentId = 'attachment-123';
        $responseData = [
            'uuid' => $uuid,
            'attachment_id' => $attachmentId,
            'status' => 'SUCCESS',
            'message' => 'Attachment deleted successfully'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/attachments/' . $attachmentId => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->deleteInvoiceAttachments($uuid, $attachmentId);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result['attachment_id'])->toBe($attachmentId);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $attachmentId) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/attachments/' . $attachmentId &&
                   $request->method() === 'DELETE' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles API error response for deleting attachment', function () {
        $uuid = 'invoice-uuid-123';
        $attachmentId = 'invalid-attachment-id';
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Attachment not found'
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/attachments/' . $attachmentId => Http::response($errorResponse, 404),
        ]);

        // Call the method
        $result = $this->invoices->deleteInvoiceAttachments($uuid, $attachmentId);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Attachment not found');
    });
});

describe('manualInvoicePayment method', function () {
    it('can process manual invoice payment successfully', function () {
        $uuid = 'invoice-uuid-123';
        $paymentData = [
            'payment_date' => '2025-06-20',
            'amount' => 100,
            'payment_method' => 'Bank Transfer',
            'reference' => 'PAY-REF-001'
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Payment recorded successfully',
            'payment_id' => 'payment-123',
            'amount_paid' => 100,
            'amount_due' => 0
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/manual-payment' => Http::response($responseData, 200),
        ]);

        // Call the method
        $result = $this->invoices->manualInvoicePayment($uuid, $paymentData);

        // Assert the result
        expect($result)->toBe($responseData);
        expect($result)->toHaveKey('payment_id');
        expect($result['amount_paid'])->toBe(100);

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) use ($uuid, $paymentData) {
            return $request->url() === $this->apiUrl . '/invoices/' . $uuid . '/manual-payment' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $paymentData;
        });
    });

    it('handles API error response for manual payment', function () {
        $uuid = 'invoice-uuid-123';
        $paymentData = [
            'payment_date' => 'invalid-date',
            'amount' => -100, // Invalid negative amount
            'payment_method' => 'Bank Transfer',
            'reference' => 'PAY-REF-001'
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid payment data',
            'errors' => [
                'payment_date' => ['The payment date field must be a valid date.'],
                'amount' => ['The amount field must be greater than 0.']
            ]
        ];

        // Mock the HTTP response for error case
        Http::fake([
            $this->apiUrl . '/invoices/' . $uuid . '/manual-payment' => Http::response($errorResponse, 422),
        ]);

        // Call the method
        $result = $this->invoices->manualInvoicePayment($uuid, $paymentData);

        // Assert the error response
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid payment data');
    });
});

describe('HTTP headers and authentication', function () {
    it('uses correct token headers for all invoice methods', function () {
        $uuid = 'test-invoice-uuid';
        $testData = ['test' => 'data'];

        // Mock responses for all methods
        Http::fake([
            $this->apiUrl . '/invoices' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/void' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/send' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/pdf' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/attachments' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/attachments/attachment-123' => Http::response(['status' => 'SUCCESS'], 200),
            $this->apiUrl . '/invoices/' . $uuid . '/manual-payment' => Http::response(['status' => 'SUCCESS'], 200),
        ]);

        // Test all methods
        $this->invoices->createInvoice($testData);
        $this->invoices->updateInvoice($uuid, $testData);
        $this->invoices->voidInvoice($uuid);
        $this->invoices->sendInvoice($uuid);
        $this->invoices->getPdfInvoiceLink($uuid);
        $this->invoices->addInvoiceAttachments($uuid, $testData);
        $this->invoices->deleteInvoiceAttachments($uuid, 'attachment-123');
        $this->invoices->manualInvoicePayment($uuid, $testData);

        // Verify all requests used correct headers
        Http::assertSent(function ($request) {
            return $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->header('Accept')[0] === 'application/json';
        });
    });
});
