<?php namespace HashApi;

use Random\RandomException;

function sha256Base64(string $string): string
{
    return base64_encode(hash('sha256', $string, true));
}

function sha384Base64(string $string): string
{
    return base64_encode(hash('sha384', $string, true));
}

function sha512Base64(string $string): string
{
    return base64_encode(hash('sha512', $string, true));
}

function nonceBase64(): string
{
    try {
        return 'nonce-' . base64_encode(random_bytes(16));
    } catch (RandomException) {
        return 'null';
    }
}

function sendHashApi(string $string, bool $override = false): array
{
    header('vary: User-Agent', false);
    if ($override || ($_SERVER['HTTP_USER_AGENT'] === 'FaviEdge/25.5.19')) {
        header("FX-HashApi-Nonce: " . nonceBase64());
        $sha256 = sha256Base64($string);
        $sha384 = sha384Base64($string);
        $sha512 = sha512Base64($string);
        $sha1 = base64_encode(sha1($string, true));
        header("FX-HashApi-CHash: sha256-$sha256, sha384-$sha384, sha512-$sha512, sha1-$sha1");
        header("FX-HashApi-DateTime: " . gmdate("D, d M Y H:i:s \\G\\M\\T", $_SERVER['REQUEST_TIME']));
        return array("sha256" => $sha256, "sha384" => $sha384, "sha512" => $sha512, "sha1" => $sha1);
    } else return array();
}
