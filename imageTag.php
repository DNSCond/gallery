<?php use function Helpers\htmlspecialchars12;

function imageTag(array $hashes, array $formats, array $classes, $fallback = 'webp'): string|false
{
    if (!count($formats)) return false;
    $result = '<picture>';
    foreach ($formats as $format) {
        if (array_key_exists($format, $hashes)) {
            $size = "width={$hashes[$format]['w']} height={$hashes[$format]['h']}";
            $result .= "<source srcset='imgdata/{$hashes[$format]['hash']}' $size type={$hashes[$format]['t']}>";
        }
    }
    $alt = '';
    $format = $fallback;
    $classes = implode(' ', $classes);
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
