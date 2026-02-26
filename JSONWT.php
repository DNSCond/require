<?php namespace JSONWT;

class JWT
{
    private string $secret;
    private string $issuer;

    public function __construct(string $secret, string $issuer = 'ANTRequest.nl')
    {
        $this->secret = $secret;
        $this->issuer = $issuer;
    }

    public function generate(array $data, int $ttl = 3600): string
    {
        $iat = $_SERVER['REQUEST_TIME'] ?? time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $this->issuer, 'iat' => $iat, 'nbf' => $iat,
            'exp' => $iat + $ttl, 'data' => $data,
        ]));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );

        return "$header.$payload.$signature";
    }

    public function validate(string $token): false|array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;

        $decodedHeader = json_decode($this->base64UrlDecode($header), true);
        if (!isset($decodedHeader['alg']) || $decodedHeader['alg'] !== 'HS256') {
            return false;
        }

        $expected = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );

        if (!hash_equals($expected, $signature)) return false;

        $claims = json_decode($this->base64UrlDecode($payload), true);
        if (!$claims || !is_array($claims)) return false;

        $now = $_SERVER['REQUEST_TIME'] ?? time();

        // Time Checks
        if (isset($claims['exp']) && $now >= $claims['exp']) return false;
        if (isset($claims['nbf']) && $now < $claims['nbf']) return false;

        return $claims['data'] ?? false;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
