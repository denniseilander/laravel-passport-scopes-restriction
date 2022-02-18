<?php

namespace Denniseilander\PassportScopeRestriction\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Client as PassportClient;

/**
 * @property string $name
 * @property Collection<Token> $tokens
 */
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
