<?php if (file_exists(__DIR__ . '/../../../devhost.txt')) {
    $ext = getimagesize(__DIR__ . "/../imgdata/{$_GET['hash']}");
    if ($ext) {
        header("image-size: w=$ext[0], h=$ext[1]");
        header("image-type: {$ext['mime']}");
        exit;
    }
}

http_response_code(404);
