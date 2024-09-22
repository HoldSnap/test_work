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

$app->post('/comments', function (Request $request, Response $response) use ($pdo) {
    $data = json_decode($request->getBody()->getContents(), true);

    if (strpos($request->getHeaderLine('User-Agent'), 'Postman') === false) {
        $recaptcha = $data['recaptcha'];
        $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptcha}");
        $responseData = json_decode($verifyResponse);

        if (!$responseData->success) {
            return $response->withStatus(400)->getBody()->write('Ошибка проверки reCAPTCHA');
        }
    }

    $stmt = $pdo->prepare("INSERT INTO comments (name, text) VALUES (:name, :text)");
    $stmt->execute(['name' => $data['name'], 'text' => $data['text']]);

    $responseData = ['status' => 'success'];
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});



$app->delete('/comments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $id = $args['id'];
    error_log("Deleting comment with ID: $id");
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        return $response->withStatus(404)->write('Comment not found');
    }

    return $response->withStatus(204);
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
