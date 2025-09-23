<?php

namespace SprintDigital\SawfishIntegration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SawfishIntegration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sawfish_integrations';

    protected $fillable = [
        'client_id',
        'webhook_key',
        'api_key',
        'api_token',
        'expires_in',
        'account_uuid',
    ];
}
