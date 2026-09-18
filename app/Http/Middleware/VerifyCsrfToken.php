<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Маршруты, для которых CSRF-проверка не применяется.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',  // 👈 Отключает CSRF для всех API-запросов
        'webhook/*', // 👈 Отключает для вебхуков
    ];
}
