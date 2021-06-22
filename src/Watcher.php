<?php

namespace JaxWilko\Profiler;

use JaxWilko\Profiler\Parse\Output;
use JaxWilko\Profiler\Parse\Parser;
use JaxWilko\Profiler\XDebugHandler;

class Watcher
{
    const OUTPUT_HTML = 0;
    const OUTPUT_ARR  = 1;

    protected static $mode = self::OUTPUT_HTML;
    protected static $handler = null;
    protected static $enabled = false;

    public static function start(): XDebugHandler
    {
        if (!static::$enabled) {
            static::$enabled = true;
            static::$handler = new XDebugHandler();
            static::$handler->start();
        }

        return static::$handler;
    }

    public static function end(string $filePath = null): ?XDebugHandler
    {
        if (!static::$enabled) {
            return null;
        }

        static::$handler->end();

        static::$enabled = false;

        if ($filePath) {
            static::output($filePath);
        }

        return static::$handler;
    }

    public static function setMode(int $mode)
    {
        if (!in_array($mode, [static::OUTPUT_HTML, static::OUTPUT_ARR])) {
            throw new \InvalidArgumentException('incorrect mode set');
        }

        static::$mode = $mode;
    }

    public static function output(string $filePath = null)
    {
        file_put_contents(__DIR__ . '/../log', static::$handler->getData());

        $result = Parser::make(static::$handler->getData());

        if ($filePath) {
            file_put_contents(
                $filePath,
                (
                    static::$mode === static::OUTPUT_ARR
                        ? print_r($result, true)
                        : Output::make($result)
                )
            );
        }

        return $result;
    }
}
