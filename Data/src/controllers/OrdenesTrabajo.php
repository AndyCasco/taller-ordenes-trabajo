<?php

declare(strict_types=1);

namespace App\controllers;

use App\Models\OrdenesTrabajoModel;
use Illuminate\Database\QueryException;
use PDO;
use PDOException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrdenesTrabajo
{
	public function __construct(private ContainerInterface $container)
	{
	}

	public function read(Request $request, Response $response, array $args): Response
	{
		$this->container->get('eloquent');

		if (isset($args['id'])) {
			$orden = OrdenesTrabajoModel::find($args['id']);

			if ($orden === null) {
				return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
			}

			return $this->json($response, ['data' => $orden], 200);
		}

		return $this->json($response, [
			'data' => OrdenesTrabajoModel::query()->get()->toArray(),
		], 200);
	}

	public function create(Request $request, Response $response, array $args): Response
	{
		$this->container->get('eloquent');
		$data = $request->getParsedBody();
		$data = is_array($data) ? $data : [];

		try {
			$orden = OrdenesTrabajoModel::create($data);
		} catch (QueryException $exception) {
			return $this->json($response, [
				'error' => 'No se pudo registrar la orden de trabajo: los datos entran en conflicto con la base de datos.',
			], 409);
		}

		return $this->json($response, ['data' => $orden->fresh()->toArray()], 201);
	}

	public function update(Request $request, Response $response, array $args): Response
	{
		$this->container->get('eloquent');
		$orden = OrdenesTrabajoModel::find($args['id'] ?? null);

		if ($orden === null) {
			return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
		}

		$data = $request->getParsedBody();
		$data = is_array($data) ? $data : [];

		try {
			$orden->fill($data);
			$orden->save();
		} catch (QueryException $exception) {
			return $this->json($response, [
				'error' => 'No se pudo actualizar la orden de trabajo: los datos entran en conflicto con la base de datos.',
			], 409);
		}

		return $this->json($response, ['data' => $orden->fresh()->toArray()], 200);
	}

	public function delete(Request $request, Response $response, array $args): Response
	{
		$conexion = $this->container->get('base_datos');

		try {
			$sentencia = $conexion->prepare('DELETE FROM ordenes_trabajo WHERE id = :id');
			$sentencia->bindValue(':id', (int) ($args['id'] ?? 0), PDO::PARAM_INT);
			$sentencia->execute();
		} catch (PDOException $exception) {
			return $this->json($response, [
				'error' => 'No se pudo eliminar la orden de trabajo por un conflicto en la base de datos.',
			], 409);
		}

		if ($sentencia->rowCount() === 0) {
			return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
		}

		return $response->withStatus(204);
	}

	private function json(Response $response, array $payload, int $status): Response
	{
		$response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

		return $response
			->withHeader('Content-Type', 'application/json; charset=utf-8')
			->withStatus($status);
	}
}
