<?php

use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\FrankenPhp\FrankenPhpClient;
use Laravel\Octane\RequestContext;
use Laravel\Octane\Stream;
use Laravel\Octane\Worker;
use Symfony\Component\HttpFoundation\Response;

if ((! ($_SERVER['FRANKENPHP_WORKER'] ?? false)) || ! function_exists('frankenphp_handle_request')) {
    require __DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';

    return;
}

ignore_user_abort(true);

$basePath = $_SERVER['APP_BASE_PATH'] ?? $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

ini_set('display_errors', 'stderr');
$_ENV['APP_RUNNING_IN_CONSOLE'] = false;

require_once "{$basePath}/vendor/autoload.php";

$frankenPhpClient = new FrankenPhpClient();

$worker = tap(new Worker(
    new ApplicationFactory($basePath), $frankenPhpClient
))->boot();

$requestCount = 0;
$maxRequests = $_ENV['MAX_REQUESTS'] ?? $_SERVER['MAX_REQUESTS'] ?? 1000;
$requestMaxExecutionTime = $_ENV['REQUEST_MAX_EXECUTION_TIME'] ?? $_SERVER['REQUEST_MAX_EXECUTION_TIME'] ?? null;

if (PHP_OS_FAMILY === 'Linux' && ! is_null($requestMaxExecutionTime)) {
    set_time_limit((int) $requestMaxExecutionTime);
}

try {
    $handleRequest = static function () use ($worker, $frankenPhpClient) {
        $debugMode = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'false';

        try {
            [$request, $context] = $frankenPhpClient->marshalRequest(new RequestContext());

            $worker->handle($request, $context);
        } catch (Throwable $e) {
            if ($worker) {
                report($e);
            }

            $response = new Response(
                $debugMode === 'true' ? (string) $e : 'Internal Server Error',
                500,
                [
                    'Status' => '500 Internal Server Error',
                    'Content-Type' => 'text/plain',
                ],
            );

            $response->send();

            Stream::shutdown($e);
        }
    };

    while ($requestCount < $maxRequests && frankenphp_handle_request($handleRequest)) {
        $requestCount++;
    }
} finally {
    $worker?->terminate();

    gc_collect_cycles();
}
