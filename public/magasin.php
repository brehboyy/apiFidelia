<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/script.php';

$app = new \Slim\App;
$app->get('/getall', function (Request $request, Response $response, array $args) use ($pdo) {
    $query = "SELECT * FROM magasin";
	$req = $pdo->prepare($query);
    $req->execute();
    $result = $req->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($result));
    $pdo = null;
    return $response;
});
$app->run();
