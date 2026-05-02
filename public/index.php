<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UserManager\Repositories\RepositoryFactory;
use UserManager\Services\UserService;
use UserManager\Http\UserController;
use UserManager\Http\ErrorHandler;
use UserManager\Exceptions\NotFoundException;
use UserManager\Exceptions\ValidationException;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$repository = RepositoryFactory::create($_ENV['DB_SOURCE'] ?? 'json');
$service = new UserService($repository);
$controller = new UserController($service);

$errorHandler = new ErrorHandler($_ENV['APP_DEBUG'] === 'true');

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
            ];
        },

        'create' => function() use ($controller) {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ValidationException([
                    'body' => 'Invalid JSON format'
                ]);
            }

            $result = $controller->create($input);

            http_response_code(201);

            return [
                'success' => true,
                'data' => $result['data'],
                'message' => $result['message'],
            ];
        },

        'delete' => function() use ($controller, $matches) {
            $id = (int)$matches[1];

            $result = $controller->delete($id);

            http_response_code(200);

            return [
                'success' => true,
                'message' => $result['message'],
            ];
        },

        'docs' => function() {
            http_response_code(200);

            return [
                'success' => true,
                'message' => 'User Manager API'
            ];
        },

        'not_found' => function() {
            throw new NotFoundException('Endpoint not found');
        }
    };

    header('Content-Type: application/json');
    echo json_encode($response(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    $errorHandler->handle($e);
}