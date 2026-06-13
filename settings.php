<?php // $selectedFilter
$selectedFilter = match (array_key_exists('with-desc', $_GET) ? ($_GET['with-desc']) : 'either') {
    'no' => 'no',
    'with' => 'with',
    default => 'either',
};

$selectedBorder = match (array_key_exists('with-bord', $_GET) ? ($_GET['with-bord']) : '0') {
    '1' => '1',
    'n' => 'n',
    's' => 's',
    default => '0',
};

$gallery = !!$_GET['gallery'];
$universe = $_GET['universe'] ?? 'Favicond-All';
$sorted = (string)($_GET['sorted'] ?? 'UniverseName');
$AiArt = match ($_GET['AiArt']) {
    '1' => '1', // show
    '2' => '2', // only
    default => '0', // hide
};
header('file-ran: settings.php', false);
