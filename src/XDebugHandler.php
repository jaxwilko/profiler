<?php

namespace JaxWilko\Profiler;

use JaxWilko\Parser;

class XDebugHandler
{
    const XDEBUG_PATH_BLACKLIST = 2;
    const XDEBUG_NAMESPACE_BLACKLIST = 18;

    protected $data = null;

    public function start(): XDebugHandler
    {
        if (!extension_loaded('xdebug')) {
            throw new \RuntimeException('ext-xdebug is not installed');
        }

        ini_set('xdebug.trace_format', 1);
//        ini_set('xdebug.collect_return', true);
//        ini_set('xdebug.collect_assignments', true);

        // filter profiler namespace
        xdebug_set_filter(XDEBUG_FILTER_TRACING, static::XDEBUG_PATH_BLACKLIST, [realpath(__DIR__)]);
        // semi undocumented usage, no file passed allows
        // xdebug to define it's own file
        xdebug_start_trace();

        return $this;
    }

    public function end(): XDebugHandler
    {
        $file = xdebug_stop_trace();
        $this->data = file_get_contents($file);
        // cleanup file
        unlink($file);

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }
}