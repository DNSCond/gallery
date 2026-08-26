<?php use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/helpers.php";
$nonce64 = $nonce = '';
try {
    $nonce64 = base64_encode(random_bytes(16));
    $nonce = " style-src 'nonce-$nonce64';";
} catch (\Random\RandomException $e) {
}
header("content-security-policy: default-src 'none'; frame-ancestors " .
        "https://antrequest.nl;$nonce img-src https://antrequest.nl/favicon.ico;");
header('access-control-allow-origin: *');
header('vary: *');
$resultHTTPContext = '';
echo '<!DOCTYPE html>';
$resultHeaders = '';
$allowlist = explode(', ', 'SERVER_PROTOCOL, REQUEST_SCHEME, ' .
        'REMOTE_PORT, QUERY_STRING, REQUEST_METHOD, REQUEST_URI, REMOTE_ADDR');
ksort($_SERVER, SORT_NATURAL | SORT_FLAG_CASE);
foreach ($_SERVER as $key => $val) {
    if (str_starts_with($key, 'HTTP_')) {
        if ($key === 'HTTP_COOKIE') continue;
        $key = substr($key, 5);
        $resultHeaders .= createEntry($key, $val);
    } elseif ($key === 'REQUEST_TIME') {
        $ht = gmdate('D, d M Y H:i:s \\G\\M\\T', +$val);
        $dt = gmdate('Y-m-d\\TH:i:s\\Z', +$val);
        $resultHTTPContext .= "<div><dt>Request-Time<dd><time datetime=$dt>$ht</time></div>";
    } elseif (in_array($key, $allowlist)) {
        $resultHTTPContext .= createEntry($key, $val);
    }
}

function createEntry(string $key, string $val): string
{
    $key = str_replace('_', '-', ucwords(strtolower($key), '_'));
    $htmlKey = htmlspecialchars12($key);
    $htmlVal = htmlspecialchars12($val);
    return "<div><dt>$htmlKey<dd>$htmlVal</div>";
} ?>
<meta charset=UTF-8>
<title>User-Agent Info (ANTRequest.nl)</title>
<meta name=viewport content="width=device-width,initial-scale=1">
<meta name=robots content=noindex>
<!--suppress HtmlUnknownTarget -->
<link rel=icon href=/favicon.ico>
<style nonce="<?= $nonce64 ?>">
    body {
        font-family: monospace;
        background-color: black;
        font-size: 1rem;
        color: lime;
    }

    dl {
        margin: 0;

        div:first-child {
            border-top: none;
        }

        dd, dt {
            display: inline;
        }

        dd {
            margin-left: 0;
        }

        dt:after {
            content: ": ";
        }
    }
</style>
<body>
<h1>User-Agent Info</h1>
<h2>HTTP Context</h2>
<dl><?= $resultHTTPContext ?></dl>
<h2>Request Headers</h2>
<dl><?= $resultHeaders ?></dl>
