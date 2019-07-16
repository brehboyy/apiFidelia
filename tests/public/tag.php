<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';
require '../../src/connexion.php';



$app = new \Slim\App;

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});


$app->get('/getall', function (Request $request, Response $response, array $args) use ($pdo) {
    
    try{
        $message = json_decode($_POST['message']);
        $contenu = $pdo->prepare('SELECT * FROM tag');//->execute(array($name,$password));
        $contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('success' => true, "message" => 'Message enregistré', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    
});


$app->get('/GetById/{Id}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $Id = $args['Id'];
		$contenu = $pdo->prepare('SELECT * FROM tag t  WHERE t.ID_Tag = ?');
		$contenu->execute(array($Id));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, 'message' => "modele de message par identifiant", 'result' => $liste));
        
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage(), 'result' => $liste));
    }
    return $response;
});

$app->post('/insert', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        var_dump($_POST);
        
		$contenu = $pdo->prepare('INSERT INTO tag VALUES (NULL,?,?);');
        $contenu->execute(array(json_decode($_POST['nom_tag']), "test"));
        $contenu = $pdo->prepare('SELECT LAST_INSERT_ID();');
        $contenu->execute();
        $liste = $contenu->fetchAll();

        foreach(json_decode($_POST['listId']) as $val){
            $contenu = $pdo->prepare('INSERT INTO tagclient VALUES (NULL,?,?);');
            $contenu->execute(array($liste[0][0],$val));
        }

        echo json_encode(array('success' => true, 'message' => "Creation du tag reussi", 'result' => $liste));
        
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage(), 'result' => $liste));
    }
    return $response;
});




$app->run();
