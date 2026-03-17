<?php use function Helpers\json_fromArray;
use function Helpers\Mime\get_accept_mimetype;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
require_once "sitemap-library.php";
$type = 'xml';
if (array_key_exists('type', $_GET)) {
    $type = match ("{$_GET['type']}") {
        'json' => 'json',
        'html' => 'html',
        default => 'xml',
    };
} else {
    $temp_type = get_accept_mimetype(['application/xml', 'text/html', 'application/json']) ?? 'application/xml';
    $type = match ("{$_GET['type']}") {
        'application/json' => 'json',
        'text/html' => 'html',
        default => 'xml',
    };
}
$urlSet = new XUrlSet($base = new XUrl('https://antrequest.nl', '/'))->set_withOuter(true);
$base->set_changefreq(changefreq::daily)->set_priority(10);

if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/standard/lastModified.json")) {
    $array = array();
    $json = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/standard/lastModified.json"), true)['metadata'];
    foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/standard/*/*/index.php") as $entry) {
        if (preg_match('/\\/([A-Za-z0-9_\\-]+)\\/(\\d+)\\.(\\d+)\\.(\\d+)\\/index\\.php$/D', $entry, $matches)) {
            if (!array_key_exists($matches[1], $array)) $array[$matches[1]] = array();
            $array[$matches[1]][] = array('major' => +$matches[2], 'minor' => +$matches[3],
                'patch' => +$matches[4], 'path' => $matches[1]);
        }
    }
    foreach ($array as $item) {
        usort($item, function ($x, $y) {
            /** @noinspection PhpLoopCanBeConvertedToArrayAnyInspection */
            foreach (['major', 'minor', 'patch'] as $key) {
                if ($x[$key] !== $y[$key]) return -($x <=> $y);
            }
            return +0;
        });
    }
    $first = true;
    foreach ($array as $path => $specification) {
        foreach (array_reverse($specification) as $specificationVersion) {
            $lzpV = "{$specificationVersion['major']}.{$specificationVersion['minor']}.{$specificationVersion['patch']}";
            $xurl = $urlSet->addXUrl("/standard/$path/$lzpV/")->set_changefreq(changefreq::never);
            if ($json[$path]) {
                if ($json[$path][$lzpV]) {
                    $xurl->set_lastMod(strtotime($json[$path][$lzpV]['lastModified'] ?? '2026-03-01T12:45:07Z'));
                } else {
                    $xurl->set_lastMod(strtotime('2026-03-01T12:45:07Z'));
                }
            } else {
                $xurl->set_lastMod(strtotime('2026-03-01T12:45:07Z'));
            }
            if ($first) {
                $first = false;
                $xurl->set_priority(7);
            } else {
                $xurl->set_priority(2);
            }
        }
    }
}
foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/images/*/main.json") as $galleryEntry) {
    if (preg_match('/gallery\\/htignore\\/images\\/([^\\/]+)\\/main\\.json$/iD', $galleryEntry, $matches)) {
        $xurl = $urlSet->addXUrl("/gallery/char/$matches[1]")->set_changefreq(changefreq::daily);
        if (file_exists($galleryEntry)) {
            $content = file_get_contents($galleryEntry);
            if (file_exists(preg_replace('/\\.json$/D', '.php', $galleryEntry))) {
                $xurl->set_priority(6);
            } else {
                $xurl->set_priority(4);
            }
            if ($content) {
                if ($json = json_decode($content, true)) {
                    if (is_string($lastmod = $json['LatModified'] ?? $json['creationDate'])) {
                        $xurl->set_lastMod(max(1771921594, strtotime($lastmod)));
                    }
                }
            }
        }
    }
}
//foreach (glob("{$_SERVER['DOCUMENT_ROOT']}/gallery/htignore/comic-images/*/*/edata.json") as $item) {
//if (preg_match('/\\/([a-zA-Z0-9\\-]+)\\/(\d+)\\/edata\\.json$/D', $item, $matches)) {
//$xurl = $urlSet->addXUrl("/gallery/char/$matches[1]");}}

if ($type === 'html') {
    header('content-type: text/html');
    echo $urlSet->asHTML(HTMLPage::fullpage, 'ANTRequest.nl');
} elseif ($type === 'json') {
    header('content-type: application/json');
    echo json_fromArray($urlSet, false);
} else {
    header('content-type: application/xml');
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";
    echo $urlSet;
}