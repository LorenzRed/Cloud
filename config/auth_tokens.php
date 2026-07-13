<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API access token lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Short-lived bearer token used on each API request.
    |
    */
    'access_ttl_minutes' => (int) env('API_ACCESS_TOKEN_TTL', 15),

    /*
    |--------------------------------------------------------------------------
    | Refresh token lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Long-lived token used to mint a new access token without logging in again.
    | Default: 30 days.
    |
    */
    'refresh_ttl_minutes' => (int) env('API_REFRESH_TOKEN_TTL', 60 * 24 * 30),
];
