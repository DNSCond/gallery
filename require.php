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

function parseURL(string $jsonFile): null|array
{
    $json = array();
    if (preg_match(
        '/htignore\\/universe-images\\/([a-zA-Z0-9\\-]+)\\/([a-zA-Z0-9\\-]+)\/main\\.json$/D',
        $jsonFile, $matches)) {
        $json['univId'] = $matches[1];
        $json['charId'] = $matches[2];
        return $json;
    }
    return null;
}
