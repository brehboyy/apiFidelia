<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/script.php';

$app = new \Slim\App;
$app->get('/GET/{Id}', function (Request $request, Response $response, array $args) {
    $Id = $args['Id'];
    $query = "SELECT * FROM visite WHERE VIS_ID = ?";
	$result = $pdo->prepare($query);
	$result->execute(array($Id));
    $response->getBody()->write(json_encode($result));

    return $response;
});
$app->run();
