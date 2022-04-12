<?php

namespace JaxWilko\Profiler;

use JaxWilko\Parser;

class XDebugHandler
{
    const XDEBUG_PATH_BLACKLIST = 2;
    const XDEBUG_NAMESPACE_BLACKLIST = 18;

    protected $file = null;

    public function start(): XDebugHandler
    {
        if (!extension_loaded('xdebug')) {
            throw new \RuntimeException('ext-xdebug is not installed');
        }

        ini_set('xdebug.trace_format', 1);

        // filter profiler namespace
        xdebug_set_filter(XDEBUG_FILTER_TRACING, static::XDEBUG_PATH_BLACKLIST, [realpath(__DIR__)]);
        // semi undocumented usage, no file passed allows
        // xdebug to define it's own file
        xdebug_start_trace();

        return $this;
    }

    public function end(): XDebugHandler
    {
        $this->file = xdebug_stop_trace();
        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }
}
