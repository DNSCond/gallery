<?php if (file_exists(__DIR__ . '/../../../devhost.txt')) {
    $ext = getimagesize(__DIR__ . "/../imgdata/{$_GET['hash']}");
    header("image-size: w=$ext[0], h=$ext[1]");
    header("image-type:{$ext['mime']}");
} else {
    http_response_code(404);
}
//<script type=application/json is=output-script><\\?= json_encode($data) ?\\></script>
