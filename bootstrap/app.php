<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // usei para debugar o horario vindo do .env
        \Log::info('IMPORT_SCHEDULE_TIME: ' . env('IMPORT_SCHEDULE_TIME', '03:00'));

        $importTime = trim(env('IMPORT_SCHEDULE_TIME', '03:00'));
        \Log::info('IMPORT_SCHEDULE_TIME agendamento: ' . $importTime);
        $schedule->command('app:import-products')->dailyAt($importTime);

        // usei isso para teste rapido a cada 1 minuto.
    //    $schedule->command('app:import-products')->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
