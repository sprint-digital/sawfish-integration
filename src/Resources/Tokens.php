<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;
use Carbon\Carbon;

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

    public function ensureValidToken()
    {
        $timeMinusMinute = $this->sawfishIntegration->expires_in ? Carbon::createFromTimestamp($this->sawfishIntegration->expires_in)->subMinute()->timestamp : null;

        if ($timeMinusMinute && $this->sawfishIntegration->access_token && $this->sawfishIntegration->refresh_token && $timeMinusMinute > time()) {
            return [
                'token' => $this->sawfishIntegration->access_token,
                'refresh_token' => $this->sawfishIntegration->refresh_token,
                'expiration' => $this->sawfishIntegration->expires_in,
                'expirationddd' => $this->sawfishIntegration->expires_in,
            ];
        }

        $refreshToken = $this->refreshToken();

        if (isset($refreshToken['status']) && $refreshToken['status'] === 'ERROR') {
            $generateToken = $this->generateToken();

            if (isset($generateToken['status']) && $generateToken['status'] === 'ERROR') {
                $revokeToken = $this->revokeToken();

                if (isset($revokeToken['status']) && $revokeToken['status'] === 'ERROR') {
                    return $revokeToken;
                } else {
                    return $this->generateToken();
                }
            }

            return $generateToken;
        }

        return $refreshToken;
    }


}
