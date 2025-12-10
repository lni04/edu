<?php
function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function createJWT(array $payload): string {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload['exp'] = time() + JWT_EXPIRE;
    
    $h = base64UrlEncode(json_encode($header));
    $p = base64UrlEncode(json_encode($payload));
    $s = base64UrlEncode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    
    return "$h.$p.$s";
}

function verifyJWT(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    [$h, $p, $s] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    
    if (!hash_equals($expected, $s)) return null;
    
    $payload = json_decode(base64UrlDecode($p), true);
    if ($payload['exp'] < time()) return null;
    
    return $payload;
}

function getAuthUser(): ?array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/', $header, $m)) return null;
    return verifyJWT($m[1]);
}
