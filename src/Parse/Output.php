<?php

namespace JaxWilko\Profiler\Parse;

class Output
{
    public static function make(array $profile)
    {
        $output = new static();
        return $output->create($profile);
    }

    public function create(array $profile)
    {
        $content = file_get_contents(__DIR__ . '/html.stub');
        return str_replace(['%%data%%'], [json_encode($profile)], $content);
    }
}