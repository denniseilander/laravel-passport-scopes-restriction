<?php

namespace Denniseilander\PassportScopeRestriction\Models;

use Laravel\Passport\Token as PassportToken;

/**
 * @property Client $client
 * @property array<string> $scopes
 */
class Token extends PassportToken
{
}
