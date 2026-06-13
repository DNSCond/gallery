<?php use function Helpers\htmlspecialchars12;

function createSelectElement(string $name, array $options, null|string|callable|array $select = null): string
{
    $name = htmlspecialchars12($name);
    $result = array("<select name=\"$name\">");
    foreach ($options as $key => $val) {
        $selected = false;
        if (is_string($select)) {
            $selected = $select === "$key";
        } elseif (is_callable($select)) {
            $selected = !!$select("$key", $val);
        } elseif (is_array($select)) {
            $selected = in_array("$key", $select);
        }
        $key = htmlspecialchars12($key);
        $val = htmlspecialchars12($val);
        $selected = $selected ? 'selected' : '';
        $result[] = "<option $selected value=\"$key\">$val</option>";
    }
    return implode('', $result) . '</select>';
}

