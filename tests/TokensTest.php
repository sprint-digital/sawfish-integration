<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\Tokens;

beforeEach(function () {
    $this->apiUrl = config('sawfish-integration.api_url');

    // Create a test SawfishIntegration model instance
    $this->sawfishIntegration = SawfishIntegration::factory()->create([
        'client_id' => 'test-client-id',
        'api_key' => 'test-api-key',
        'access_token' => null,
        'refresh_token' => null,
        'expires_in' => null,
    ]);

    $this->clientId = $this->sawfishIntegration->client_id;
    $this->apiKey = $this->sawfishIntegration->api_key;

    $this->tokens = new Tokens();
});

describe('generateToken method', function () {
    it('can generate a new access token successfully', function () {
        $tokenData = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
            'refresh_token' => 'refresh_token_12345',
            'expiration' => time() + 600,
        ];

        // Mock the HTTP response - use wildcard pattern to match the baseUrl approach
        Http::fake([
            $this->apiUrl . '/*' => Http::response($tokenData, 200),
        ]);

        // Call the method
        $result = $this->tokens->generateToken();

        // Assert the result
        expect($result)->toBe($tokenData);
        expect($result)->toHaveKey('token');
        expect($result)->toHaveKey('refresh_token');
        expect($result)->toHaveKey('expiration');

        // Verify HTTP was called with correct parameters
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/token/generate-token') &&
                   $request->method() === 'POST' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-api-key')[0] === $this->apiKey;
        });

        // Verify database was updated
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBe($tokenData['token']);
        expect($this->sawfishIntegration->refresh_token)->toBe($tokenData['refresh_token']);
        expect($this->sawfishIntegration->expires_in)->toBe($tokenData['expiration']);
    });

    it('handles token generation when token is still valid', function () {
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid request'
        ];

        // Mock the HTTP response for existing valid token
        Http::fake([
            $this->apiUrl . '/*' => Http::response($errorResponse, 401),
        ]);

        // Call the method
        $result = $this->tokens->generateToken();

        // Assert the error response - getResponseData wraps error responses
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid request');

        // Verify database was not updated
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBeNull();
        expect($this->sawfishIntegration->expires_in)->toBeNull();
    });

    it('handles invalid credentials error', function () {
        $errorResponse = [
            'message' => 'Invalid credentials'
        ];

        // Mock the HTTP response for invalid credentials
        Http::fake([
            $this->apiUrl . '/*' => Http::response($errorResponse, 401),
        ]);

        // Call the method
        $result = $this->tokens->generateToken();

        // Assert the error response - getResponseData wraps error messages
        expect($result)->toHaveKey('status');
        expect($result)->toHaveKey('message');
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid credentials');

        // Verify database was not updated
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBeNull();
        expect($this->sawfishIntegration->expires_in)->toBeNull();
    });

    it('does not update database when response is missing required fields', function () {
        $incompleteResponse = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
            // Missing refresh_token and expiration
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/*' => Http::response($incompleteResponse, 200),
        ]);

        // Call the method
        $result = $this->tokens->generateToken();

        // Assert the result
        expect($result)->toBe($incompleteResponse);

        // Verify database was not updated due to missing fields
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBeNull();
        expect($this->sawfishIntegration->expires_in)->toBeNull();
    });
});

describe('refreshToken method', function () {
    beforeEach(function () {
        // Set up existing refresh token
        $this->sawfishIntegration->update([
            'refresh_token' => 'existing_refresh_token_12345'
        ]);
    });

    it('handles invalid refresh token error', function () {
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid refresh token'
        ];

        // Mock the HTTP response for invalid refresh token
        Http::fake([
            $this->apiUrl . '/token/refresh-token' => Http::response($errorResponse, 401),
        ]);

        // Call the method
        $result = $this->tokens->refreshToken();

        // Assert the error response
        expect($result)->toBe($errorResponse);
        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Invalid refresh token');

        // Verify database was not updated
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBe('existing_refresh_token_12345');
        expect($this->sawfishIntegration->expires_in)->toBeNull();
    });

    it('handles network exception during token refresh', function () {
        // Mock network exception
        Http::fake([
            $this->apiUrl . '/token/refresh-token' => function () {
                throw new \Exception('Network error');
            },
        ]);

        // Expect exception to be thrown
        expect(function () {
            $this->tokens->refreshToken();
        })->toThrow(\Exception::class, 'Network error');
    });

    it('does not update database when response is missing required fields', function () {
        $incompleteResponse = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.new.token',
            // Missing refresh_token and expiration
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/token/refresh-token' => Http::response($incompleteResponse, 200),
        ]);

        // Call the method
        $result = $this->tokens->refreshToken();

        // Assert the result
        expect($result)->toBe($incompleteResponse);

        // Verify database was not updated due to missing fields
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBe('existing_refresh_token_12345');
        expect($this->sawfishIntegration->expires_in)->toBeNull();
    });

    it('handles case when refresh token is null', function () {
        // Set refresh token to null
        $this->sawfishIntegration->update(['refresh_token' => null]);

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Invalid refresh token'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/token/refresh-token' => Http::response($errorResponse, 401),
        ]);

        // Call the method
        $result = $this->tokens->refreshToken();

        // Assert the error response
        expect($result)->toBe($errorResponse);

        // Verify HTTP was called with null refresh token
        Http::assertSent(function ($request) {
            return $request->url() === $this->apiUrl . '/token/refresh-token' &&
                   $request['refresh_token'] === null;
        });
    });
});

describe('revokeToken method', function () {
    beforeEach(function () {
        // Set up existing tokens
        $this->sawfishIntegration->update([
            'access_token' => 'existing_access_token',
            'refresh_token' => 'existing_refresh_token_12345',
            'expires_in' => time() + 600,
        ]);
    });

    it('handles network exception during token revocation', function () {
        // Mock network exception
        Http::fake([
            $this->apiUrl . '/token/revoke-token' => function () {
                throw new \Exception('Connection failed');
            },
        ]);

        // Expect exception to be thrown
        expect(function () {
            $this->tokens->revokeToken();
        })->toThrow(\Exception::class, 'Connection failed');
    });

    it('does not clear database when response status is not SUCCESS', function () {
        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'Token not found'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/token/revoke-token' => Http::response($errorResponse, 200),
        ]);

        // Call the method
        $result = $this->tokens->revokeToken();

        // Assert the result
        expect($result)->toBe($errorResponse);

        // Verify database was not cleared due to non-SUCCESS status
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBe('existing_access_token');
        expect($this->sawfishIntegration->refresh_token)->toBe('existing_refresh_token_12345');
        expect($this->sawfishIntegration->expires_in)->toBeGreaterThan(time());
    });

    it('handles case when refresh token is null', function () {
        // Set refresh token to null
        $this->sawfishIntegration->update(['refresh_token' => null]);

        $errorResponse = [
            'status' => 'ERROR',
            'message' => 'No refresh token provided'
        ];

        // Mock the HTTP response
        Http::fake([
            $this->apiUrl . '/token/revoke-token' => Http::response($errorResponse, 400),
        ]);

        // Call the method
        $result = $this->tokens->revokeToken();

        // Assert the error response
        expect($result)->toBe($errorResponse);

        // Verify HTTP was called with null refresh token
        Http::assertSent(function ($request) {
            return $request->url() === $this->apiUrl . '/token/revoke-token' &&
                   $request['refresh_token'] === null;
        });
    });
});

describe('Database integration', function () {
    it('properly handles database transactions during token operations', function () {
        $tokenData = [
            'token' => 'transaction_test_token',
            'refresh_token' => 'transaction_test_refresh',
            'expiration' => time() + 600,
        ];

        // Mock successful response
        Http::fake([
            $this->apiUrl . '/token/generate-token' => Http::response($tokenData, 200),
        ]);

        // Verify initial state
        expect($this->sawfishIntegration->access_token)->toBeNull();
        expect($this->sawfishIntegration->refresh_token)->toBeNull();
        expect($this->sawfishIntegration->expires_in)->toBeNull();

        // Call the method
        $result = $this->tokens->generateToken();

        // Verify database was updated
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBe($tokenData['token']);
        expect($this->sawfishIntegration->refresh_token)->toBe($tokenData['refresh_token']);
        expect($this->sawfishIntegration->expires_in)->toBe($tokenData['expiration']);

        // Verify the model was saved
        expect($this->sawfishIntegration->wasChanged())->toBeFalse();
    });

    it('handles multiple rapid token operations', function () {
        $tokenData1 = [
            'token' => 'token_1',
            'refresh_token' => 'refresh_1',
            'expiration' => time() + 600,
        ];

        $tokenData2 = [
            'token' => 'token_2',
            'refresh_token' => 'refresh_2',
            'expiration' => time() + 1200,
        ];

        // Mock responses
        Http::fake([
            $this->apiUrl . '/token/generate-token' => Http::response($tokenData1, 200),
            $this->apiUrl . '/token/refresh-token' => Http::response($tokenData2, 200),
        ]);

        // Set up refresh token
        $this->sawfishIntegration->update(['refresh_token' => 'initial_refresh']);

        // Perform multiple operations
        $this->tokens->generateToken();
        $this->tokens->refreshToken();

        // Verify final state
        $this->sawfishIntegration->refresh();
        expect($this->sawfishIntegration->access_token)->toBe($tokenData2['token']);
        expect($this->sawfishIntegration->refresh_token)->toBe($tokenData2['refresh_token']);
        expect($this->sawfishIntegration->expires_in)->toBe($tokenData2['expiration']);
    });
});
