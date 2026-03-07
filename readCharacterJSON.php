<?php namespace readCharacterJSON;

use function Helpers\htmlspecialchars12;
use function JWT\generateToken;
use Random\RandomException;

use HTMLSafeEscaped;

global $baseURL;
/** @noinspection PhpIncludeInspection */
require_once "{$baseURL}dataDescriptionList.php";

function readCharacterJSON(string $jsonFile, bool $longDate = false): ?array
{
    // , null|string|callable $_replacer = null
    //$replacer = $replacer ?? fn(string $_key, mixed $val) => htmlspecialchars12($val);
    if (preg_match('/htignore\\/images\\/([a-zA-Z0-9\\-]+)\\/main\\.json$/D', $jsonFile, $matches)) {
        $json = json_decode(file_get_contents($jsonFile) ?? '{}', true);
        $name = htmlspecialchars12($json['name'] ?? $matches[1]);
        $charId = $matches[1];
        $array = array(
            'name' => $name, 'FavicondId' => $json['FavicondId'] ?? '??:??',
            'UniverseId' => $json['UniverseId'] ?? 'Favicond-Unknown',
            'charId' => $charId,
        );
        $registerDate =
        $LastModified =
        $creationDate = INF;
        if (array_key_exists('creationDate', $json)) {
            $creationDate = strtotime("{$json['creationDate']}");
            $array['creationDate'] = toHTMLDatetime($creationDate, $longDate);
            if (array_key_exists('LastModified', $json)) {
                $LastModified = strtotime("{$json['LastModified']}");
                $array['LastModified'] = toHTMLDatetime($LastModified, $longDate);
            } else {
                $LastModified = $creationDate;
                $array['LastModified'] = $array['creationDate'];
            }
            if (array_key_exists('registerDate', $json)) {
                $registerDate = strtotime("{$json['registerDate']}");
                $array['registerDate'] = toHTMLDatetime($registerDate, $longDate);
            } else {
                $registerDate = $creationDate;
                $array['registerDate'] = $array['creationDate'];
            }
        }

        if (array_key_exists('FavicondId', $json)) {
            if (preg_match('/^(\\d{2}):(\\d{2})$/D', $json['FavicondId'], $matches)) {
                $array['listing'] = $matches[1];
                $array['join-Id'] = $matches[2];
            } else {
                $array['listing'] = '??';
                $array['join-Id'] = '??';
            }
        } else {
            $array['listing'] = '??';
            $array['join-Id'] = '??';
        }

        return ['data' => array_merge($array, [
            'creationDate-epoch' => $creationDate,
            'LastModified-epoch' => $LastModified,
            'registerDate-epoch' => $registerDate,
        ]), 'json' => $json];
    }
    return null;
}

function toHTMLDatetime(int $time, bool $longDate): HTMLSafeEscaped
{
    $date = date('D Y-M-d', $time);
    $datetime = gmdate('Y-m-d\\TH:i:s\\Z', $time);
    if ($longDate) {
        return new HTMLSafeEscaped("<relative-time datetime=$datetime><time"
            . " datetime=$datetime>$date</time></relative-time> (<clock-time"
            . " datetime=$datetime format='D M Y-m-d \\TH:i:s \\U\\T\\CO (e)'"
            . " timezone=local><time datetime=$datetime>$date</time></clock-time>)");
    }
    return new HTMLSafeEscaped("<relative-time datetime=$datetime>" .
        "<time datetime=$datetime>$date</time></relative-time>");
}

require_once "JWT.php";
function createJWT(bool $nowatermark = true, bool|string $referermustmatch = false): string
{
    if (array_key_exists('show-watermark', $_GET)) {
        $token = time();
    } else {
        try {
            $array = [
                'nonce' => bin2hex(random_bytes(8)),
                'nowatermark' => $nowatermark,
            ];
            if ($referermustmatch)
                if (is_string($referermustmatch)) {
                    $array['referermustmatch'] = $referermustmatch;
                } else $array['referermustmatch'] = 'antrequest.nl';
            $token = generateToken($array, 30);
        } catch (RandomException) {
            $token = time();
        }
    }
    return "$token";
}
