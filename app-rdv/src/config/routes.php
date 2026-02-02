<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;

//use toubilib\api\actions\ListerPraticienAction;
//use toubilib\api\actions\RechercherPraticienAction;
//use toubilib\api\actions\AfficherPraticienAction;
use toubilib\api\actions\ListerPraticienRdvAction;
use toubilib\api\actions\ListerHistoriquePatientAction;
use toubilib\api\actions\ConsulterRdvAction;
use toubilib\api\actions\CreerRdvAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\actions\CreerIndisponibiliteAction;
use toubilib\api\middlewares\ValidateCreerRdvMiddleware;
use toubilib\api\actions\AnnulerRdvAction;
use toubilib\api\actions\ConsulterAgendaAction;
use toubilib\api\middlewares\AuthzMiddleware;

//use toubilib\api\actions\SigninAction;

return function (App $app): App {

    // ==============================
    // UI HTML de test
    // ==============================
    $app->get('/', function (Request $request, Response $response) {
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Test API REST Toubilib</title>
        </head>
        <body>
            <h1>Client de test API REST</h1>

            <h2>Liste des praticiens</h2>
            <button onclick="callApi('/praticiens', 'listePraticiens')">Charger</button>
            <pre id="listePraticiens"></pre>

            <h2>Détails praticien</h2>
            <input type="text" id="praticienId" placeholder="ID praticien">
            <button onclick="callApi('/praticiens/' + document.getElementById('praticienId').value, 'detailsPraticien')">Voir</button>
            <pre id="detailsPraticien"></pre>

            <h2>Liste des RDV d’un praticien</h2>
            <input type="text" id="praticienRdvId" placeholder="ID praticien">
            <input type="date" id="dateDebut">
            <input type="date" id="dateFin">
            <button onclick="callApi('/praticiens/' + document.getElementById('praticienRdvId').value + '/rdvs?date_debut=' + document.getElementById('dateDebut').value + '&date_fin=' + document.getElementById('dateFin').value, 'rdvsPraticien')">Lister</button>
            <pre id="rdvsPraticien"></pre>

            <h2>Consulter un RDV</h2>
            <input type="text" id="rdvId" placeholder="ID RDV">
            <button onclick="callApi('/rdvs/' + document.getElementById('rdvId').value, 'rdvDetails', {auth: true})">Consulter</button>
            <pre id="rdvDetails"></pre>

            <h2>Créer un RDV</h2>
            <form id="createRdvForm">
                <input type="text" name="praticienId" placeholder="ID praticien" required><br>
                <input type="text" name="patientId" placeholder="ID patient" required><br>
                <input type="datetime-local" name="dateHeure" required><br>
                <input type="text" name="motifVisite" placeholder="Motif" required><br>
                <input type="number" name="duree" placeholder="Durée (minutes)" required><br>
                <button type="submit">Créer</button>
            </form>
            <pre id="rdvCree"></pre>

            <h2>Annuler un RDV</h2>
            <input type="text" id="rdvIdAnnuler" placeholder="ID RDV">
            <button onclick="deleteRdv()">Annuler</button>
            <pre id="rdvAnnule"></pre>

            <h2>Agenda d’un praticien</h2>
            <input type="text" id="praticienAgendaId" placeholder="ID praticien">
            <input type="date" id="agendaDebut">
            <input type="date" id="agendaFin">
            <button onclick="callApi('/praticiens/' + document.getElementById('praticienAgendaId').value + '/agenda?dateDebut=' + document.getElementById('agendaDebut').value + '&dateFin=' + document.getElementById('agendaFin').value, 'agendaPraticien')">Voir</button>
            <pre id="agendaPraticien"></pre>


            <h2>Connexion</h2>
            <form id="signinForm" method="post" onsubmit="return false;">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
            </form>
            <pre id="signinResult"></pre>


            <script>
                // Stockage du token JWT
                let accessToken = null;

                async function callApi(url, targetId, options = {}) {
                    try {
                        let fetchOptions = options;
                        if (!fetchOptions.headers) fetchOptions.headers = {};
                        // Ajoute le token JWT si demandé
                        if (options.auth && accessToken) {
                            fetchOptions.headers['Authorization'] = 'Bearer ' + accessToken;
                        }
                        const res = await fetch(url, fetchOptions);
                        const data = await res.json();
                        document.getElementById(targetId).textContent = JSON.stringify(data, null, 2);
                    } catch (err) {
                        document.getElementById(targetId).textContent = "Erreur: " + err;
                    }
                }

                document.getElementById("createRdvForm").addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const jsonData = Object.fromEntries(formData.entries());
                    let headers = { 'Content-Type': 'application/json' };
                    if (accessToken) headers['Authorization'] = 'Bearer ' + accessToken;
                    const res = await fetch('/rdvs', {
                        method: 'POST',
                        headers,
                        body: JSON.stringify(jsonData)
                    });
                    document.getElementById("rdvCree").textContent = JSON.stringify(await res.json(), null, 2);
                });

                async function deleteRdv() {
                    const id = document.getElementById("rdvIdAnnuler").value;
                    let headers = {};
                    if (accessToken) headers['Authorization'] = 'Bearer ' + accessToken;
                    const res = await fetch('/rdvs/' + id, { method: 'DELETE', headers });
                    document.getElementById("rdvAnnule").textContent = JSON.stringify(await res.json(), null, 2);
                }

                // === Formulaire Signin ===
                document.getElementById("signinForm").addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const jsonData = Object.fromEntries(formData.entries());
                    const res = await fetch('/signin', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(jsonData)
                    });
                    const data = await res.json();
                    document.getElementById("signinResult").textContent = JSON.stringify(data, null, 2);
                    if (data.access_token) {
                        accessToken = data.access_token;
                    }
                });
            </script>

        <h2>Rechercher un praticien, en fonction d’une spécialité et/ou d’une ville d’exercice</h2>
            <input type="text" id="searchSpecialite" placeholder="Spécialité">
            <input type="text" id="searchVille" placeholder="Ville">
            <button onclick="callApi('/praticiens/recherche?specialite=' + document.getElementById('searchSpecialite').value + '&ville=' + document.getElementById('searchVille').value, 'searchResult')">Rechercher</button>
            <pre id="searchResult"></pre>

        <h2>Historique des consultations d’un patient</h2>
            <input type="text" id="patientId" placeholder="ID patient">
            <button onclick="callApi('/patients/' + document.getElementById('patientId').value + '/rdvs', 'patientHistory')">Voir l'historique</button>
            <pre id="patientHistory"></pre>

        <h2>Inscription Patient</h2>
            <form id="createPatientForm">
                <input type="text" name="nom" placeholder="Nom" required><br>
                <input type="text" name="prenom" placeholder="Prénom" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="text" name="telephone" placeholder="Téléphone" required><br>
                <input type="date" name="dateNaissance" placeholder="Date de naissance"><br>
                <input type="text" name="adresse" placeholder="Adresse"><br>
                <input type="text" name="codePostal" placeholder="Code Postal"><br>
                <input type="text" name="ville" placeholder="Ville"><br>
                <button type="submit">S'inscrire</button>
            </form>
            <pre id="patientCreated"></pre>

            <h2>Ajouter une indisponibilité (Praticien)</h2>
            <form id="createIndispoForm">
                <input type="text" name="praticienId" placeholder="ID praticien" required><br>
                <input type="datetime-local" name="dateDebut" required><br>
                <input type="datetime-local" name="dateFin" required><br>
                <input type="text" name="motif" placeholder="Motif"><br>
                <button type="submit">Ajouter</button>
            </form>
            <pre id="indispoCreated"></pre>

            <h2>Modifier le statut d'un RDV</h2>
            <form id="updateRdvStatusForm">
                <input type="text" name="rdvId" placeholder="ID RDV" required><br>
                <select name="status" required>
                    <option value="honore">Honoré</option>
                    <option value="non_honore">Non Honoré</option>
                </select>
                <button type="submit">Mettre à jour</button>
            </form>
            <pre id="rdvStatusUpdated"></pre>

            <script>

                document.getElementById("createPatientForm").addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const jsonData = Object.fromEntries(formData.entries());
                    const res = await fetch('/patients', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(jsonData)
                    });
                    document.getElementById("patientCreated").textContent = JSON.stringify(await res.json(), null, 2);
                });

                document.getElementById("createIndispoForm").addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const jsonData = Object.fromEntries(formData.entries());
                    const res = await fetch('/praticiens/indisponibilites', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(jsonData)
                    });
                    document.getElementById("indispoCreated").textContent = JSON.stringify(await res.json(), null, 2);
                });

                document.getElementById("updateRdvStatusForm").addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const rdvId = formData.get('rdvId');
                    const status = formData.get('status');
                    
                    let headers = { 'Content-Type': 'application/json' };
                    if (accessToken) headers['Authorization'] = 'Bearer ' + accessToken;

                    const res = await fetch('/rdvs/' + rdvId + '/status', {
                        method: 'PATCH',
                        headers: headers,
                        body: JSON.stringify({ status: status })
                    });
                    document.getElementById("rdvStatusUpdated").textContent = JSON.stringify(await res.json(), null, 2);
                });
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

    //$app->get('/praticiens/recherche', RechercherPraticienAction::class);
    //$app->get('/praticiens', ListerPraticienAction::class);
    //$app->get('/praticiens/{id}', AfficherPraticienAction::class);
    $app->get('/praticiens/{id}/rdvs', ListerPraticienRdvAction::class);
    
    // Route protégée : accès à l'agenda réservé au praticien concerné
    $app->get('/praticiens/{id}/agenda', ConsulterAgendaAction::class)
        ->add(AuthzMiddleware::class);

    $app->get('/patients/{id}/rdvs', ListerHistoriquePatientAction::class);
    $app->post('/patients', CreerPatientAction::class);

    $app->post('/praticiens/indisponibilites', CreerIndisponibiliteAction::class);

    // Route publique
    //$app->post('/signin', SigninAction::class);

    // Routes protégées par AuthzMiddleware
    $app->group('/rdvs', function ($group) {
        // Accès au détail d'un RDV : réservé au patient ou praticien concerné
        $group->get('/{id}', ConsulterRdvAction::class)
            ->add(AuthzMiddleware::class);
        // Création d'un RDV : réservé au praticien ou patient concerné
        $group->post('', CreerRdvAction::class)
            ->add(AuthzMiddleware::class)
            ->add(ValidateCreerRdvMiddleware::class);
        $group->delete('/{id}', AnnulerRdvAction::class);
        $group->patch('/{id}/status', \toubilib\api\actions\MajStatusRdvAction::class);
    });


    return $app;
};
