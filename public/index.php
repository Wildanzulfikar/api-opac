<?php
declare(strict_types=1);

use DI\Container;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(
    filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    true,
    true
);

$app->add(function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('Content-Type', 'application/json');
});

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database'  => $_ENV['DB_DATABASE'] ?? 'opac',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? 'wildanzulfikar110924',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$routesPath = __DIR__ . '/../app/Routes/routes.php';
if (!file_exists($routesPath)) {
    $routesPath = __DIR__ . '/../app/routes.php';
}

if (file_exists($routesPath)) {
    (require $routesPath)($app);
} else {

    $app->get('/', function ($req, $res) {
        $res->getBody()->write(json_encode(['status' => 'no routes found']));
        return $res->withHeader('Content-Type', 'application/json');
    });
}

// run app
$app->run();
