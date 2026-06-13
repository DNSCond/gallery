<?php // writer

function structuredFields_encode(mixed $value): string
{
    // (Assuming you have handled Arrays/Dictionaries/Parameters elsewhere,
    // and are now processing an individual string value)
    if (is_string($value)) {
        return _serialize_sf_string($value);
    } elseif ($value instanceof SFSerialize) {
        return $value->sfSerialize();
    } elseif (is_int($value) || is_float($value)) {
        return _serialize_sf_int_or_float($value);
    } elseif (is_bool($value)) {
        return "?" . (int)$value;
    } else throw new TypeError;
}

function _serialize_sf_int_or_float(int|float $value): string
{
    // 1. Check for NaN or Infinity (not allowed in HTTP structured headers)
    if (is_nan($value) || is_infinite($value)) {
        throw new InvalidArgumentException("NaN or Infinity cannot be encoded in Structured Fields.");
    }

    // 2. Format safely: Force '.' decimal separator and round to max 3 decimal places
    // sprintf('%0.3f') ensures it doesn't drop into scientific notation.
    $formatted = sprintf('%.3f', $value);

    // 3. Clean up trailing zeros (e.g., "12.300" -> "12.3", "12.000" -> "12.0")
    // RFC 9651 requires at least one decimal digit (e.g., "12.0" is valid, "12." is not).
    $formatted = rtrim($formatted, '0');
    if (str_ends_with($formatted, '.')) {
        $formatted .= '0';
    }

    return $formatted;
}


function _serialize_sf_string(string $str): string
{
    // Check if the string contains ANY character outside the printable ASCII range
    if (preg_match('/[^\x20-\x7E]/', $str)) {
        // It's a Display String!
        return _serialize_display_string($str);
    }

    // It's a Standard ASCII String!
    return _serialize_ascii_string($str);
}

function _serialize_ascii_string(string $str): string
{
    // Escape backslashes and double quotes
    $escaped = str_replace(['\\', '"'], ['\\\\', '\"'], $str);
    return "\"$escaped\"";
}

function _serialize_display_string(string $str): string
{
    // RFC 9651 requires percent-encoding for Display Strings.
    // We raw-url-encode the string, but we must carefully restore
    // the safe characters that RFC 9651 allows UNENCODED inside %"..."

    $encoded = rawurlencode($str);

    // RFC 9651 allows printable ASCII to remain unencoded inside %"...",
    // EXCEPT for '%' and '"'.
    // You can optionally decode safe characters to make the wire format cleaner,
    // or leave it entirely percent-encoded (which is completely valid).

    return "%\"$encoded\"";
}

interface SFSerialize
{
    function sfSerialize(): string;
}

readonly class SFDisplayString implements SFSerialize
{
    public function __construct(private string $string)
    {
    }

    public function __toString(): string
    {
        return "$this->string";
    }

    public function toString(): string
    {
        return "$this";
    }

    public function sfSerialize(): string
    {
        return _serialize_display_string("$this");
    }
}

readonly class SFAsciiString implements SFSerialize
{
    public function __construct(private string $string)
    {
        if (preg_match('/[^\x20-\x7E]/', $string)) {
            throw new InvalidArgumentException("SFAsciiString can only contain printable ASCII.");
        }
    }

    public function __toString(): string
    {
        return "$this->string";
    }

    public function toString(): string
    {
        return "$this";
    }

    public function sfSerialize(): string
    {
        return _serialize_ascii_string("$this");
    }
}

readonly class SFByteSequence implements SFSerialize
{
    public function __construct(private string $bytes)
    {
    }

    public function __toString(): string
    {
        return "$this->bytes";
    }

    public function toString(): string
    {
        return "$this";
    }

    public function sfSerialize(): string
    {
        return ':' . base64_encode("$this") . ':';
    }

    public static function fromBase64(string $base64String): self
    {
        $decoded = base64_decode($base64String, true);
        if ($decoded === false) {
            throw new InvalidArgumentException("Invalid Base64 payload provided.");
        }
        return new self($decoded);
    }
}

readonly class SFString extends SFAsciiString implements SFSerialize
{
}

readonly class SFInteger implements SFSerialize
{
    public function __construct(private int $int)
    {
    }

    public function __toString(): string
    {
        return _serialize_sf_int_or_float($this->int);
    }


    public function sfSerialize(): string
    {
        return "$this";
    }
}

readonly class SFDate implements SFSerialize
{
    public function __construct(private int $int)
    {
    }

    public function __toString(): string
    {
        return "$this->int";
    }

    static function fromUTC(int $year = 2024, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0, int $second = 0): SFDate
    {
        $datetime = gmmktime($hour, $minute, $second, $month, $day, $year);
        return new self($datetime);
    }

    static function mktimeUTC(int $hour = 0, int $minute = 0, int $second = 0, int $month = 1, int $day = 1, int $year = 2024): SFDate
    {
        $datetime = gmmktime($hour, $minute, $second, $month, $day, $year);
        return new self($datetime);
    }

    static function fromDateTimeInterface(DateTimeInterface $input): SFDate
    {
        return new self($input->getTimestamp());
    }

    public function sfSerialize(): string
    {
        return "@$this";
    }
}
