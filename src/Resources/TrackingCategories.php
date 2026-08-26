<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class TrackingCategories extends SawfishIntegration
{
    /**
     * Method: GET.
     */
    public function getTrackingCategories()
    {
        $response = $this->withTokenHeaders()->get('/tracking-categories');

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     */
    public function getTrackingCategoryByUuid(string $uuid)
    {
        $response = $this->withTokenHeaders()->get('/tracking-categories/' . $uuid);

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function createTrackingCategory(array $data)
    {
        $response = $this->withTokenHeaders()->post('/tracking-categories', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateTrackingCategory(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/tracking-categories/' . $uuid, $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: DELETE.
     */
    public function deleteTrackingCategory(string $uuid)
    {
        $response = $this->withTokenHeaders()->delete('/tracking-categories/' . $uuid);

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function createTrackingCategoryOption(string $trackingCategoryUuid, array $data)
    {
        $response = $this->withTokenHeaders()->post('/tracking-categories/' . $trackingCategoryUuid . '/options', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateTrackingCategoryOption(string $trackingCategoryUuid, string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/tracking-categories/' . $trackingCategoryUuid . '/options/' . $uuid, $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: DELETE.
     */
    public function deleteTrackingCategoryOption(string $trackingCategoryUuid, string $uuid)
    {
        $response = $this->withTokenHeaders()->delete('/tracking-categories/' . $trackingCategoryUuid . '/options/' . $uuid);

        return $this->getResponseData($response);
    }
}
