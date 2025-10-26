<?php

namespace SprintDigital\SawfishIntegration\Resources;

use Illuminate\Support\Js;
use SprintDigital\SawfishIntegration\SawfishIntegration;
use SprintDigital\SawfishIntegration\Models\SawfishWebhook as SawfishWebhookModel;

class SawfishWebhook extends SawfishIntegration
{

    public function saveWebhook(array $data)
    {
        if(!isset($data['events']) || count($data['events']) == 0) {
            return [
                'status' => 'ERROR',
                'message' => 'No events found',
            ];
        }

        $isInvoice = false;
        foreach($data['events'] as $event) {

            if($event['event_category'] && $event['event_category'] == 'invoice') {
                $isInvoice = true;
            }

            $sawfishWebhook = SawfishWebhookModel::updateOrCreate([
                'resource_uuid' => $event['resource_uuid'] ?? null,
                'event_category' => $event['event_category'] ?? null,
                'processed_at' => null,
            ], [
                'webhook_data' => json_encode($data) ?? null,
                'total' => $data['total'] ?? null,
                'metadata' => $data['metadata']['event_source'] ?? null,
                'generated_at' => $data['generated_at'] ?? null,
                'resource_uuid' => $event['resource_uuid'] ?? null,
                'event_category' => $event['event_category'] ?? null,
                'processed_at' => null,
                'processed_response' => null,
            ]);
        }

        return [
            'status' => 'SUCCESS',
            'is_invoice' => $isInvoice,
            'message' => 'Webhook saved successfully',
        ];
    }
}
