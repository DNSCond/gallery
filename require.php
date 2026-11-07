<?php require_once "{$_SERVER['DOCUMENT_ROOT']}/require/helpers.php";

function fetchThingOrNull(array $current, array $properties): mixed
{
    /** @noinspection DuplicatedCode */
    foreach ($properties as $property) {
        if (!is_array($current)) return null;
        elseif ((null) === ($property)) return null;
        if (!is_string($property)) return null;
        if (array_key_exists($property, $current)) {
            $current = $current[$property];
        } else return null;
    }
    return $current;
}

function array__get_key_as_boolean(string $key, array $array): bool
{
    if (array_key_exists($key, $array)) {
        return (bool)$array[$key];
    } else return false;
}
