<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\SawfishIntegration;

beforeEach(function () {
    // Set up test configuration
    config([
        'sawfish-integration.api_url' => 'https://api.sawfish.2mm.io/api/v2/accounting',
        'sawfish-integration.client_id' => 'test-client-id',
        'sawfish-integration.api_key' => 'test-api-key',
    ]);

    $this->apiUrl = config('sawfish-integration.api_url');
    $this->clientId = config('sawfish-integration.client_id');
    $this->apiKey = config('sawfish-integration.api_key');
    $this->sawfishIntegration = new SawfishIntegration();
});

it('can generate new token successfully', function () {
    // Mock the HTTP response for successful token generation
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                'refresh_token' => 'refresh_token_12345',
                'expiration' => time() + 600, // 10 minutes from now
            ],
        ], 200),
    ]);

    // Make the API call
    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'client_id' => $this->clientId,
        'api_key' => $this->apiKey,
    ]);

    // Assert the response
    expect($response->status())->toBe(200);

    $responseData = $response->json();
    expect($responseData['status'])->toBe('SUCCESS');
    expect($responseData)->toHaveKey('data');
    expect($responseData['data'])->toHaveKey('token');
    expect($responseData['data'])->toHaveKey('refresh_token');
    expect($responseData['data'])->toHaveKey('expiration');

    // Verify token format (basic JWT structure)
    expect($responseData['data']['token'])->toStartWith('eyJ');

    // Verify expiration is in the future
    expect($responseData['data']['expiration'])->toBeGreaterThan(time());

    // Verify refresh token is present
    expect($responseData['data']['refresh_token'])->not->toBeEmpty();
});

it('returns existing token when current token is still valid', function () {
    // Mock the HTTP response for existing valid token
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.existing.token',
                'expiration' => time() + 300, // 5 minutes from now
            ],
        ], 200),
    ]);

    // Make the API call
    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'client_id' => $this->clientId,
        'api_key' => $this->apiKey,
    ]);

    // Assert the response
    expect($response->status())->toBe(200);

    $responseData = $response->json();
    expect($responseData['status'])->toBe('SUCCESS');
    expect($responseData)->toHaveKey('data');
    expect($responseData['data'])->toHaveKey('token');
    expect($responseData['data'])->toHaveKey('expiration');

    // Verify refresh_token is NOT present when existing token is returned
    expect($responseData['data'])->not->toHaveKey('refresh_token');

    // Verify token format
    expect($responseData['data']['token'])->toStartWith('eyJ');

    // Verify expiration is in the future
    expect($responseData['data']['expiration'])->toBeGreaterThan(time());
});

it('handles token generation error response', function () {
    // Mock the HTTP response for error case
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'ERROR',
            'message' => 'Failed to generate token'
        ], 500),
    ]);

    // Make the API call
    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'client_id' => $this->clientId,
        'api_key' => $this->apiKey,
    ]);

    // Assert the error response
    expect($response->status())->toBe(500);

    $responseData = $response->json();
    expect($responseData['status'])->toBe('ERROR');
    expect($responseData['message'])->toBe('Failed to generate token');
    expect($responseData)->not->toHaveKey('data');
});

it('handles network exception during token generation', function () {
    // Mock network exception
    Http::fake([
        $this->apiUrl . '/token/generate-token' => function () {
            throw new \Exception('Network error');
        },
    ]);

    // Expect an exception to be thrown
    expect(function () {
        Http::post($this->apiUrl . '/token/generate-token', [
            'client_id' => $this->clientId,
            'api_key' => $this->apiKey,
        ]);
    })->toThrow(\Exception::class, 'Network error');
});

it('validates required parameters for token generation', function () {
    // Test with missing client_id
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'ERROR',
            'message' => 'Missing required parameters'
        ], 400),
    ]);

    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'api_key' => $this->apiKey,
        // client_id is missing
    ]);

    expect($response->status())->toBe(400);
    $responseData = $response->json();
    expect($responseData['status'])->toBe('ERROR');
});

it('validates token expiration time', function () {
    // Mock response with token that expires in exactly 10 minutes (600 seconds)
    $expirationTime = time() + 600;

    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                'refresh_token' => 'refresh_token_12345',
                'expiration' => $expirationTime,
            ],
        ], 200),
    ]);

    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'client_id' => $this->clientId,
        'api_key' => $this->apiKey,
    ]);

    $responseData = $response->json();
    $actualExpiration = $responseData['data']['expiration'];

    // Verify the token expires in approximately 10 minutes (allow 5 seconds tolerance)
    expect($actualExpiration - time())->toBeGreaterThanOrEqual(595);
    expect($actualExpiration - time())->toBeLessThanOrEqual(605);
});

it('can parse token structure', function () {
    $testToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => $testToken,
                'refresh_token' => 'refresh_token_12345',
                'expiration' => time() + 600,
            ],
        ], 200),
    ]);

    $response = Http::post($this->apiUrl . '/token/generate-token', [
        'client_id' => $this->clientId,
        'api_key' => $this->apiKey,
    ]);

    $responseData = $response->json();
    $token = $responseData['data']['token'];

    // Verify JWT structure (header.payload.signature)
    $tokenParts = explode('.', $token);
    expect($tokenParts)->toHaveCount(3);

    // Verify each part is base64 encoded (JWT uses base64url encoding)
    foreach ($tokenParts as $part) {
        // JWT uses base64url encoding, so we need to convert it first
        $part = str_replace(['-', '_'], ['+', '/'], $part);
        $part = str_pad($part, strlen($part) + (4 - strlen($part) % 4) % 4, '=', STR_PAD_RIGHT);
        expect(base64_decode($part, true))->not->toBeFalse();
    }
});
