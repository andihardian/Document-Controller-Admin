<?php

use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\UpdateLastLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Tambahkan ke grup 'web' — berlaku untuk semua request web
        $middleware->appendToGroup('web', [
            CheckUserActive::class,
            UpdateLastLogin::class,
        ]);

        // Alias untuk dipakai di route (role: dari Spatie sudah otomatis lewat ServiceProvider)
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Tampilan error yang ramah untuk 403 dan 404
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            $status = $e->getStatusCode();

            if (in_array($status, [403, 404])) {
                return response()->view("errors.{$status}", [
                    'message' => $e->getMessage() ?: ($status === 403 ? 'Anda tidak memiliki akses ke halaman ini.' : 'Halaman tidak ditemukan.'),
                ], $status);
            }
        });

    })->create();
