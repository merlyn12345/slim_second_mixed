<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container;


AppFactory::setContainer($container);
$settings = require __DIR__.'/../app/settings.php';  // returnt eine callback-function die das CI als Parameter hat
$settings($container);

$app = AppFactory::create();



$container->set('db', function()
use ($app)
{
    $setting = $app->getContainer()->get('settings');
    $pdo = new PDO('mysql:host=' . $setting['dbHost']. ';dbname=' . $setting['dbName'], $setting['dbUser'] , $setting['dbPass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
});

$container->set('templating', function(){
    return new Mustache_Engine([
        'loader' => new Mustache_Loader_FilesystemLoader(
            __DIR__ . '/../templates',
            ['extension' => ''] )
    ]);
});

$container->set('session', function(){
    return new \SlimSession\Helper();
});



$app->add(new \Slim\Middleware\Session);

$middleware = require __DIR__ . '/../app/Middleware/middleware.php';
$middleware($app);

/* Routes */

$app->get('/logout', '\App\Controller\AuthController:logout');

$app->group('/secure', function($app){
    $app->get('', '\App\Controller\SecureController:home');
    $app->get('/status', '\App\Controller\SecureController:status');
    $app->get('/shop', '\App\Controller\ShopController:default');
    $app->get('/submit', '\App\Controller\ShopController:kategorien');
    $app->post('/submit', '\App\Controller\ShopController:submit');
    $app->get('/selectdata/{kategorie}', '\App\Controller\ShopController:items');
    $app->get('/details/{id:[0-9]+}', '\App\Controller\ShopController:details');
})->add(new \App\Middleware\Authenticate($app->getContainer()->get('session')));



/* scripts, css, n images.... */
$app->get('/jquery', function ($request, $response){
    $file = __DIR__  . "/../javascript/jquery.js";
    if (!file_exists($file)) {
        die("file:$file");
    }
    $jquery = file_get_contents($file);
    if ($jquery === false) {
        die("error getting jquery");
    }
    $response->write($jquery);
    return $response->withHeader('Content-Type', 'text/javascript');
});


$app->get('/submitform', function ($request, $response){
    $file = __DIR__  . "/../javascript/submitform.js";
    if (!file_exists($file)) {
        die("file:$file");
    }
    $submitform = file_get_contents($file);
    if ($submitform === false) {
        die("error getting submitform");
    }
    $response->write($submitform);
    return $response->withHeader('Content-Type', 'text/javascript');
});


$app->any('/', '\App\Controller\AuthController:login');
$app->run();