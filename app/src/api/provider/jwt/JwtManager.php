<?php

namespace toubilib\api\provider\jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use toubilib\api\provider\jwt\JwtManagerInterface;
use UnexpectedValueException;

class JwtManager implements JwtManagerInterface
{
  private string $secret;
  private int $access_expiration_time;
  private int $refresh_expiration_time;
  private string $issuer;

  public function __construct(string $secret, int $expirationTime, int $refreshExpirationTime)
  {
    $this->secret = $secret;
    $this->access_expiration_time = $expirationTime;
    $this->refresh_expiration_time = $refreshExpirationTime;
  }

  public function setIssuer(string $issuer): void
  {
    $this->issuer = $issuer;
  }

  public function create(array $payload, string $type): string
  {
    if ($type === JwtManagerInterface::ACCESS_TOKEN) {
      $expirationTime = time() + $this->access_expiration_time;
    } else {
      $expirationTime = time() + $this->refresh_expiration_time;
    }

    $token = JWT::encode(
      [
        'iss' => $this->issuer,
        'sub' => $payload['id'],
        'iat' => time(),
        'exp' => $expirationTime,
        'upr' => $payload,
      ],
      $this->secret,
      'HS512'
    );

    return $token;
  }

  public function decode(string $token): array
  {
    try {
      $decoded = JWT::decode($token, new Key($this->secret, 'HS512'));
      return json_decode(json_encode($decoded), true);
    } catch (UnexpectedValueException $e) {
      throw new \RuntimeException("Token invalide: " . $e->getMessage());
    }
  }
}