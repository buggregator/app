<?php

use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunnerLaravel\Defaults;
use Spiral\RoadRunnerLaravel\Events;
use Spiral\RoadRunnerLaravel\Listeners;

return [
    'rpc' => [
        'host' => env('RPC_HOST', 'tcp://127.0.0.1:6001'),
    ],

    'session' => [
        'storage' => 'session',
    ],

    'cache' => [
        'storage' => 'cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS Schema Usage
    |--------------------------------------------------------------------------
    |
    | Set this value to `true` if your application uses HTTPS (required for
    | correct links generation, for example).
    |
    */

    'force_https' => (bool) env('APP_FORCE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    |
    | Worker provided by this package allows to interacts with request
    | processing loop using application events.
    |
    | Feel free to add your own event listeners.
    |
    */

    'listeners' => [
        Events\BeforeLoopStartedEvent::class => [
            ...Defaults::beforeLoopStarted(),
            \App\Listeners\RoadRunner\RegisterConsoleStreamHandlerListener::class,
            // Listeners\SetupTelescopeListener::class, // for <https://github.com/laravel/telescope>
        ],

        Events\BeforeLoopIterationEvent::class => [
            ...Defaults::beforeLoopIteration(),
            // Listeners\ResetLaravelScoutListener::class,     // for <https://github.com/laravel/scout>
            // Listeners\ResetLaravelSocialiteListener::class, // for <https://github.com/laravel/socialite>
            Listeners\ResetInertiaListener::class,          // for <https://github.com/inertiajs/inertia-laravel>
            Listeners\ResetZiggyListener::class,            // for <https://github.com/tighten/ziggy>
        ],

        \Infrastructure\RoadRunner\TCP\Events\BeforeLoopIterationEvent::class => [
            ...Defaults::beforeLoopIteration(),
        ],

        Events\BeforeRequestHandlingEvent::class => [
            ...Defaults::beforeRequestHandling(),
            Listeners\InjectStatsIntoRequestListener::class,
        ],

        Events\AfterRequestHandlingEvent::class => [
            ...Defaults::afterRequestHandling(),
            \App\Listeners\Request\SendRequestDebugToConsole::class,
        ],

        Events\AfterLoopIterationEvent::class => [
            ...Defaults::afterLoopIteration(),
            \Infrastructure\CycleOrm\Listeners\ClearIdentityMap::class,
            Listeners\RunGarbageCollectorListener::class, // keep the memory usage low
        ],

        \Infrastructure\RoadRunner\TCP\Events\AfterLoopIterationEvent::class => [
            ...Defaults::afterLoopIteration(),
            \Infrastructure\CycleOrm\Listeners\ClearIdentityMap::class,
            Listeners\RunGarbageCollectorListener::class, // keep the memory usage low
        ],

        Events\AfterLoopStoppedEvent::class => [
            ...Defaults::afterLoopStopped(),
        ],

        Events\LoopErrorOccurredEvent::class => [
            ...Defaults::loopErrorOccurred(),
            Listeners\SendExceptionToStderrListener::class,
            Listeners\StopWorkerListener::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Containers Pre Resolving / Clearing
    |--------------------------------------------------------------------------
    |
    | The bindings listed below will be resolved before the events loop
    | starting. Clearing a binding will force the container to resolve that
    | binding again when asked.
    |
    | Feel free to add your own bindings here.
    |
    */

    'warm' => [
        ...Defaults::servicesToWarm(),
    ],

    'clear' => [
        ...Defaults::servicesToClear(),
        'auth', // is not required for Laravel >= v8.35
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Providers
    |--------------------------------------------------------------------------
    |
    | Providers that will be registered on every request.
    |
    | Feel free to add your service-providers here.
    |
    */

    'reset_providers' => [
        ...Defaults::providersToReset(),
        Illuminate\Auth\AuthServiceProvider::class,             // is not required for Laravel >= v8.35
        Illuminate\Pagination\PaginationServiceProvider::class, // is not required for Laravel >= v8.35
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Classes
    |--------------------------------------------------------------------------
    |
    | Here you can override the worker class for processing different kinds of
    | jobs, that received from the RoadRunner daemon.
    |
    */

    'workers' => [
        Mode::MODE_HTTP => \Spiral\RoadRunnerLaravel\Worker::class,
        Mode::MODE_JOBS => \Infrastructure\RoadRunner\Queue\Worker::class,
        'tcp' => \Infrastructure\RoadRunner\TCP\Worker::class,
        // Mode::MODE_TEMPORAL => ...,
    ],
];
