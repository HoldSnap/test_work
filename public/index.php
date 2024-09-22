<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

// Middleware для включения CORS
$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $response = $handler->handle($request);
    return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

$pdo = new PDO('mysql:host=127.0.0.1;dbname=comments_db', 'root', 'root_password');

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Welcome to the comments API!");
    return $response;
});

$app->get('/comments', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($comments));
    return $response->withHeader('Content-Type', 'application/json');
});



$app->options('/comments/{id}', function (Request $request, Response $response) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

$app->options('/comments', function (Request $request, Response $response) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

$app->run();
