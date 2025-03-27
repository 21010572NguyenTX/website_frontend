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
        // Thêm các routes admin vào danh sách loại trừ
        'admin/medicines/add',
        'admin/medicines/update',
        'admin/medicines/delete',
        'admin/medicines/import',
        'admin/diseases/add',
        'admin/diseases/update',
        'admin/diseases/delete',
        'admin/diseases/import',
        // Thêm các routes có tiền tố khác
        'admin/*',
        'api/*'
    ];
}
