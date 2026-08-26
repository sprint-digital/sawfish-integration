<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Bills;

beforeEach(function () {
    $this->apiUrl = config('sawfish-integration.api_url');

    $this->sawfishIntegration = SawfishIntegration::factory()->create([
        'client_id' => 'test-client-id',
        'api_key' => 'test-api-key',
        'access_token' => 'test-access-token',
        'refresh_token' => 'test-refresh-token',
        'expires_in' => time() + 3600, // 1 hour from now
    ]);

    $this->clientId = $this->sawfishIntegration->client_id;
    $this->apiKey = $this->sawfishIntegration->api_key;

    $this->bills = new Bills();
});

describe('addBillAttachments method', function () {
    it('can add attachments to a bill successfully', function () {
        $uuid = 'bill-uuid-123';
        $attachmentData = [
            'attachments' => [
                [
                    'name' => 'receipt.pdf',
                    'content_type' => 'application/pdf',
                    'data' => 'base64-encoded-data',
                ],
            ],
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Attachments added successfully',
            'attachment_ids' => ['attachment-1'],
        ];

        Http::fake([
            $this->apiUrl . '/bills/' . $uuid . '/attachments' => Http::response($responseData, 200),
        ]);

        $result = $this->bills->addBillAttachments($uuid, $attachmentData);

        expect($result)->toBe($responseData);

        Http::assertSent(function ($request) use ($uuid, $attachmentData) {
            return $request->url() === $this->apiUrl . '/bills/' . $uuid . '/attachments' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token' &&
                   $request->data() === $attachmentData;
        });
    });

    it('handles API error response for adding attachments', function () {
        $uuid = 'invalid-bill-uuid';
        $attachmentData = [
            'attachments' => [
                [
                    'name' => 'invalid-file.exe',
                    'content_type' => 'application/x-executable',
                    'data' => 'base64-encoded-data',
                ],
            ],
        ];

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid file type or bill not found',
        ];

        Http::fake([
            $this->apiUrl . '/bills/' . $uuid . '/attachments' => Http::response($errorResponse, 400),
        ]);

        $result = $this->bills->addBillAttachments($uuid, $attachmentData);

        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid file type or bill not found');
    });
});

describe('addBillAttachmentsFromFiles method', function () {
    it('can upload attachment files to a bill successfully', function () {
        $uuid = 'bill-uuid-123';
        $files = [
            [
                'name' => 'receipt.pdf',
                'contents' => 'raw-file-contents',
                'content_type' => 'application/pdf',
            ],
        ];

        $responseData = [
            'uuid' => $uuid,
            'status' => 'SUCCESS',
            'message' => 'Attachments added successfully',
            'attachment_ids' => ['attachment-1'],
        ];

        Http::fake([
            $this->apiUrl . '/bills/' . $uuid . '/attachments' => Http::response($responseData, 200),
        ]);

        $result = $this->bills->addBillAttachmentsFromFiles($uuid, $files);

        expect($result)->toBe($responseData);

        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/bills/' . $uuid . '/attachments' &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });
});
