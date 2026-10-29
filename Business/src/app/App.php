<?php
use Slim\Factory\AppFactory;
use DI\Container;


require __DIR__ . '/../../vendor/autoload.php';

$container = new Container();
$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();


require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/config.php';

$app->addErrorMiddleware(false, false, false);

$app->run();
