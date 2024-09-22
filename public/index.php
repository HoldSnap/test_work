<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

require __DIR__ . '/cors.php';
require __DIR__ . '/db.php';
require __DIR__ . '/routes.php';

$app->run();
