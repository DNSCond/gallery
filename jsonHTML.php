<?php
function mkHTMLFromJSON(mixed $desc, bool $htmlEscape = false): string
{
    $result = "<$desc[0]";
    foreach ($desc[1] as $name => $value) {
        $result .= "\x20$name=\"$value\"";
    }
    $result .= ">";
    if (in_array(strtolower($desc[0]), ['br', 'hr', 'source', 'img', 'wbr'])) {
        return $result;
    }
    foreach ($desc as $pos => $val) {
        if ($pos == 0 || $pos == 1) continue;
        if (is_string($val)) {
            if ($desc[0] !== 'script' && $htmlEscape)
                $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
            $result .= $val;
        } else {
            $result .= mkHTMLFromJSON($val, $htmlEscape);
        }
    }
    return "$result</$desc[0]>";
}
