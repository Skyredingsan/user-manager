<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UserManager\Repositories\RepositoryFactory;
use UserManager\Services\UserService;
use UserManager\Http\UserController;
use UserManager\Exceptions\ApiException;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$repository = RepositoryFactory::create($_ENV['DB_SOURCE'] ?? 'json');
$service = new UserService($repository);
$controller = new UserController($service);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $route = match(true) {
        $method === 'GET' && $path === '/users' => 'list',
        $method === 'POST' && $path === '/users' => 'create',
        $method === 'DELETE' && preg_match('/^\/users\/(\d+)$/', $path, $matches) => 'delete',
        $path === '/' => 'docs',
        default => 'not_found'
    };

    $response = match($route) {
        'list' => function() use ($controller) {
            $result = $controller->list();
            http_response_code(200);
            return [
                'success' => true,
                'data' => $result['data'],
                'count' => $result['count'],
                '_links' => [
                    'self' => ['href' => '/users'],
                    'create' => ['href' => '/users', 'method' => 'POST'],
                    'docs' => ['href' => '/']
                ]
            ];
        },

        'create' => function() use ($controller) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON format');
            }

            $result = $controller->create($input);
            http_response_code(201);

            return [
                'success' => true,
                'data' => $result['data'],
                'message' => $result['message'],
                '_links' => [
                    'self' => ['href' => '/users/' . $result['data']['id']],
                    'list' => ['href' => '/users'],
                    'delete' => ['href' => '/users/' . $result['data']['id'], 'method' => 'DELETE']
                ]
            ];
        },

        'delete' => function() use ($controller, $matches) {
            $id = (int)$matches[1];
            $result = $controller->delete($id);
            http_response_code(200);

            return [
                'success' => true,
                'message' => $result['message'],
                '_links' => [
                    'list' => ['href' => '/users']
                ]
            ];
        },

        'docs' => function() {
            http_response_code(200);
            return [
                'success' => true,
                'message' => 'User Manager API',
                '_links' => [
                    'users' => ['href' => '/users', 'method' => 'GET'],
                    'create_user' => ['href' => '/users', 'method' => 'POST'],
                    'delete_user' => ['href' => '/users/{id}', 'method' => 'DELETE']
                ]
            ];
        },

        'not_found' => function() {
            throw new \InvalidArgumentException('Endpoint not found');
        }
    };

    $responseData = $response();

} catch (ApiException $e) {
    http_response_code($e->getCode() ?: 400);
    $responseData = [
        'success' => false,
        'error' => $e->getCode(),
        'message' => $e->getMessage(),
        '_links' => [
            'docs' => ['href' => '/']
        ]
    ];
} catch (\InvalidArgumentException $e) {
    http_response_code(404);
    $responseData = [
        'success' => false,
        'error' => 404,
        'message' => $e->getMessage(),
        '_links' => [
            'docs' => ['href' => '/']
        ]
    ];
} catch (\Throwable $e) {
    http_response_code(500);
    $responseData = [
        'success' => false,
        'error' => 500,
        'message' => 'Internal server error',
        '_links' => [
            'docs' => ['href' => '/']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);