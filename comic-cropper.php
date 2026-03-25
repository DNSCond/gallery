<?php

try {
    $image = new Imagick($path = __DIR__ . ($file =
            "/htignore/comic-images/{$_GET['comic-name']}/{$_GET['episode']}/raws/img{$_GET['imageId']}.{$_GET['format']}"));

    $height = getimagesize($path)[1];
    // cropImage(width, height, x, y)
    $image->cropImage(800, 1280, 0, +$_GET['chunk'] * 1280);

    // Optional: reset canvas so offsets don't remain
    $image->setImagePage(0, 0, 0, 0);

    $fileContent = $image->getImageBlob();
    $ext = getimagesizefromstring("$fileContent");
    header("content-length:" . strlen($fileContent));
    $return_path = preg_replace('/\\/(?:raws|htignore\\/)/', '', $file);
    $return_path = preg_replace('/img(\\d{3})/', "-img$1-{$_GET['chunk']}", $return_path);
    header("return_path: $return_path");
    header("content-type:{$ext['mime']}");
    header("image-width: $ext[0]");
    header("image-height:$ext[1]");
    header("chunk-next: " . (+$_GET['chunk'] < (($height / 1280)-1) ? '?1' : '?0'));
    echo "$fileContent";
} catch (ImagickException $e) {
    http_response_code(500);
    header('content-type: text/plain');
    header("chunk-next: ?0");
    echo "$e";
}
