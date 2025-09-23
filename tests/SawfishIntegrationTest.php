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
    $this->sawfishIntegration = new SawfishIntegration();
});

it('can generate token using sawfish integration class', function () {
    // Mock the HTTP response
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                'refresh_token' => 'refresh_token_12345',
                'expiration' => time() + 600,
            ],
        ], 200),
    ]);

    // Call the method
    $result = $this->sawfishIntegration->generateToken();

    // Assert the result
    expect($result['status'])->toBe('SUCCESS');
    expect($result)->toHaveKey('data');
    expect($result['data'])->toHaveKey('token');
    expect($result['data'])->toHaveKey('refresh_token');
    expect($result['data'])->toHaveKey('expiration');

    // Verify HTTP was called with correct parameters
    Http::assertSent(function ($request) {
        return $request->url() === $this->apiUrl . '/token/generate-token' &&
               $request->method() === 'POST' &&
               $request['client_id'] === 'test-client-id' &&
               $request['api_key'] === 'test-api-key';
    });
});

it('handles token generation error in sawfish integration class', function () {
    // Mock error response
    Http::fake([
        $this->apiUrl . '/token/generate-token' => Http::response([
            'status' => 'ERROR',
            'message' => 'Failed to generate token'
        ], 500),
    ]);

    // Expect exception to be thrown
    expect(function () {
        $this->sawfishIntegration->generateToken();
    })->toThrow(\Exception::class, 'Failed to generate token: {"status":"ERROR","message":"Failed to generate token"}');
});

it('can refresh token using sawfish integration class', function () {
    $refreshToken = 'refresh_token_12345';

    // Mock the HTTP response
    Http::fake([
        $this->apiUrl . '/token/refresh-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.new.token',
                'expiration' => time() + 600,
            ],
        ], 200),
    ]);

    // Call the method
    $result = $this->sawfishIntegration->refreshToken($refreshToken);

    // Assert the result
    expect($result['status'])->toBe('SUCCESS');
    expect($result)->toHaveKey('data');
    expect($result['data'])->toHaveKey('token');
    expect($result['data'])->toHaveKey('expiration');

    // Verify HTTP was called with correct parameters
    Http::assertSent(function ($request) use ($refreshToken) {
        return $request->url() === $this->apiUrl . '/token/refresh-token' &&
               $request->method() === 'POST' &&
               $request['refresh_token'] === $refreshToken;
    });
});

it('handles network exception in sawfish integration class', function () {
    // Mock network exception
    Http::fake([
        $this->apiUrl . '/token/generate-token' => function () {
            throw new \Exception('Connection timeout');
        },
    ]);

    // Expect exception to be thrown
    expect(function () {
        $this->sawfishIntegration->generateToken();
    })->toThrow(\Exception::class, 'Connection timeout');
});

it('uses correct configuration values', function () {
    // Test with different config values
    config([
        'sawfish-integration.api_url' => 'https://api.sawfish.2mm.io/api/v2/accounting',
        'sawfish-integration.client_id' => 'custom-client',
        'sawfish-integration.api_key' => 'custom-key',
    ]);

    $customIntegration = new SawfishIntegration();

    Http::fake([
        'https://api.sawfish.2mm.io/api/v2/accounting/token/generate-token' => Http::response([
            'status' => 'SUCCESS',
            'data' => [
                'token' => 'test-token',
                'refresh_token' => 'test-refresh',
                'expiration' => time() + 600,
            ],
        ], 200),
    ]);

    $result = $customIntegration->generateToken();

    expect($result['status'])->toBe('SUCCESS');

    // Verify HTTP was called with custom configuration
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.sawfish.2mm.io/api/v2/accounting/token/generate-token' &&
               $request['client_id'] === 'custom-client' &&
               $request['api_key'] === 'custom-key';
    });
});
