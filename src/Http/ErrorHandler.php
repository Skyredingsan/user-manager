<?php

declare(strict_types=1);

namespace UserManager\Http;

use UserManager\Exceptions\ApiException;

class ErrorHandler
{
    private bool $isDebug;

    public function __construct(bool $isDebug = false)
    {
        $this->isDebug = $isDebug;
    }

    public function handle(\Throwable $e): void
    {
        header('Content-Type: application/json');

        if ($e instanceof ApiException) {
            $this->handleApiException($e);
            return;
        }

        $this->handleUnknownException($e);
    }

    private function handleApiException(ApiException $e): void
    {
        http_response_code($e->getStatusCode());

        echo json_encode([
            'success' => false,
            'error' => $e->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function handleUnknownException(\Throwable $e): void
    {
        http_response_code(500);

        $response = [
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'Internal server error',
            ],
        ];

        if ($this->isDebug) {
            $response['error']['debug'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        error_log($e->getMessage());
    }
}