<?php

use JaxWilko\Profiler\Watcher;
use JaxWilko\Profiler\XDebugHandler;

if (!function_exists('jax_watcher_start')) {
    function jax_watcher_start(): XDebugHandler
    {
        return Watcher::start();
    }
}

if (!function_exists('jax_watcher_end')) {
    function jax_watcher_end(?string $filePath = null): ?XDebugHandler
    {
        return Watcher::end($filePath);
    }
}
