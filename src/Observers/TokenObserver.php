<?php

namespace Denniseilander\PassportScopeRestriction\Observers;

use Denniseilander\PassportScopeRestriction\Models\Token;

class TokenObserver
{
    public function creating(Token $token): void
    {
        $allowedScopes = $token->client->getAllowedScopes();
        $scopes = $token->scopes;

        if (config('passport-scopes.enable_requesting_scopes')) {
            $scopes = array_intersect($scopes, $allowedScopes);
        } else {
            $scopes = $allowedScopes;
        }

        if (empty($scopes)) {
            $scopes = array_unique(array_merge($allowedScopes, $scopes), SORT_REGULAR);
        }

        $token->scopes = $scopes;
    }
}
