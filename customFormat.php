<?php

/**
 * Split a string by Python-style line boundaries, optionally keeping the separators
 */
function splitLinesPythonStyle(string $str, bool $keepEnds = false): array
{
    // Regex matches all Python line boundaries
    $pattern = '/\\r\\n|[\\n\\r]/';

    if ($keepEnds) {
        // Match lines including separators
        $result = [];
        $lastIndex = 0;

        if (preg_match_all($pattern, $str, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $matchIndex = $match[1];
                $matchLength = strlen($match[0]);
                $result[] = substr($str, $lastIndex, $matchIndex - $lastIndex + $matchLength);
                $lastIndex = $matchIndex + $matchLength;
            }
        }

        if ($lastIndex < strlen($str)) {
            $result[] = substr($str, $lastIndex);
        }

        return $result;
    } else {
        // Split and discard separators
        return preg_split($pattern, $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}

/**
 * Normalize newlines by splitting and rejoining with \n
 */
function normalizeNewlines(string $of): string
{
    return implode("\n", splitLinesPythonStyle($of));
}

/**
 * Chunk an array at elements matching a predicate function
 */
function chunkArrayAt(array $array, callable $fn, bool $skipAtSplit = false): array
{
    $result = [[]];
    $current = &$result[0];
    $index = 0;

    foreach ($array as $element) {
        if ($fn($element, $index++, $array)) {
            $result[] = [];
            $current = &$result[count($result) - 1];
            if ($skipAtSplit) continue;
        }
        $current[] = $element;
    }

    return $result;
}

/**
 * Parse named blocks from a string with configurable duplicate handling
 *
 * @param string $string The input string to parse
 * @param array $options Configuration options:
 *   - duplicateDisambiguation: 'last' (default), 'error', 'array', or 'concat'
 *   - concatSeperator: Separator for concat mode (default: '\n\n')
 *
 * @return array The parsed blocks as associative array
 * @throws Exception When duplicateDisambiguation is 'error' and duplicate key found
 */
function parseNamedBlocks(string $string, array $options = []): array
{
    $duplicateDisambiguation = $options['duplicateDisambiguation'] ?? 'last';
    $concatSeperator = (string)($options['concatSeperator'] ?? "\n\n");
    $result = array();

    $apply = function (string $key, string $stringValue) use (&$result, $duplicateDisambiguation, $concatSeperator) {
        if (trim($stringValue) === '') return;

        if ($duplicateDisambiguation === 'error') {
            if (array_key_exists($key, $result)) {
                throw new Exception("key \"$key\" appears more than once");
            }
        } elseif ($duplicateDisambiguation === 'array') {
            if (array_key_exists($key, $result)) {
                $result[$key][] = $stringValue;
            } else {
                $result[$key] = [$stringValue];
            }
            return;
        } elseif ($duplicateDisambiguation === 'concat') {
            if (array_key_exists($key, $result)) {
                $result[$key] .= $concatSeperator . $stringValue;
            } else {
                $result[$key] = $stringValue;
            }
            return;
        }

        $result[$key] = $stringValue;
    };

    $chunks = chunkArrayAt(
        splitLinesPythonStyle($string),
        fn($str) => $str === '---', true
    );

    foreach ($chunks as $element) {
        if (count($element) === 0) continue;

        if (preg_match('/^name: ?(.+)/i', $element[0], $matches)) {
            $key = trim($matches[1]);
            $apply($key, implode("\n", array_slice($element, 1)));
        } else {
            $apply('', implode("\n", $element));
        }
    }

    return $result;
}

/**
 * Stringify a named blocks array back into the original format
 */
function stringify(array $object): string
{
    $array = [];

    foreach ($object as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $v) {
                $array[] = "name: $key\n" . normalizeNewlines($v);
            }
        } else {
            $array[] = "name: $key\n" . normalizeNewlines($val);
        }
    }

    return implode("\n---\n", $array);
}