<?php use function Helpers\htmlspecialchars12;

function imageTag(array $hashes, array $formats, array $classes, ?string $fallback = 'webp'): string|false
{
    if (!count($formats)) return false;
    $formatsImploded = implode(' ', $formats);

    $lastValid = null;
    $result = "<picture>";
    foreach ($formats as $format) {
        if (array_key_exists($format, $hashes)) {
            $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
            $result .= "<source srcset='imgdata/{$hashes[$format]['hash']}' $size type={$hashes[$format]['t']}>";
            $lastValid = $format;
        }
    }
    $alt = '';
    $classes = implode(' ', $classes);
    if ($fallback !== null) $format = $fallback;
    elseif ($lastValid === null) return false;
    else $format = $lastValid;
    if (!array_key_exists($format, $hashes)) return false;
    $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
    $result .= "<img src=\"imgdata/{$hashes[$format]['hash']}\" $size alt="
        . "\"$alt\" class=\"$classes\" fetchpriority=auto loading=lazy>";
    return "$result</picture>";
}

function sha256Bin(string $string): string
{
    return hash('sha256', $string, true);
}

function base64UrlEncode_temporary(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
