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


$app->post('/insertMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($_POST['message']))
    {	
        try{
            $message = json_decode($_POST['message']);
            $contenu = $pdo->prepare('INSERT INTO `modele_message` (`ID_Modele_Message`, `Titre_Modele_Message`, `Corps_Modele_Message`,`Template_Modele_Message`, `Objet_Modele_Message`, `Type_Modele_Message`, `Categorie_Modele_Message`, `Date_Modele_Message`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)');//->execute(array($name,$password));
            $contenu->execute(array($message->Titre, $message->Corps, $message->Template, $message->Object, $message->Type, $message->Categorie, date("Y-m-d")));
            echo json_encode(array('success' => true, "message" => 'Message enregistré'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});

/// ++ Début SRO - V1 - 17.07.2019 InsertProgrammation
$app->post('/insertProgrammation', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($_POST['programmation']))
    {   
        try{
            $programmation = json_decode($_POST['programmation']);
            $contenu = $pdo->prepare('INSERT INTO `programmation` (`ID_programmation`,`ID_Modele_Message_programmation`, `NbTempsJour_programmation`, `DateEnvoi_programmation`, `Condition_programmation`) VALUES (NULL, ?, ?, ?, ?)');//->execute(array($name,$password));
            $contenu->execute(array($programmation->ID_Modele_Message, $programmation->NbTempsJour, $programmation->DateEnvoi, $programmation->Condition));
            echo json_encode(array('success' => true, 'message' => 'Programmation enregistré'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});
/// ++ Fin SRO - V1 - 17.07.2019 InsertProgrammation



$app->get('/getall', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message');//->execute(array($name,$password));
		$contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, 'message' => 'recuperation reussi', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    return $response;
});


$app->get('/GetById/{Id}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $Id = $args['Id'];
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.ID_Modele_Message = ?');
		$contenu->execute(array($Id));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, 'message' => 'recuperation reussi', 'result' => $liste));
        
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    return $response;
});

$app->get('/getbytype/{type}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $type = $args['type'];
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Type_Modele_Message = ?');
		$contenu->execute(array($type));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        
        var_dump($liste);
        $response = array('success' => true, 'message' => "modele de message par identifiant", 'result' => $liste);
    }catch(Exception $e){
        $response = array('success' => false, 'message' => $e->getMessage());
    }
    return $response;
});

$app->get('/GetByCategorie/{Categorie}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $Categorie = $args['Categorie'];
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Categorie_Modele_Message = ?');
		$contenu->execute(array($Categorie));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array('success' => true, 'message' => "modele de message par identifiant", 'result' => $liste);
    }catch(Exception $e){
        $response = array('success' => false, 'message' => $e->getMessage());
    }
    return $response;
});

$app->get('/GetByDate/{Date}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $Date = $args['Date'];
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Date_Modele_Message = ?');
		$contenu->execute(array($Date));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array('success' => true, 'message' => "modele de message par identifiant", 'result' => $liste);
    }catch(Exception $e){
        $response = array('success' => false, 'message' => $e->getMessage());
    }
    return $response;
});

$app->post('/sendMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST["message"])){
        $message = json_decode($_POST["message"]);
        $needle = "{{";
        $lastPos = 0;
        $positions = array();
        $dirPositions = array();
        $listeTag = $_POST["listTag"];
        while (($lastPos = strpos($message, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        for($i = 0 ; $i < count($positions) ; $i++){
            $contenu = $pdo->prepare('SELECT `ID_Balise`,`Attribut_Balise`,`Nom_Balise`,`Table_Balise` FROM `balise` WHERE `Nom_Balise` = ?');
            $contenu->execute(array(get_string_between(substr ( $message, $positions[$i], ($i == count($positions) - 1) ? strlen($message) - $positions[$i] : $positions[$i + 1] - $positions[$i] ), "{{", "}}")));
            $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
            array_push($dirPositions, array('position' => $positions[$i] , 'Nom_Balise' =>  $res[0]["Nom_Balise"]));
        }
        echo json_encode(array($dirPositions, $listeTag));


        /*$contenu = $pdo->prepare('SELECT  FROM `tagclient` tc INNER JOIN ?  ON tc.ID_Client = c.ID_Client WHERE '.$string);
        
        $contenu->execute(array($value["Nom_Balise"]));
        $listeClient = $contenu->fetchAll(PDO::FETCH_ASSOC);
        while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        $stringtag = "";
        for($i = 0 ; $i < count($listeTag) ; $i++){
            $string += ($i == 0) ? "tc.ID_Tag = ".$tag : " OR tc.ID_Tag = ".$tag ;
        }

        for($i = 0 ; $i < count($positions) ; $i++){
            $contenu = $pdo->prepare('SELECT `ID_Balise`,`Attribut_Balise`,`Nom_Balise`,`Table_Balise` FROM `balise` WHERE `Nom_Balise` = ?');
            $contenu->execute(array(get_string_between(substr ( $str, $positions[$i], ($i == count($positions) - 1) ? strlen($str) - $positions[$i] : $positions[$i + 1] - $positions[$i] ), "{{", "}}")));
            $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
            array_push($dirPositions, array('position' => $positions[$i] , 'Nom_Balise' =>  $res[0]['Nom_Balise']));
        }

        foreach($dirPositions as $key => $value){
            $contenu = $pdo->prepare('SELECT ? FROM `tagclient` tc INNER JOIN ?  ON tc.ID_Client = c.ID_Client WHERE '.$string);
            $contenu->execute(array($value["Nom_Balise"]));
            $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
            $value[""]
        }*/
    }
    /*if (isset($postdata))
    {	 
        try{
            $request = json_decode($postdata);
            $message = $request->message;
            $response = array('success' => true, 'message' => "modele de message par identifiant", 'result' => $message);
        }catch(Exception $e){
            $response = array('success' => false, 'message' => $e->getMessage());
        }
    }
    return $response;*/
});

$app->post('/delete', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST['id'])){
        try{
            $id = intval($_POST['id']);
            $contenu = $pdo->prepare('DELETE FROM `modele_message` WHERE ID_Modele_Message=?');//->execute(array($name,$password));
            $contenu->execute(array($id));

            echo json_encode(array('success' => true, "message" => $_POST['table']. ' supprimé'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    } 
});



$app->get('/sendMessage/{str}/', function (Request $request, Response $response, array $args) use ($pdo) {
    $str = $args['str'];
    $html = $str;
    $needle = "{{";
    $lastPos = 0;
    $positions = array();
    $dirPositions = array();


    while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + strlen($needle);
    }

    // Displays 3 and 10
    $liste = array();
    for($i = 0 ; $i < count($positions) ; $i++){
        $contenu = $pdo->prepare('SELECT `ID_Balise`,`Attribut_Balise`,`Nom_Balise`,`Table_Balise` FROM `balise` WHERE `Nom_Balise` = ?');
		$contenu->execute(array(strtoupper(get_string_between(substr ( $str, $positions[$i], ($i == count($positions) - 1) ? strlen($str) - $positions[$i] : $positions[$i + 1] - $positions[$i] ), "{{", "}}"))));
        $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
        array_push($liste, $res[0]);
    }

    /*
    $tabString = split("[%%]",$str);
    var_dump($tabString);

    return $tabString;*/
    /*if (isset($postdata))
    {	 
        try{
            $request = json_decode($postdata);
            $message = $request->message;
            $response = array('success' => true, 'message' => "modele de message par identifiant", 'result' => $message);
        }catch(Exception $e){
            $response = array('success' => false, 'message' => $e->getMessage());
        }
    }
    return $response;*/
});

 $app->get('/envoyer', function (Request $request, Response $response, array $args) use ($pdo) {
    
    try{
        $to      = 'personne@example.com';
        $subject = 'le sujet';
        $message = 'Bonjour !';
        $headers = 'From: webmaster@example.com' . "\r\n" .
        'Reply-To: webmaster@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
   
        mail($to, $subject, $message, $headers);
        $response = array('success' => true, 'message' => "Le mail a été envoyé");
    
    }catch(Exception $e){
        $response = array('success' => false, 'message' => $e->getMessage());
    }
    return $response;
});




$app->run();
