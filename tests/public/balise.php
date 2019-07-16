<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';
require '../../src/connexion.php';


$app = new \Slim\App;

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});


$app->get('/getall', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $contenu = $pdo->prepare('SELECT Nom_balise FROM balise');
        $contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('success' => true, "message" => 'Liste des balises diponibles', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
});
$app->run();
