<?php

namespace JaxWilko\Profiler\Parse;

use DOMDocument;
use DOMElement;

class Parser
{
    const TABLE_ROW_NODE = 'tr';
    const TABLE_DATA_NODE = 'td';

    public static function make(string $file): array
    {
        $parser = new static();
        return $parser->parseFile($file);
    }

    public function parseFile(string $file): array
    {
        $fp = fopen($file, 'r');
        $data = [];

        while (!feof($fp) && $line = fgetcsv($fp, 0, "\t")) {
            if (
                (!isset($line[0]) || !is_numeric($line[0]))
                || (empty($data) && $line[2] !== '0')
                || (isset($line[2]) && $line[2] === 'A')
            ) {
                continue;
            }

            if (isset($data[$line[1]]) && isset($line[2])) {
                switch ($line[2]) {
                    case '1':
                        $data[$line[1]]['endTime'] = $line[3];
                        $data[$line[1]]['endMemory'] = $line[4];
                        break;
                    case 'R':
                        $data[$line[1]]['return'] = $line[5];
                        break;
                }
                continue;
            }

            $data[$line[1]] = [
                'id'            => $line[1],
                'depth'         => (int) $line[0],
                'startTime'     => $line[3],
                'endTime'       => null,
                'startMemory'   => $line[4],
                'endMemory'     => null,
                'function'      => $line[5],
                'file'          => $line[8],
                'line'          => $line[9],
                'args'          => $this->getLineArgs($line),
                'return'        => null,
                'children'      => []
            ];
        }

        $process = ['children' => []];
        $thread = &$process['children'];
        $scopes = [];

        $previousDepth = 0;

        foreach ($data as $id => $function) {
            if ($function['depth'] === $previousDepth) {
                $thread[] = $function;
            }

            if ($function['depth'] > $previousDepth) {
                $scopes[] = &$thread;
                if (!empty($thread)) {
                    $thread = &$thread[count($thread) - 1]['children'];
                }
                $thread[] = $function;
            }

            if ($function['depth'] < $previousDepth) {
                for ($i = $previousDepth - $function['depth']; $i !== 0; $i--) {
                    $thread = &$scopes[count($scopes) - 1];
                    array_pop($scopes);
                }

                $thread[] = $function;
            }

            $previousDepth = $function['depth'];
        }

        return $process['children'];
    }

    protected function getLineArgs(array $line): string
    {
        if (!isset($line[10]) || $line[10] === 0) {
            return '[]';
        }

        return '[' . implode(', ', array_slice($line, 11, $line[10])) . ']';
    }
}
