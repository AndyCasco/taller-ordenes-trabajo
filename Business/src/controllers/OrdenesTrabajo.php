<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Services\DataService;
use App\Validators\OrdenesTrabajoValidator;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class OrdenesTrabajo {
    public function __construct(private ContainerInterface $container)
	{
	}

    public function read(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $path = isset($args['id']) ? 'ordenes_trabajo/' . rawurlencode($args['id']) : 'ordenes_trabajo';
            $upstream = $dataService->get($path);
            $response->getBody()->write((string) $upstream->getBody());
            return $response->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $data = json_decode((string) $request->getBody(), true);
            $data = is_array($data) ? $data : [];

            $errores = (new OrdenesTrabajoValidator())->validate($data);
            if ($errores !== []) {
                return $this->json($response, ['errors' => $errores], 409);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->post('ordenes_trabajo', json_encode($data, JSON_THROW_ON_ERROR));
            $response->getBody()->write((string) $upstream->getBody());
            return $response->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $request->getBody()->rewind();
            $data = json_decode((string) $request->getBody(), true);
            $data = is_array($data) ? $data : [];

            $errores = (new OrdenesTrabajoValidator())->validatePartial($data);
            if ($errores !== []) {
                return $this->json($response, ['errors' => $errores], 409);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->put('ordenes_trabajo/' . rawurlencode($args['id']), json_encode($data, JSON_THROW_ON_ERROR));
            $response->getBody()->write((string) $upstream->getBody());
            return $response->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->delete('ordenes_trabajo/' . rawurlencode($args['id']));

            if ($upstream->getStatusCode() === 204) {
                return $response->withStatus(204);
            }

            $response->getBody()->write((string) $upstream->getBody());
            return $response->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
                ->withStatus($upstream->getStatusCode());
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no esta disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

   private function json(Response $response, array $payload, int $status): Response {
         $response-> getBody()->write(json_encode($payload,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
    
    }


}
