<?php


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // ログインしているかをチェックするミドルウェア（タスク画面、設定画面等）
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            // ログインしていないかをチェックするミドルウェア（ログイン画面、新規登録画面等）
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            // キャッシュ無効化ミドルウェア
            'no-cache' => \App\Http\Middleware\NoCacheForAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // CSRFトークン不一致（419）時はログイン画面にリダイレクト
        $exceptions->render(fn(TokenMismatchException $e) => redirect()->route('login'));
    })->create();
