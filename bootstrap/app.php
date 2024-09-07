<?php

use App\Console\Commands\UpdateReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append([
        //     'access' => \App\Http\Middleware\PermissionAccess::class
        // ]);

        //
        // $middleware->use([
        //     'access' => \App\Http\Middleware\PermissionAccess::class
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('reminders:update')->everyMinute();
        $schedule->command('mail:get')->everyMinute(); //->everyFiveMinutes();
        //$schedule->command('mail:send')->everyMinute(); //->everyFiveMinutes();
    })->withBroadcasting(
        __DIR__.'/../routes/channels.php',
    )
    ->create();
