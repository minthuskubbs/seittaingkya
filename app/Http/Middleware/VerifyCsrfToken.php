<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Logout is low CSRF risk (worst case: a forced sign-out). Exempting it
        // avoids a 419 "page expired" when the session/token has gone stale.
        'logout',
    ];
}
