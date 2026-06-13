<?php use ANTHeader\ANTNavLinkTag;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;
use function Helpers\json_fromArray;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";

function base58_encode(string $bytes): string
{
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    if ($bytes === '') return '';

    $data = array_values(unpack('C*', $bytes));
    // Count leading zero bytes
    $zeroCount = 0;
    while ($zeroCount < count($data) && $data[$zeroCount] === 0) {
        $zeroCount++;
    }
    $result = '';
    while (count($data) > 0) {
        $carry = 0;
        $next = [];
        foreach ($data as $byte) {
            $carry = ($carry << 8) + $byte; // multiply by 256 and add byte
            $digit = intdiv($carry, 58);
            $carry %= 58;
            if (count($next) > 0 || $digit !== 0) {
                $next[] = $digit;
            }
        }
        $result = $alphabet[$carry] . $result;
        $data = $next;
    }
    return str_repeat('1', $zeroCount) . $result;
}

function base58Encode(string $bytes): string
{
    return base58_encode($bytes);
}

$output = false;
$paths = array();
//$imagickExceptions = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
    $fileTmp = $_FILES['upload']['tmp_name'];   // temporary file path
    $fileName = $_FILES['upload']['name'];      // original filename
    $fileSize = $_FILES['upload']['size'];
    $fileError = $_FILES['upload']['error'];
    $outputDir = 'output_svgs/';

    // --- Ensure a file was uploaded ---
    if ($_FILES['upload']['error'] === 0) {

        // --- Read uploaded SVG ---
        $uploadedFile = $_FILES['upload']['tmp_name'];
        $svgContent = file_get_contents($uploadedFile);

        // --- Compute SHA256 hash and Base58 encode ---
        $sha256 = hash('sha256', $svgContent, true); // raw binary
        $base58 = base58Encode($sha256);
        $outputDir = "output_svgs/sha256B58-$base58";
        $output = $outputDir;
        // --- Create output directory ---
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0o777, true);
            // --- Load SVG ---
            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($svgContent);

            // Get root and defs/style
            $svgRoot = $dom->documentElement;
            $defsList = $svgRoot->getElementsByTagName('defs');
            $styleList = $svgRoot->getElementsByTagName('style');

            // Drawing tags to split
            $drawingTags = ['path', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse'];

            $counter = 0;

            foreach ($drawingTags as $tag) {
                $elements = $svgRoot->getElementsByTagName($tag);
                $elementsArray = [];
                foreach ($elements as $el) {
                    $elementsArray[] = $el; // copy to avoid live NodeList issues
                }

                foreach ($elementsArray as $element) {
                    $newDom = new DOMDocument();
                    $newDom->preserveWhiteSpace = false;
                    $newDom->formatOutput = true;

                    // shallow copy of original <svg> root (preserve attributes exactly)
                    $newSvgRoot = $newDom->importNode($svgRoot, false);
                    $newDom->appendChild($newSvgRoot);

                    // copy <defs> and <style>
                    foreach ($defsList as $defs) {
                        $newSvgRoot->appendChild($newDom->importNode($defs, true));
                    }
                    foreach ($styleList as $style) {
                        $newSvgRoot->appendChild($newDom->importNode($style, true));
                    }

                    // copy the drawing element
                    $newSvgRoot->appendChild($newDom->importNode($element, true));

                    // save file
                    $filename = "$outputDir/{$tag}_$counter.svg";
                    $newDom->save($filename);
                    $paths[] = $filename;
                    $counter++;
                }
            }
            if ($counter) {
                foreach ($paths as $svg) {
                    try {
                        $im = new Imagick($svg_path = realpath("./$svg"));
                        $im->setBackgroundColor(new ImagickPixel('transparent'));
                        $im->setImageFormat('png32');
                        $im->writeImage(preg_replace('/\\.svg$/D', '.png', $svg_path));
                    } catch (ImagickException$e) {
                        // $imagickExceptions[] = (string)$e;
                    }
                }
                [$width, $height] = getimagesize(preg_replace('/\\.svg$/D', '.png', $paths[0]));
                $pngDir = "$outputDir/"; // directory with PNGs
                $zipFile = "{$pngDir}output.zip";

                // Create new ZipArchive
                $zip = new ZipArchive();
                if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Add all PNG files in directory
                    $files = glob($pngDir . '*.png'); // all PNGs in directory
                    foreach ($files as $file) {
                        // Add file to zip. The second argument sets the filename **inside the zip**
                        $zip->addFile($file, basename($file));
                    }
                    file_put_contents("{$pngDir}layerzip.json", json_fromArray([
                            'specVersion' => '0.0.2', 'width' => $width, 'height' => $height,
                            'layers' => array_map(fn($path) => array(
                                    'type' => 'rasterlayer',
                                    'path' => $path,
                                    'name' => $path,
                                    'blendMode' => 'normal',
                            ), $files),
                    ], false));
                    $zip->addFile("{$pngDir}layerzip.json", basename('layerzip.json'));
                    // Close zip
                    $zip->close();
                }
            }
        }
    }
}

create_head2($title = 'SVG to LayerZip (PNG Only Mode)', [
], [new ANTNavLinkTag('stylesheet', ["cssx.css", 'ddDL-table.css']),
], [ANTNavFavicond('https://ANTRequest.nl', $title, true)]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <form method=post enctype=multipart/form-data>
        <label>Choose File: <input type=file accept=image/svg+xml name=upload></label>
        <button type=submit>Create</button>
        <output><?= ($output ? "<a href=\"./$output/output.zip\">Download LayerZip</a>" : 'No Output Yet.') ?></output>
    </form>

</div>
