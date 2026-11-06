<?php use function Helpers\htmlspecialchars12;

function imageTag(?string $characterName, array $hashes, array $formats, string $alt,
                  array   $classes, ?string $fallback = 'webp', bool $highPriority = false): string|false
{
    $lastValid = null;
    if (!count($formats)) return false;
    $cname = is_string($characterName) ? "$characterName--" : '';
    $fn = htmlspecialchars12("$cname{$hashes['data-name']}");
    $result = "<picture data-file-name='$fn'>";
    foreach ($formats as $format) {
        if (array_key_exists($format, $hashes)) {
            $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
            $result .= "<source srcset='imgdata/{$hashes[$format]['hash']}' $size type={$hashes[$format]['t']}>";
            $lastValid = $format;
        }
    }
    $alt = htmlspecialchars12($alt);
    $classes = implode(' ', $classes);
    if ($fallback !== null) $format = $fallback;
    elseif ($lastValid === null) return false;
    else $format = $lastValid;
    if (!array_key_exists($format, $hashes)) return false;
    $fetchpriority = $highPriority ? 'fetchpriority=high' : 'fetchpriority=auto loading=lazy';
    $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
    $result .= "<img src=\"imgdata/{$hashes[$format]['hash']}\" $size alt=\"$alt\""
        . " class=\"$classes\" data-type='{$hashes[$format]['t']}' $fetchpriority>";
    return "$result</picture>";
}
