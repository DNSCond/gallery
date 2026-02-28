<?php use JSONWT\JWT;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/JSONWT.php";
$JWT = new JWT('d475d715859c8ff96a898e175a86f8304be4c96e85a7df9e77f4a715c4825ef3b0ad1e5c8d0e9e5af95aab3546e4876c81e6a2693185b1c948760690bffea1634f0476');

function set_cookie(string $name, ?string $value, array $options, bool $send = true): bool|string
{
    $name = urlencode($name);
    $value = is_string($value) ? urlencode($value) : null;
    if (empty($name)) {
        return false; // Name must not be empty
    }

    // Determine if the connection is secure
    $secure = !empty($_SERVER['HTTPS']) ? 'Secure' : '';

    // Set the domain
    $domain = "Domain={$_SERVER['SERVER_NAME']}";

    // Validate the path

    if (array_key_exists('path', $options) && is_string($options['path'])) {
        $path = preg_match('/^[\\/%a-zA-Z\\-0-9._]+$/D', $options['path']) ? "Path={$options['path']}" : '';
    } else {
        $path = 'Path=/';
    }

    // Max-Age handling
    $date = new \DateTimeImmutable("@{$_SERVER['REQUEST_TIME']}");
    if (array_key_exists('max-age', $options) && is_integer($maxAge = $options['max-age'])) {
        $expires = $date->add(new DateInterval("PT{$maxAge}S"));
        if ($maxAge > 0) {
            $maxAge = "Max-Age=$maxAge";
        } else {
            $maxAge = '';
        }
        $expires = "Expires={$expires->format('D, d M Y H:i:s \\G\\M\\T')}";
    } else {
        $expires = $maxAge = '';
    }
    if (array_key_exists('session', $options) && $options['session']) {
        $expires = $maxAge = '';
    }

    // HttpOnly flag
    $httpOnly = array_key_exists('HttpOnly', $options) && $options['HttpOnly'] ? 'HttpOnly' : '';

    if (empty($value)) {
        $maxAge = "Max-Age=0";
        $expires = gmdate('D, d M Y H:i:s', +"{$_SERVER['REQUEST_TIME']}" - 100) . " GMT";
    }

    $header = '';
    foreach ([$maxAge, $expires, $domain, $httpOnly, $path, $secure, 'SameSite=Lax'] as $item) {
        if (empty($item)) continue;
        $header .= "; $item";
    }
    // Assemble the Set-Cookie header
    $header = "Set-Cookie: $name=$value$header";

    // Send the cookie header
    if ($send) header($header, false);
    return $header;
}

//class ANTCookie
//{
//    private readonly string $name;
//    private readonly string $value;
//    private bool $HttpOnly = false;
//    private ?int $maxAge = null;
//
//    public function __construct(string $name, string $value)
//    {
//        $this->name = $name;
//        $this->value = $value;
//    }
//
//    public function setMaxAge(int $seconds): self
//    {
//        if ($seconds > 0) {
//            $this->maxAge = $seconds;
//        }
//        return $this;
//    }
//
//    public function setHttpOnly(bool $HttpOnly): self
//    {
//        $this->HttpOnly = $HttpOnly;
//        return $this;
//    }
//
//    public function __toString(): string
//    {
//        $array = array('HttpOnly' => $this->HttpOnly);
//        if (is_integer($this->maxAge)) $array['max-age'] = $this->maxAge;
//        return set_cookie($this->name, $this->value, $array);
//    }
//
//    public function send(): self
//    {
//        header("$this", false);
//        return $this;
//    }
//}
