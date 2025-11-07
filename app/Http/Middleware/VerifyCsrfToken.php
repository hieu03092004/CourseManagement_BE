<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'admin/courses',        // Bỏ qua CSRF cho route này
        'admin/courses/*',      // Bỏ qua CSRF cho tất cả các route con của courses
    ];
}
