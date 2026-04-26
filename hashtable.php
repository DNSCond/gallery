<?php use function ANTHeader\ANTNavBinary;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavHome;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use ANTHeader\ANTNavIStyle;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";

$bgcolor = new Color('#0073a6');
create_head2('Hash Table!', [], [
    new ANTNavLinkTag('stylesheet', []),
    new ANTNavIStyle('table{border-collapse:collapse;background-color:white;margin:auto;}td,th{border:' .
        '1px solid #dddddd;text-align:left;padding:8px;}tr:nth-child(even) {background-color:#dddddd;}'),
], [
    ANTNavHome(),
    ANTNavBinary('/gallery/ascii-table.php', 'Ascii Table'),
    new ANTNavOption('/gallery/hashtable.php', '/dollmaker2/icon/endpoint.php?' .
        'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
        'Hash Table', '#00a6a6', '#00ffff', true),
]);

echo "<div style=overflow-x:scroll>\n";
echo '<table><thead><tr><th>Hash Algorithm<th>Encoding<th>Identifier<th>Alternate identifier<th>Multibase (if applicable)</thead><tbody>';
$bases = [
    'b64' => ['name' => 'Base64 (padded)', 'multibase' => 'm'],
    'b64u' => ['name' => 'Base64Url', 'multibase' => 'u'],
    'hex' => ['name' => 'hex (lowercase)', 'multibase' => 'f'],
    'uhex' => ['name' => 'hex (uppercase)', 'multibase' => 'F'],
    'b58' => ['name' => 'Base58 BTC', 'multibase' => 'z'],
    'b32' => ['name' => 'Base32 (RFC 4648)', 'multibase' => 'b'],
    'r' => ['name' => 'Raw (unencoded)', 'multibase' => null],
];
foreach (['sha256' => 'SHA-256', 'sha512' => 'SHA-512', 'sha384' => 'SHA-384',
             'blake3' => 'BLAKE3', 'sha512_256' => 'SHA-512/256',
             'sha3_256' => 'SHA-3 (256)',
             'sha3_512' => 'SHA-3 (512)',
         ] as $identifierHash => $nameHash) {
    // binary hashes.
    foreach ($bases as $identifierDecode => $nameDecode) {
        $alt = $nameDecode['multibase'] === null ? 'N&#x2f;A' : "$identifierHash{$nameDecode['multibase']}";
        echo "\n    <tr><td>$nameHash<td>{$nameDecode['name']}<td>$identifierHash$identifierDecode<td>$alt<td>" .
            ($nameDecode['multibase'] === null ? 'N&#x2f;A' : $nameDecode['multibase']);
    }
}
// special hashes.
$identifierDecode = '';
$nameDecode = 'Self Specified';
foreach (['bcrypt' => 'BCRYPT', 'a2i' => 'Argon2i', 'a2id' => 'Argon2id'] as $identifierHash => $nameHash) {
    echo "\n    <tr><td>$nameHash<td>$nameDecode<td>$identifierHash$identifierDecode<td>N&#x2f;A<td>N&#x2f;A";
}
// legacy hashes.
foreach (['sha1' => 'SHA-1', 'md5' => 'MD5'] as $identifierHash => $nameHash) {
    // binary hashes.
    foreach ($bases as $identifierDecode => $nameDecode) {
        $alt = $nameDecode['multibase'] === null ? 'N&#x2f;A' : "$identifierHash{$nameDecode['multibase']}";
        echo "\n    <tr><td>$nameHash (Legacy)<td>{$nameDecode['name']}<td>$identifierHash$identifierDecode<td>".
            "$alt<td>{$nameDecode['multibase']}";
    }
}
echo "\n</tbody></table>\n</div>\n";
