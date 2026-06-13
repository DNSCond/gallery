<?php header('content-type: application/json');
require_once 'GREASE.php';
$array = array('n' => createGREASEJsonNumberValue());
$array[createGREASEJsonKey()] = createGREASEJsonValue();
echo json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
