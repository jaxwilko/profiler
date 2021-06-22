<?php

namespace JaxWilko\Profiler\Parse;

use DOMDocument;
use DOMElement;

class Parser
{
    const TABLE_ROW_NODE = 'tr';
    const TABLE_DATA_NODE = 'td';

    public static function make(string $src)
    {
        $parser = new static();
        return $parser->parseString($src);
    }

    public function parseString(string $src): array
    {
        $lines = array_filter(explode(PHP_EOL, $src));
        // clean input
        unset($lines[count($lines) - 1], $lines[0], $lines[1], $lines[2]);
        // map strings to arrays
        $lines = array_values(array_map(function ($line) {
            return str_getcsv($line, "\t");
        }, $lines));
        // clean values before tracking started
        while (count($lines[0]) < 6) {
            unset($lines[0]);
            $lines = array_values($lines);
        }

        $data = [];

        foreach ($lines as $line) {
            if (isset($line[2]) && $line[2] !== '0') {
                if (!isset($data[$line[1]])) {
                    continue;
                }
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
                'id' => $line[1],
                'depth' => ((int) $line[0]) - 2,
                'startTime' => $line[3],
                'startMemory' => $line[4],
                'function' => $line[5],
                'file' => $line[8],
                'line' => $line[9],
                'args' => $this->getLineArgs($line),
            ];
        }

        $process = [];
        $thread = &$process;

        $previousDepth = 0;

        foreach ($data as $id => $line) {

            $function = $line;

            if ($function['depth'] === $previousDepth) {
                $thread['children'][] = $function;
            }

            if ($function['depth'] > $previousDepth) {
                $thread = &$thread['children'][count($thread['children']) - 1];
                $thread['children'][] = $function;
            }

            if ($function['depth'] < $previousDepth) {
                $thread = &$process;
                for ($i = 0; $i < $function['depth']; $i++) {
                    $thread = &$thread['children'][count($thread['children']) - 1];
                }
            }

            $previousDepth = $function['depth'];
        }

        print_r($process['children'][0]);

        return $process['children'][0];
    }

    public function parseStringHtml(string $src): array
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($src);

        $doc = $doc->childNodes->item(1) // html
        ->childNodes->item(0) // body
        ->childNodes->item(0); // table

        $process = [];
        $thread = &$process;

        $previousHierarchy = 0;

        foreach ($doc->childNodes as $tr) {
            if (
                $tr->nodeName !== static::TABLE_ROW_NODE
                || $tr->childNodes->item(0)->nodeName !== static::TABLE_DATA_NODE
            ) {
                continue;
            }

            $hierarchy = $this->getFunctionHierarchy($tr->childNodes->item(3)->nodeValue);

            $function = [
                'id' => $tr->childNodes->item(0)->nodeValue,
                'time' => $tr->childNodes->item(1)->nodeValue,
                'memory' => $tr->childNodes->item(2)->nodeValue,
                'function' => $tr->childNodes->item(4)->nodeValue,
                'location' => $tr->childNodes->item(5)->nodeValue,
                'hierarchy' => $hierarchy
            ];

            if ($hierarchy === $previousHierarchy) {
                $thread['children'][] = $function;
            }

            if ($hierarchy > $previousHierarchy) {
                $thread = &$thread['children'][count($thread['children']) - 1];
                $thread['children'][] = $function;
            }

            if ($hierarchy < $previousHierarchy) {
                $thread = &$process;
                for ($i = 0; $i < $hierarchy; $i++) {
                    $thread = &$thread['children'][count($thread['children']) - 1];
                }
            }

            $previousHierarchy = $hierarchy;
        }

        return $process['children'][0];
    }

    protected function getLineArgs(array $line): string
    {
        if (!isset($line[10]) || $line[10] === 0) {
            return '[]';
        }

        return '[' . implode(', ', array_slice($line, 11, $line[10])) . ']';
    }

    protected function getFunctionHierarchy(string $str): int
    {
        return substr_count($str, ' ') - 1;
    }

    protected function getHtml(DOMElement $element)
    {
        $doc = new DOMDocument();
        $cloned = $element->cloneNode(true);
        $doc->appendChild($doc->importNode($cloned, true));
        return $doc->saveHTML();
    }
}