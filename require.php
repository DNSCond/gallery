<?php // readJSON
function readJSON(string $path): mixed
{
    $file = file_get_contents($path);
    if (is_string($file)) return json_decode($file, true);
    return null;
}

function isPresent(array $current, array $properties): bool
{
    foreach ($properties as $property) {
        if (!is_array($current)) return false;
        elseif ((null) === ($property)) return false;
        if (!is_string($property)) return false;
        if (array_key_exists($property, $current)) {
            $current = $current[$property];
        } else return false;
    }
    return true;
}

function array__get_key_as_boolean(string $key, array $array): bool
{
    if (array_key_exists($key, $array)) {
        return (bool)$array[$key];
    } else return false;
}
