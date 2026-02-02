<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;

use toubilib\api\actions\ListerPraticienAction;
use toubilib\api\actions\RechercherPraticienAction;
use toubilib\api\actions\AfficherPraticienAction;

return function (App $app): App {

    // ==============================
    // UI HTML de test (Simplifiée pour Praticiens)
    // ==============================
    $app->get('/', function (Request $request, Response $response) {
         $html = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Service Praticiens - Toubilib</title>
        </head>
        <body>
            <h1>Service Praticiens</h1>

            <h2>Liste des praticiens</h2>
            <button onclick="callApi('/praticiens', 'listePraticiens')">Charger</button>
            <pre id="listePraticiens"></pre>

            <h2>Détails praticien</h2>
            <input type="text" id="praticienId" placeholder="ID praticien">
            <button onclick="callApi('/praticiens/' + document.getElementById('praticienId').value, 'detailsPraticien')">Voir</button>
            <pre id="detailsPraticien"></pre>

            <h2>Rechercher un praticien</h2>
            <input type="text" id="searchSpecialite" placeholder="Spécialité">
            <input type="text" id="searchVille" placeholder="Ville">
            <button onclick="callApi('/praticiens/recherche?specialite=' + document.getElementById('searchSpecialite').value + '&ville=' + document.getElementById('searchVille').value, 'searchResult')">Rechercher</button>
            <pre id="searchResult"></pre>

            <script>
                async function callApi(url, targetId) {
                    try {
                        const res = await fetch(url);
                        const data = await res.json();
                        document.getElementById(targetId).textContent = JSON.stringify(data, null, 2);
                    } catch (err) {
                        document.getElementById(targetId).textContent = "Erreur: " + err;
                    }
                }
            </script>
        </body>
        </html>
        HTML;

        $response->getBody()->write($html);
        return $response;
    });

    // ==============================
    // ROUTES API RESTFUL
    // ==============================

    $app->get('/praticiens/recherche', RechercherPraticienAction::class);
    $app->get('/praticiens', ListerPraticienAction::class);
    $app->get('/praticiens/{id}', AfficherPraticienAction::class);

    return $app;
};
