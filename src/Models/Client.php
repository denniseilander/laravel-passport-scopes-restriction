<?php

namespace Denniseilander\PassportScopeRestriction\Models;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    public function __construct(array $attributes = [])
    {
        $this->mergeCasts([
            (string) config('passport-scopes.allowed_scopes_column') => 'array',
        ]);

        parent::__construct($attributes);
    }

    public function getAllowedScopes(): array
    {
        return $this->{config('passport-scopes.allowed_scopes_column')} ?: [];
    }
}
