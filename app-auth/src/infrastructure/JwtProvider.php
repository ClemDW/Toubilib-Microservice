<?php
namespace toubilib\api\infrastructure;

class JwtProvider
{
    private $secret;

    public function __construct()
    {
        // À adapter selon votre configuration
        $this->secret = getenv('JWT_SECRET') ?: 'votre_secret';
    }

    public function validate($jwt)
    {
        // Décodage et validation du JWT (exemple simple, à remplacer par une vraie lib JWT)
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Token format invalid.');
        }
        // Ici, on pourrait utiliser firebase/php-jwt ou une autre lib
        // Pour l’exemple, on ne vérifie que la structure
        // Ajoutez ici la vérification de la signature et de l’expiration
        return true;
    }
}
