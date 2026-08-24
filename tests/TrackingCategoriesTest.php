<?php

use Illuminate\Support\Facades\Http;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use SprintDigital\SawfishIntegration\Resources\TrackingCategories;

beforeEach(function () {
    $this->apiUrl = config('sawfish-integration.api_url');

    $this->sawfishIntegration = SawfishIntegration::factory()->create([
        'client_id' => 'test-client-id',
        'api_key' => 'test-api-key',
        'access_token' => 'test-access-token',
        'refresh_token' => 'test-refresh-token',
        'expires_in' => time() + 3600,
    ]);

    $this->clientId = $this->sawfishIntegration->client_id;

    $this->trackingCategories = new TrackingCategories();
});

describe('getTrackingCategories method', function () {
    it('can get tracking categories', function () {
        $data = ['tracking_categories' => [['uuid' => 'tc-uuid-1', 'name' => 'Region']]];

        Http::fake([
            $this->apiUrl . '/tracking-categories' => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->getTrackingCategories();

        expect($result)->toBe($data);

        Http::assertSent(function ($request) {
            return $request->url() === $this->apiUrl . '/tracking-categories' &&
                   $request->method() === 'GET' &&
                   $request->header('x-client-id')[0] === $this->clientId &&
                   $request->header('x-jwt-token')[0] === 'test-access-token';
        });
    });

    it('handles API error response', function () {
        Http::fake([
            $this->apiUrl . '/tracking-categories' => Http::response(['status' => 'ERROR', 'message' => 'Access denied'], 403),
        ]);

        $result = $this->trackingCategories->getTrackingCategories();

        expect($result['status'])->toBe('ERROR');
        expect($result['message'])->toBe('Access denied');
    });
});

describe('getTrackingCategoryByUuid method', function () {
    it('can get a tracking category by uuid', function () {
        $uuid = 'tc-uuid-1';
        $data = ['uuid' => $uuid, 'name' => 'Region', 'options' => []];

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $uuid => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->getTrackingCategoryByUuid($uuid);

        expect($result)->toBe($data);

        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $uuid &&
                   $request->method() === 'GET';
        });
    });
});

describe('createTrackingCategory method', function () {
    it('can create a tracking category', function () {
        $payload = ['name' => 'Region'];
        $data = ['uuid' => 'new-tc-uuid', 'name' => 'Region'];

        Http::fake([
            $this->apiUrl . '/tracking-categories' => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->createTrackingCategory($payload);

        expect($result)->toBe($data);

        Http::assertSent(function ($request) use ($payload) {
            return $request->url() === $this->apiUrl . '/tracking-categories' &&
                   $request->method() === 'POST' &&
                   $request->data() === $payload;
        });
    });
});

describe('updateTrackingCategory method', function () {
    it('can update a tracking category', function () {
        $uuid = 'tc-uuid-1';
        $payload = ['name' => 'Updated Region'];
        $data = ['uuid' => $uuid, 'name' => 'Updated Region'];

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $uuid => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->updateTrackingCategory($uuid, $payload);

        expect($result)->toBe($data);

        Http::assertSent(function ($request) use ($uuid, $payload) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $uuid &&
                   $request->method() === 'PUT' &&
                   $request->data() === $payload;
        });
    });
});

describe('deleteTrackingCategory method', function () {
    it('can delete a tracking category', function () {
        $uuid = 'tc-uuid-1';

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $uuid => Http::response(['message' => 'Tracking category deleted successfully'], 200),
        ]);

        $result = $this->trackingCategories->deleteTrackingCategory($uuid);

        expect($result['message'])->toBe('Tracking category deleted successfully');

        Http::assertSent(function ($request) use ($uuid) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $uuid &&
                   $request->method() === 'DELETE';
        });
    });
});

describe('createTrackingCategoryOption method', function () {
    it('can create a tracking category option', function () {
        $trackingCategoryUuid = 'tc-uuid-1';
        $payload = ['name' => 'North'];
        $data = ['uuid' => 'option-uuid-1', 'name' => 'North'];

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options' => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->createTrackingCategoryOption($trackingCategoryUuid, $payload);

        expect($result)->toBe($data);

        Http::assertSent(function ($request) use ($trackingCategoryUuid, $payload) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options' &&
                   $request->method() === 'POST' &&
                   $request->data() === $payload;
        });
    });
});

describe('updateTrackingCategoryOption method', function () {
    it('can update a tracking category option', function () {
        $trackingCategoryUuid = 'tc-uuid-1';
        $optionUuid = 'option-uuid-1';
        $payload = ['name' => 'North Updated'];
        $data = ['uuid' => $optionUuid, 'name' => 'North Updated'];

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options/' . $optionUuid => Http::response($data, 200),
        ]);

        $result = $this->trackingCategories->updateTrackingCategoryOption($trackingCategoryUuid, $optionUuid, $payload);

        expect($result)->toBe($data);

        Http::assertSent(function ($request) use ($trackingCategoryUuid, $optionUuid, $payload) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options/' . $optionUuid &&
                   $request->method() === 'PUT' &&
                   $request->data() === $payload;
        });
    });
});

describe('deleteTrackingCategoryOption method', function () {
    it('can delete a tracking category option', function () {
        $trackingCategoryUuid = 'tc-uuid-1';
        $optionUuid = 'option-uuid-1';

        Http::fake([
            $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options/' . $optionUuid => Http::response(['message' => 'Tracking category option deleted successfully'], 200),
        ]);

        $result = $this->trackingCategories->deleteTrackingCategoryOption($trackingCategoryUuid, $optionUuid);

        expect($result['message'])->toBe('Tracking category option deleted successfully');

        Http::assertSent(function ($request) use ($trackingCategoryUuid, $optionUuid) {
            return $request->url() === $this->apiUrl . '/tracking-categories/' . $trackingCategoryUuid . '/options/' . $optionUuid &&
                   $request->method() === 'DELETE';
        });
    });
});
