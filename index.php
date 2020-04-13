<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'Task.php';

use Relay\Relay;

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$loader = new Twig_Loader_Filesystem('.');
$twig = new \Twig_Environment($loader, array(
    'debug' => true,
    'cache' => false,
));
$raizproyecto="/todolist";
$router = new Aura\Router\RouterContainer();
$map = $router->getMap();
$map->get('todo.list', $raizproyecto.'/', function ($request) use ($twig) {
    $tasks = Task::all();

    $response = new Zend\Diactoros\Response\HtmlResponse($twig->render('template.twig', [
        'tasks' => $tasks
    ]));
    return $response;
});
$map->post('todo.add', $raizproyecto.'/add', function ($request) {
    $data = $request->getParsedBody();
    $task = new Task();
    $task->description = $data['description'];
    $task->save();

    $response = new Zend\Diactoros\Response\RedirectResponse('/todolist/');
    return $response;
});
$map->get('todo.delete', $raizproyecto.'/delete/{id}', function ($request) {
    $id = $request->getAttribute('id');
    $task = Task::find($id);
    $task->delete();

    $response = new Zend\Diactoros\Response\RedirectResponse('/todolist/');
    return $response;
});
$map->get('todo.check', $raizproyecto.'/check/{id}', function ($request) {
    var_dump("INgresoooooooo");

    $id = $request->getAttribute('id');
    $task = Task::find($id);
    $task->done = true;
    $task->save();

    $response = new Zend\Diactoros\Response\RedirectResponse('/todolist/');
    return $response;
});
$map->get('todo.uncheck',$raizproyecto.'/uncheck/{id}', function ($request) {
    $id = $request->getAttribute('id');
    $task = Task::find($id);
    $task->done = false;
    $task->save();

    $response = new Zend\Diactoros\Response\RedirectResponse('/todolist/');
    return $response;
});



$relay = new Relay([
    new Middlewares\AuraRouter($router),
    new Middlewares\RequestHandler()
]);

$response = $relay->handle($request);

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();