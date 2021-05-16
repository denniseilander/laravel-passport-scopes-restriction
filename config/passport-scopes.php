<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed scopes table column
    |--------------------------------------------------------------------------
    |
    | If you wish to change the default 'allowed_scopes' column, you can change
    | this value.
    |
    */
    'allowed_scopes_column' => 'allowed_scopes',

    /*
    |--------------------------------------------------------------------------
    | Requesting scopes
    |--------------------------------------------------------------------------
    |
    | This option disables the possibility to authorize the token for any scope
    | via the `oauth/token` request. When disabled, all scopes sent with the
    | `oauth/token` request will be ignored and only the scopes defined in the
    | `allowed_scopes` column will be used.
    |
    */
    'enable_requesting_scopes' => true,
];