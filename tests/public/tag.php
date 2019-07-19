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
        $contenu = $pdo->prepare('SELECT ID_Tag, Nom_Tag, Description_Tag FROM tag');//->execute(array($name,$password));
        $contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('success' => true, "message" => 'Message enregistré', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    
});


$app->get('/GetByIdMessage/{Id}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $Id = $args['Id'];
		$contenu = $pdo->prepare('SELECT t.ID_Tag, t.Nom_Tag, t.Description_Tag  FROM tagmessage tm INNER JOIN tag t ON t.ID_Tag = tm.ID_tag_tagmessage WHERE tm.ID_message_modele_message = ?');
		$contenu->execute(array($Id));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('success' => true, 'message' => "Recupération de la liste de tags réussite", 'result' => $liste));  
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
});

$app->post('/insertClient', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST['nom_tag'])){
        try{
            $contenu = $pdo->prepare('INSERT INTO tag VALUES (NULL,?,?);');
            $contenu->execute(array(json_decode($_POST['nom_tag']), "test"));
            $contenu = $pdo->prepare('SELECT LAST_INSERT_ID() AS LASTID;');
            $contenu->execute();
            $liste = $contenu->fetchAll();

            foreach(json_decode($_POST['listId']) as $val){
                $contenu = $pdo->prepare('INSERT INTO tagclient VALUES (?,?);');
                $contenu->execute(array($liste[0]['LASTID'],$val));
            }
            echo json_encode(array('success' => true, 'message' => "Creation du tag reussi", 'result' => $liste));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});

$app->post('/insertMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST['listId']) && isset($_POST['idMessage'])){
        try{
            foreach(json_decode($_POST['listId']) as $val){
                $contenu = $pdo->prepare('INSERT INTO tagmessage VALUES (?,?);');
                $contenu->execute(array($val,$_POST['idMessage']));
            }

            echo json_encode(array('success' => true, 'message' => "Creation du tag reussi", 'result' => $liste));
        
            
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage(), 'result' => $liste));
        }
    }
});




$app->run();
