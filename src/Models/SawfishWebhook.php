<?php

namespace SprintDigital\SawfishIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SawfishWebhook extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sawfish_webhooks';

    protected $fillable = [
        'total',
        'metadata',
        'generated_at',
        'webhook_data',
        'resource_uuid',
        'event_category',
        'processed_at',
        'processed_response',
    ];
}
