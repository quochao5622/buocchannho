<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Livewire\Exceptions\TooManyCallsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(fn () => route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log which component/methods overflowed Livewire's payload.max_calls guard
        $exceptions->report(function (TooManyCallsException $e): void {
            $components = collect(request()->input('components', []))
                ->map(fn ($component) => [
                    'name' => data_get(json_decode($component['snapshot'] ?? '', true), 'memo.name'),
                    'calls' => collect($component['calls'] ?? [])
                        ->countBy(fn ($call) => ($call['method'] ?? '?')
                            .(is_string($call['params'][0] ?? null) ? '('.$call['params'][0].')' : ''))
                        ->all(),
                ])
                ->filter(fn ($component) => $component['calls'] !== [])
                ->values()
                ->all();

            Log::warning('Livewire request exceeded payload.max_calls', [
                'user_id' => auth()->id(),
                'referer' => request()->headers->get('referer'),
                'user_agent' => request()->userAgent(),
                'components' => $components,
            ]);
        });
    })->create();
