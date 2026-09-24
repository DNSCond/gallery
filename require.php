<?php
function readJSON(string $path): mixed
{
    $file = file_get_contents($path);
    if (is_string($file)) return json_decode($file, true);
    return null;
}

function readCharacterJSON(string $jsonFile): null|array
{
    //$json = readJSON($jsonFile);
    //if (preg_match(
    //    '/htignore\\/universe-images\\/[a-zA-Z0-9\\-]+\\/([a-zA-Z0-9\\-]+)\/main\\.json$/D',
    //    $jsonFile, $matches)) {$json['charId'] = $matches[1];return $json;}
    return null;
}

/**
 * @param string $jsonFile
 * @return array|null
 * @deprecated
 */
function parseURL(string $jsonFile): null|array
{
    return null;
}

function isPresent(array $array, array $properties): bool
{
    $current = $array;
    foreach ($properties as $property) {
        if (!is_array($current)) return false;
        if (array_key_exists($property, $current)) {
            $current = $current[$property];
        } else {
            return false;
        }
    }
    return true;
}
