<?php

declare(strict_types=1);


require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UserManager\Repositories\RepositoryFactory;
use UserManager\Http\UserController;


$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


$dbSource = $_ENV['DB_SOURCE'] ?? 'json';
$repository = RepositoryFactory::create($dbSource);
$controller = new UserController($repository);


$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


try {
    if ($method === 'GET' && $path === '/users') {
        $response = $controller->list();
        http_response_code(200);

    } elseif ($method === 'POST' && $path === '/users') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = ['success' => false, 'message' => 'Invalid JSON format'];
            http_response_code(400);
        } else {
            $response = $controller->create($input);
            http_response_code($response['success'] ? 201 : 400);
        }

    } elseif ($method === 'DELETE' && preg_match('/^\/users\/(\d+)$/', $path, $matches)) {
        $id = (int)$matches[1];
        $response = $controller->delete($id);
        http_response_code($response['success'] ? 200 : 404);

    } elseif ($path === '/') {
        $response = [
            'success' => true,
            'message' => 'User Manager API',
            'endpoints' => [
                'GET /users' => 'List all users',
                'POST /users' => 'Create user (JSON: first_name, last_name, email)',
                'DELETE /users/{id}' => 'Delete user by ID'
            ]
        ];
        http_response_code(200);

    } else {
        $response = ['success' => false, 'message' => 'Endpoint not found'];
        http_response_code(404);
    }

} catch (\Throwable $e) {
    $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);