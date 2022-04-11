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
        return str_replace('%%data%%', json_encode($this->utf8Encode($profile)), $content);
    }

    public function utf8Encode(mixed $data): mixed
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        if (is_array($data)) {
            $return = [];
            foreach ($data as $index => $value) {
                $return[$index] = $this->utf8Encode($value);
            }

            return $return;
        }

        if (is_object($data)) {
            foreach ((array) $data as $index => $value) {
                $data->{$index} = $this->utf8Encode($value);
            }

            return $data;
        }

        return $data;
    }
}
