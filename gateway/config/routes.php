<?php

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\gateway\application\actions\GatewayAction;
use toubilib\gateway\application\middlewares\CorsMiddleware;
use toubilib\gateway\application\middlewares\AuthnMiddleware;
use toubilib\gateway\application\actions\PraticienGatewayAction;
use toubilib\gateway\application\actions\RdvGatewayAction;
use toubilib\gateway\application\actions\AuthGatewayAction;

return function (App $app) {

    // Interface de Test HTML
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

    <nav>
        <button onclick="showSection('section-auth')">Authentification</button>
        <button onclick="showSection('section-praticien')">Praticiens</button>
        <button onclick="showSection('section-patient')">Patients</button>
        <button onclick="showSection('section-rdv')">Rendez-vous</button>
    </nav>

    <hr>

    <div id="section-auth" class="api-section">
        <h2>Connexion</h2>
        <form id="signinForm" method="post" onsubmit="return false;">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <pre id="signinResult"></pre>

        <h2>Valider un token JWT</h2>
        <button onclick="
            const tokenToValidate = accessToken;
            callApi('/tokens/validate', 'tokenValidation', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + tokenToValidate }
        })">Valider le token courant</button>
        <pre id="tokenValidation"></pre>
    </div>

    <div id="section-praticien" class="api-section" style="display:none;">
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

        <h2>Agenda d’un praticien</h2>
        <input type="text" id="praticienAgendaId" placeholder="ID praticien">
        <input type="date" id="agendaDebut">
        <input type="date" id="agendaFin">
        <button onclick="callApi('/praticiens/' + document.getElementById('praticienAgendaId').value + '/agenda?dateDebut=' + document.getElementById('agendaDebut').value + '&dateFin=' + document.getElementById('agendaFin').value, 'agendaPraticien', {auth: true})">Voir</button>
        <pre id="agendaPraticien"></pre>

        <h2>Ajouter une indisponibilité</h2>
        <form id="createIndispoForm">
            <input type="text" name="praticienId" placeholder="ID praticien" required><br>
            <input type="datetime-local" name="dateDebut" required><br>
            <input type="datetime-local" name="dateFin" required><br>
            <input type="text" name="motif" placeholder="Motif"><br>
            <button type="submit">Ajouter</button>
        </form>
        <pre id="indispoCreated"></pre>
    </div>

    <div id="section-patient" class="api-section" style="display:none;">
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

        <h2>Historique des consultations d’un patient</h2>
        <input type="text" id="patientIdHistory" placeholder="ID patient">
        <button onclick="callApi('/patients/' + document.getElementById('patientIdHistory').value + '/rdvs', 'patientHistory')">Voir l'historique</button>
        <pre id="patientHistory"></pre>
    </div>

    <div id="section-rdv" class="api-section" style="display:none;">
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

        <h2>Modifier le statut d'un RDV</h2>
        <form id="updateRdvStatusForm">
            <input type="text" name="rdvIdStatus" placeholder="ID RDV" required><br>
            <select name="status" required>
                <option value="honore">Honoré</option>
                <option value="non_honore">Non Honoré</option>
            </select>
            <button type="submit">Mettre à jour</button>
        </form>
        <pre id="rdvStatusUpdated"></pre>

        <h2>Annuler un RDV</h2>
        <input type="text" id="rdvIdAnnuler" placeholder="ID RDV">
        <button onclick="deleteRdv()">Annuler</button>
        <pre id="rdvAnnule"></pre>
    </div>

    <script>
        // --- Logique d'affichage des sections ---
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.api-section');
            sections.forEach(sec => sec.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }

        // --- Ton code JavaScript original (inchangé) ---
        let accessToken = null;

        async function callApi(url, targetId, options = {}) {
            try {
                let fetchOptions = options;
                if (!fetchOptions.headers) fetchOptions.headers = {};
                
                if (options.auth && !accessToken) {
                     document.getElementById(targetId).textContent = "Erreur : Vous devez être connecté pour accéder à cette fonctionnalité.";
                     return;
                }

                if (options.auth && accessToken) {
                    fetchOptions.headers['Authorization'] = 'Bearer ' + accessToken;
                    fetchOptions.headers['X-Auth-Token'] = accessToken;
                }
                const res = await fetch(url, fetchOptions);
                
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    document.getElementById(targetId).textContent = JSON.stringify(data, null, 2);
                } catch(e) {
                    document.getElementById(targetId).textContent = text;
                }
            } catch (err) {
                document.getElementById(targetId).textContent = "Erreur: " + err;
            }
        }

        document.getElementById("createRdvForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const jsonData = {};
            formData.forEach((value, key) => jsonData[key] = value);

            let headers = { 'Content-Type': 'application/json' };
            if (accessToken) {
                headers['Authorization'] = 'Bearer ' + accessToken;
                headers['X-Auth-Token'] = accessToken;
            } 
            const res = await fetch('/rdvs', {
                method: 'POST',
                headers,
                body: JSON.stringify(jsonData)
            });
            
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                document.getElementById("rdvCree").textContent = JSON.stringify(data, null, 2);
            } catch(e) {
                document.getElementById("rdvCree").textContent = text;
            }
        });

        async function deleteRdv() {
            const id = document.getElementById("rdvIdAnnuler").value;
            let headers = {};
            if (accessToken) {
                headers['Authorization'] = 'Bearer ' + accessToken;
                headers['X-Auth-Token'] = accessToken;
            }
            const res = await fetch('/rdvs/' + id, { method: 'DELETE', headers });
            document.getElementById("rdvAnnule").textContent = await res.text();
        }

        document.getElementById("signinForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const jsonData = {};
            formData.forEach((value, key) => jsonData[key] = value);
            const res = await fetch('/auth/signin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const data = await res.json();
            document.getElementById("signinResult").textContent = JSON.stringify(data, null, 2);
            
            let token = null;
            if (data.data && data.data.accessToken) token = data.data.accessToken;
            else if (data.data && data.data.access_token) token = data.data.access_token;
            else if (data.access_token) token = data.access_token;

            if (token) {
                accessToken = token;
            }
        });

        document.getElementById("createPatientForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const jsonData = {};
            formData.forEach((value, key) => jsonData[key] = value);
            const res = await fetch('/patients', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                document.getElementById("patientCreated").textContent = JSON.stringify(data, null, 2);
            } catch(e) {
                document.getElementById("patientCreated").textContent = text;
            }
        });

        document.getElementById("createIndispoForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const jsonData = {};
            formData.forEach((value, key) => jsonData[key] = value);
            const res = await fetch('/praticiens/indisponibilites', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                document.getElementById("indispoCreated").textContent = JSON.stringify(data, null, 2);
            } catch(e) {
                document.getElementById("indispoCreated").textContent = text;
            }
        });

        document.getElementById("updateRdvStatusForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const rdvId = formData.get('rdvIdStatus');
            const status = formData.get('status');
            
            let headers = { 'Content-Type': 'application/json' };
            if (accessToken) {
                headers['Authorization'] = 'Bearer ' + accessToken;
                headers['X-Auth-Token'] = accessToken;
            }

            const res = await fetch('/rdvs/' + rdvId + '/status', {
                method: 'PATCH',
                headers: headers,
                body: JSON.stringify({ status: status })
            });
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                document.getElementById("rdvStatusUpdated").textContent = JSON.stringify(data, null, 2);
            } catch(e) {
                document.getElementById("rdvStatusUpdated").textContent = text;
            }
        });
    </script>
</body>
</html>
HTML;

        $response->getBody()->write($html);
        return $response;
    });

    $app->options('/{routes:.*}', function ($request, $response) {
        return $response;
    });

    $app->add(CorsMiddleware::class);

    $app->get('/praticiens', PraticienGatewayAction::class);
    $app->get('/praticiens/{id}', PraticienGatewayAction::class);

    // Routes RDV
    $app->get('/praticiens/{id}/rdvs', RdvGatewayAction::class);
    $app->get('/praticiens/{id}/agenda', RdvGatewayAction::class)
        ->add(AuthnMiddleware::class);
    $app->post('/praticiens/indisponibilites', RdvGatewayAction::class);

    $app->get('/patients/{id}/rdvs', RdvGatewayAction::class);
    $app->post('/patients', RdvGatewayAction::class);

    // Auth Routes
    $app->post('/auth/login', AuthGatewayAction::class);
    $app->post('/auth/signin', AuthGatewayAction::class);
    $app->post('/signin', AuthGatewayAction::class);
    $app->post('/tokens/validate', AuthGatewayAction::class);

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/rdvs', RdvGatewayAction::class)
        ->add(AuthnMiddleware::class);
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/rdvs/{routes:.*}', RdvGatewayAction::class)
        ->add(AuthnMiddleware::class);
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.*}', GatewayAction::class);

};
