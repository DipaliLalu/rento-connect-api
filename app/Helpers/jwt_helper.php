<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getJWTFromRequest()
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

function generateJWT($userData)
{
    $key = getenv('JWT_SECRET');
    $payload = [
        'iss' => 'localhost',
        'aud' => 'localhost',
        'iat' => time(),
        'exp' => time() + 3600, // 1 hour
        'data' => $userData
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function validateJWT($token)
{
    try {
        $key = getenv('JWT_SECRET');
        return JWT::decode($token, new Key($key, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
