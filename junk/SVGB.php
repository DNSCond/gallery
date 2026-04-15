<?php

class RangeError extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message, 5);
    }
}

class SVGBEncode
{
    private array $toEncode = [];
    private float $w;
    private float $h;

    private const array PARAM_COUNT = [
        'M' => 2, 'm' => 2, 'L' => 2, 'l' => 2, 'H' => 1, 'h' => 1,
        'V' => 1, 'v' => 1, 'C' => 6, 'c' => 6, 'S' => 4, 's' => 4,
        'Q' => 4, 'q' => 4, 'T' => 2, 't' => 2, 'A' => 7, 'a' => 7, 'Z' => 0, 'z' => 0
    ];

    /**
     * @throws RangeError
     */
    public function __construct($w, $h)
    {
        if ($w < 0 || $h < 0) {
            throw new RangeError('Dimensions must be positive');
        }
        $this->w = (float)$w;
        $this->h = (float)$h;
    }

    private function toRGBAInt($color): ?int
    {
        if ($color === null) return null;
        $color = ltrim($color, '#');
        if (strlen($color) === 6) $color .= 'FF';
        return (int)hexdec($color);
    }

    /**
     * @throws RangeError
     */
    private function makeBasicShape(string $shapeType, $fill, $stroke, $stroke_width): array
    {
        $obj = [
            'shapeType' => $shapeType,
            'fill' => $this->toRGBAInt($fill),
            'stroke' => null,
            'stroke_width' => 0
        ];

        if ($stroke !== null && $stroke_width !== null) {
            $obj['stroke'] = $this->toRGBAInt($stroke);
            $sw = (int)$stroke_width;
            if ($sw < 0 || $sw > 255) throw new RangeError('stroke_width must be 0-255');
            $obj['stroke_width'] = $sw;
        }
        return $obj;
    }

    /**
     * @throws RangeError
     */
    public function addPath($fill, string $svgPath, $stroke = null, $stroke_width = null): void
    {
        $pathObject = $this->makeBasicShape('Path', $fill, $stroke, $stroke_width);
        $pathObject['path'] = $this->expandCommands($this->parsePath($svgPath));
        $this->toEncode[] = $pathObject;
    }

    /**
     * @throws RangeError
     */
    public function addRect($fill, $x, $y, $w, $h, $stroke = null, $stroke_width = null): void
    {
        $obj = $this->makeBasicShape('Rect', $fill, $stroke, $stroke_width);
        $obj['args'] = [(float)$x, (float)$y, (float)$w, (float)$h];
        $this->toEncode[] = $obj;
    }

    /**
     * @throws RangeError
     */
    public function addCircle($fill, $cx, $cy, $r, $stroke = null, $stroke_width = null): void
    {
        $obj = $this->makeBasicShape('Circle', $fill, $stroke, $stroke_width);
        $obj['args'] = [(float)$cx, (float)$cy, (float)$r];
        $this->toEncode[] = $obj;
    }

    /**
     * @throws RangeError
     */
    public function addEllipse($fill, $cx, $cy, $rx, $ry, $stroke = null, $stroke_width = null): void
    {
        $obj = $this->makeBasicShape('Ellipse', $fill, $stroke, $stroke_width);
        $obj['args'] = [(float)$cx, (float)$cy, (float)$rx, (float)$ry];
        $this->toEncode[] = $obj;
    }

    public function constructBinary(bool $littleEndian = true): string
    {
        $le = $littleEndian;
        $u32 = $le ? 'V' : 'N';
        $u16 = $le ? 'v' : 'n';
        $f32 = $le ? 'G' : 'g';

        $buffer = pack("A4", "SVGB");
        $buffer .= pack($u16, count($this->toEncode));
        $buffer .= pack($f32, $this->w);
        $buffer .= pack($f32, $this->h);

        foreach ($this->toEncode as $element) {
            $isPath = $element['shapeType'] === 'Path';
            $type = $isPath ? 0b1 : 0b0;
            if ($element['fill'] !== null) $type |= 0b10;
            if ($element['stroke'] !== null) $type |= 0b100;

            $buffer .= pack("C", $type);

            if (!$isPath) {
                $tag = ['Rect' => 1, 'Circle' => 2, 'Ellipse' => 3][$element['shapeType']];
                $buffer .= pack("C", $tag);
            }

            if ($element['fill'] !== null) {
                $buffer .= pack($u32, $element['fill']);
            }
            if ($element['stroke'] !== null) {
                $buffer .= pack("C", $element['stroke_width']);
                $buffer .= pack($u32, $element['stroke']);
            }

            if ($isPath) {
                $buffer .= pack($u16, count($element['path']));
                foreach ($element['path'] as $cmd) {
                    $char = $cmd['type'];
                    $buffer .= pack("C", ord($char));
                    if (strtoupper($char) === 'A') {
                        $buffer .= pack($f32, (float)$cmd['values'][0]);
                        $buffer .= pack($f32, (float)$cmd['values'][1]);
                        $buffer .= pack($f32, (float)$cmd['values'][2]);
                        $flags = ((int)$cmd['values'][3] ? 0b01 : 0) | ((int)$cmd['values'][4] ? 0b10 : 0);
                        $buffer .= pack("C", $flags);
                        $buffer .= pack($f32, (float)$cmd['values'][5]);
                        $buffer .= pack($f32, (float)$cmd['values'][6]);
                    } else {
                        foreach ($cmd['values'] as $val) {
                            $buffer .= pack($f32, (float)$val);
                        }
                    }
                }
            } else {
                foreach ($element['args'] as $val) {
                    $buffer .= pack($f32, $val);
                }
            }
        }
        return $buffer;
    }

    private function parsePath(string $d): array
    {
        preg_match_all('/([a-zA-Z])([^a-zA-Z]*)/', $d, $matches, PREG_SET_ORDER);
        $result = [];
        foreach ($matches as $match) {
            $type = $match[1];
            preg_match_all('/-?\d*\.?\d+(?:e[-+]?\d+)?/i', $match[2], $numMatches);
            $numbers = array_map('floatval', $numMatches[0]);
            $needed = self::PARAM_COUNT[strtoupper($type)] ?? 0;
            if ($needed === 0) {
                $result[] = ['type' => $type, 'values' => []];
                continue;
            }
            for ($i = 0; $i < count($numbers); $i += $needed) {
                $result[] = ['type' => $type, 'values' => array_slice($numbers, $i, $needed)];
            }
        }
        return $result;
    }

    private function expandCommands(array $commands): array
    {
        $result = [];
        foreach ($commands as $cmd) {
            $type = $cmd['type'];
            $values = $cmd['values'];
            if (strtoupper($type) === 'M') {
                for ($i = 0; $i < count($values); $i += 2) {
                    $chunk = array_slice($values, $i, 2);
                    if ($i === 0) $result[] = ['type' => $type, 'values' => $chunk];
                    else $result[] = ['type' => ($type === 'M' ? 'L' : 'l'), 'values' => $chunk];
                }
            } else {
                $result[] = $cmd;
            }
        }
        return $result;
    }
}
