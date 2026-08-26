<?php

namespace SprintDigital\SawfishIntegration\Resources;

use SprintDigital\SawfishIntegration\SawfishIntegration;

class Invoices extends SawfishIntegration
{

    /**
     * Get invoices from Sawfish API
     * /invoices?uuids={{ string_split_by_commas }}
     * @return array
     */
    public function getInvoices($uuids = null, $perPage = 200, $page = 1)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/invoices?' . http_build_query([
            'uuids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     */
    public function getInvoiceByUuid($uuids)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/invoices?' . http_build_query([
            'uuids' => $data,
        ]));

        return $this->getResponseData($response);
    }

    public function getInvoicesByProviderUuids($uuids = null, $perPage = 200, $page = 1)
    {
        if ($uuids && !is_string($uuids) && !is_array($uuids)) {
            throw new \InvalidArgumentException('The $uuids parameter must be a string or an array.');
        }

        $data = $uuids;
        if (is_array($uuids)) {
            $data = implode(',', $uuids);
        }

        $response = $this->withTokenHeaders()->get('/invoices?' . http_build_query([
            'accounting_provider_ids' => $data,
            'per_page' => $perPage,
            'page' => $page,
        ]));

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function createInvoice(array $data)
    {
        $response = $this->withTokenHeaders()->post('/invoices', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: PUT.
     */
    public function updateInvoice(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->put('/invoices/' . $uuid, $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function voidInvoice(string $uuid)
    {
        $response = $this->withTokenHeaders()->post('/invoices/' . $uuid . '/void');

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function sendInvoice(string $uuid, array $data = [])
    {
        $response = $this->withTokenHeaders()->post('/invoices/' . $uuid . '/send', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: GET.
     */
    public function getPdfInvoiceLink(string $uuid)
    {
        $response = $this->withTokenHeaders()->get('/invoices/' . $uuid . '/pdf');

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function addInvoiceAttachments(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->post('/invoices/' . $uuid . '/attachments', $data);

        return $this->getResponseData($response);
    }

    /**
     * Method: POST (multipart).
     *
     * @param  array<int, array{name: string, contents: string, content_type?: string}>  $files
     */
    public function addInvoiceAttachmentsFromFiles(string $uuid, array $files)
    {
        $request = $this->withTokenHeaders();

        foreach ($files as $file) {
            $headers = [];
            if (!empty($file['content_type'])) {
                $headers['Content-Type'] = $file['content_type'];
            }

            $request = $request->attach(
                'media[]',
                $file['contents'],
                $file['name'],
                $headers
            );
        }

        $response = $request->post('/invoices/' . $uuid . '/attachments');

        return $this->getResponseData($response);
    }

    /**
     * Method: DELETE.
     */
    public function deleteInvoiceAttachments(string $uuid, string $attachmentId)
    {
        $response = $this->withTokenHeaders()->delete('/invoices/' . $uuid . '/attachments/' . $attachmentId);

        return $this->getResponseData($response);
    }

    /**
     * Method: POST.
     */
    public function manualInvoicePayment(string $uuid, array $data)
    {
        $response = $this->withTokenHeaders()->post('/invoices/' . $uuid . '/manual-payment', $data);

        return $this->getResponseData($response);
    }

}
