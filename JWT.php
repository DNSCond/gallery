<?php namespace JWT;

use JSONWT\JWT;
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/JSONWT.php";

// crypto.getRandomValues(new Uint8Array(67)).toHex()
const secret = '026c56c425825f58b24b96f3ea54dfa46b563a4139687039e837e2824868c75bc5fb6ca2e07e41be46b830faeedb0c99a6c42ed12d6e14a39748bdf55802e827ca5526';

/**
 * @param array $json
 * @param int $validForSeconds
 * @return string
 * @deprecated for backwards compat
 */
function generateToken(array $json, int $validForSeconds): string
{
    return new JWT(secret)->generate($json, $validForSeconds);
}

/**
 * @param string $token
 * @return false|array
 * @deprecated for backwards compat
 */
function validateToken(string $token): false|array
{

    return new JWT(secret)->validate($token);
}
