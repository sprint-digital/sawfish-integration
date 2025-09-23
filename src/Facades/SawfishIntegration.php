<?php

namespace SprintDigital\SawfishIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SprintDigital\SawfishIntegration\SawfishIntegration
 */
class SawfishIntegration extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SprintDigital\SawfishIntegration\SawfishIntegration::class;
    }
}
