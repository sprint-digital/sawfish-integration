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
        'api_key',
        'webhook_key',
        'expires_in',
        'access_token',
        'refresh_token',
        'sawfish_account_uuid',
    ];
}
