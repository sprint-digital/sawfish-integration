<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Tokens extends SawfishIntegration
{

    /**
     * Generate a new access token
     * Method: POST
     *
     * @return array
     */
    public function generateToken()
    {
        $response = $this->withApiKeyHeaders()->post('/token/generate-token');
        $data = $this->getResponseData($response);

        if (isset($data['token']) && isset($data['refresh_token']) && isset($data['expiration'])) {
            $this->sawfishIntegration->access_token = $data['token'];
            $this->sawfishIntegration->refresh_token = $data['refresh_token'];
            $this->sawfishIntegration->expires_in = $data['expiration'];
            $this->sawfishIntegration->save();
        }

        return $data;
    }

    /**
     * Refresh the current access token
     * Method: POST
     *
     * @return array
     */
    public function refreshToken()
    {
        $response = $this->withApiKeyHeaders()->post('/token/refresh-token', [
            'refresh_token' => $this->sawfishIntegration->refresh_token,
        ]);

        $data = $this->getResponseData($response);

        if (isset($data['token']) && isset($data['refresh_token']) && isset($data['expiration'])) {
            $this->sawfishIntegration->access_token = $data['token'];
            $this->sawfishIntegration->refresh_token = $data['refresh_token'];
            $this->sawfishIntegration->expires_in = $data['expiration'];
            $this->sawfishIntegration->save();
        }

        return $data;
    }

    /**
     * Revoke the current token
     * Method: POST
     *
     * @return array
     */
    public function revokeToken()
    {
        $response = $this->withApiKeyHeaders()->post('/token/revoke-token', [
            'refresh_token' => $this->sawfishIntegration->refresh_token,
        ]);

        $data = $this->getResponseData($response);

        if ($data['status'] === 'SUCCESS') {
            $this->sawfishIntegration->refresh_token = null;
            $this->sawfishIntegration->access_token = null;
            $this->sawfishIntegration->expires_in = null;
            $this->sawfishIntegration->save();
        }

        return $data;
    }
}
