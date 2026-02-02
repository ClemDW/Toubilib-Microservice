<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InputRendezVousMiddleware implements MiddlewareInterface
{
	public function process(Request $request, RequestHandlerInterface $handler): Response
	{
		$data = $request->getParsedBody();

		// Champs obligatoires
		$requiredFields = ['dateHeureDebut', 'dateHeureFin', 'praticienId', 'patientId', 'motifVisite'];
		foreach ($requiredFields as $field) {
			if (empty($data[$field])) {
				return $this->errorResponse("Champ obligatoire manquant : $field");
			}
		}

		// Validation des formats
		if (!$this->isValidDateTime($data['dateHeureDebut'])) {
			return $this->errorResponse('Format dateHeureDebut invalide');
		}
		if (!$this->isValidDateTime($data['dateHeureFin'])) {
			return $this->errorResponse('Format dateHeureFin invalide');
		}
		if (!$this->isValidUuid($data['praticienId'])) {
			return $this->errorResponse('Format praticienId invalide');
		}
		if (!$this->isValidUuid($data['patientId'])) {
			return $this->errorResponse('Format patientId invalide');
		}
		if (!is_string($data['motifVisite']) || trim($data['motifVisite']) === '') {
			return $this->errorResponse('motifVisite doit être une chaîne non vide');
		}

		// Protection des données
		$data['motifVisite'] = $this->sanitizeString($data['motifVisite']);
		if (strlen($data['motifVisite']) > 255) {
			return $this->errorResponse('motifVisite trop long');
		}

		// Ajout des données nettoyées dans la requête
		$request = $request->withParsedBody($data);

		// Les validations métier doivent être faites dans le service métier (ServiceRdv)
		return $handler->handle($request);
	}

	private function isValidDateTime($date): bool
	{
		$d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
		return $d && $d->format('Y-m-d H:i:s') === $date;
	}

	private function isValidUuid($uuid): bool
	{
		return preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid);
	}

	private function sanitizeString($str): string
	{
		$str = strip_tags($str);
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		return $str;
	}

	private function errorResponse($message): Response
	{
		$response = new \Slim\Psr7\Response();
		$response->getBody()->write(json_encode(['error' => $message]));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
	}
}
