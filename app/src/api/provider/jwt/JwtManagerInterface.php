<?php

namespace toubilib\api\provider\jwt;

interface JwtManagerInterface
{
  public const ACCESS_TOKEN = 'access';
  public const REFRESH_TOKEN = 'refresh';


  public function create(array $payload, string $type): string;

  public function setIssuer(string $issuer): void;
  
}
