<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            $contenu = $pdo->prepare('SELECT LAST_INSERT_ID() AS LASTID;');
            $contenu->execute();
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC); 
            foreach($message->ListTag as $nomtag){
                try{
                    $contenu = $pdo->prepare('INSERT INTO tagmessage (`ID_tag_tagmessage`,`ID_message_modele_message`) SELECT ID_tag, '.$liste[0]['LASTID'].' FROM tag t WHERE t.Nom_Tag = ?');
                    $contenu->execute(array($nomtag));
                }catch(Exception $e){

                }
            }
            echo json_encode(array('success' => true, "message" => 'Message enregistré', 'result' => $liste[0]['LASTID']));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});

$app->post('/insertPiecesjointes', function (Request $request, Response $response, array $args) use ($pdo) {
    
    if ( 0 < min($_FILES['fileToUpload']['error']) ) {
        foreach($_FILES['fileToUpload']['error'] as $error)
        echo json_encode(array('success' => false, 'message' => $error));
    }
    else {
        try{
            $contenu = $pdo->prepare("DELETE IGNORE FROM piece_jointe WHERE ID_modele_message_Piece_Jointe = ?");
            $contenu->execute(array(explode("_", $_FILES['fileToUpload']['name'][0])[1]));

            for($i = 0 ; $i < count($_FILES['fileToUpload']) ; $i++){
                //$pathfileinfo = pathinfo($_FILES['fileToUpload']['tmp_name'][$i]);
                //$filename = $pathfileinfo['dirname'].'/'.explode("_", $_FILES['fileToUpload']['name'][$i])[0];
                copy($_FILES['fileToUpload']['tmp_name'][$i], '../../piecejointe/'.explode("_", $_FILES['fileToUpload']['name'][$i])[0]);
                //rename ($_FILES['fileToUpload']['tmp_name'][$i], $filename);
                //$blob = fopen($filename, 'rb');
                $contenu = $pdo->prepare("INSERT INTO `piece_jointe` (`ID_Piece_Jointe`, `ID_modele_message_Piece_Jointe`, File_path_piece_jointe) VALUES (NULL, ?, ?)");
                $contenu->execute(array(explode("_", $_FILES['fileToUpload']['name'][$i])[1], '../../piecejointe/'.explode("_", $_FILES['fileToUpload']['name'][$i])[0]));
            }
            echo json_encode(array('success' => true, "message" => 'pieces jointes enregistré'));
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

/// ++ Début SRO - V1 - 18.07.2019 UpdateMessage
$app->post('/updateMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($_POST['message']))
    {   
        try{
            $message = json_decode($_POST['message']);
            $contenu = $pdo->prepare('UPDATE `modele_message` SET `Titre_Modele_Message`= ?,`Corps_Modele_Message`= ?,`Template_Modele_Message`= ?,`Objet_Modele_Message`= ?,`Type_Modele_Message`= ?,`Categorie_Modele_Message`= ?  WHERE  `ID_Modele_Message`= ? ');
            $contenu->execute(array($message->Titre, $message->Corps, $message->Template, $message->Object, $message->Type, $message->Categorie, $message->Id)); 
            
            $contenu = $pdo->prepare('DELETE IGNORE FROM tagmessage WHERE ID_message_modele_message = ?');
            $contenu->execute(array($message->Id)); 
            
            foreach($message->ListTag as $nomtag){
                try{
                    $contenu = $pdo->prepare('INSERT IGNORE INTO tagmessage (`ID_tag_tagmessage`,`ID_message_modele_message`) SELECT ID_tag, ? FROM tag t WHERE t.Nom_Tag = ?');
                    $contenu->execute(array($message->Id, $nomtag));
                }catch(Exception $e){

                }
            }
            echo json_encode(array('success' => true, "message" => 'Message enregistré'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});

$app->get('/existById/{Id}', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($args['Id'])) {
        try{
            $Id = $args['Id'];
            $contenu = $pdo->prepare('SELECT ID_Modele_Message FROM modele_message m  WHERE m.ID_Modele_Message = ?');
            $contenu->execute(array($Id));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC); 
            if (count($liste) > 0) {
                echo json_encode(array('success' => true, 'message' => 'message exist', 'result' => true));
            }else{
                echo json_encode(array('success' => true, 'message' => 'message exist', 'result' => false));
            }
            
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
 }
});

$app->post('/updateStatusMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($_POST['message']))
    {   
        try{
            $message = json_decode($_POST['message']);
            var_dump($message);
            //var_dump($message);
            $contenu = $pdo->prepare('UPDATE `modele_message` SET `Statut_Message`= ?  WHERE  `ID_Modele_Message`= ? ');
            $contenu->execute(array($message->Status,$message->Id)); 
            
            echo json_encode(array('success' => true, "message" => 'Statut du message enregistré'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }
});

/// ++ Fin SRO - V1 - 18.07.2019 UpdateMessage



$app->get('/getall', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message, Statut_Message FROM modele_message');//->execute(array($name,$password));
		$contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, 'message' => 'recuperation reussi', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    return $response;
});


$app->get('/GetById/{Id}', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($args['Id'])) {
        try{
            $Id = $args['Id'];
    		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.ID_Modele_Message = ?');
    		$contenu->execute(array($Id));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(array('success' => true, 'message' => 'recuperation reussi', 'result' => $liste));
            
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
 }
});

$app->get('/getbytype/{type}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        $type = $args['type'];
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Type_Modele_Message = ?');
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
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Categorie_Modele_Message = ?');
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
		$contenu = $pdo->prepare('SELECT ID_Modele_Message, Titre_Modele_Message, Corps_Modele_Message, Template_Modele_Message, Objet_Modele_Message, Type_Modele_Message, Categorie_Modele_Message, Date_Modele_Message FROM modele_message m  WHERE m.Date_Modele_Message = ?');
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

            echo json_encode(array('success' => true, "message" => 'Message supprimé'));
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

 $app->post('/envoyer', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST['Id'])){

        $id = $_POST['Id'];
        $sql = 'SELECT `Corps_Modele_Message`,`Objet_Modele_Message` FROM `modele_message` WHERE `ID_modele_message` = ?';
        $contenu = $pdo->prepare($sql);
        $contenu->execute(array($id));
        $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
        $value = $res[0];
        $html = $value['Corps_Modele_Message'];
        $needle = "{{";
        $lastPos = 0;
        $positions = array();
        $dirPositions = array();



        while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        //On recupere le nom des balise correspondant
        $liste = array();
        for($i = 0 ; $i < count($positions) ; $i++){
            $contenu = $pdo->prepare('SELECT `ID_Balise`,`Attribut_Balise`,`Nom_Balise`,`Table_Balise` FROM `balise` WHERE `Nom_Balise` = ?');
            $contenu->execute(array(strtoupper(get_string_between(substr ( $html, $positions[$i], ($i == count($positions) - 1) ? strlen($html) - $positions[$i] : $positions[$i + 1] - $positions[$i] ), "{{", "}}"))));
            $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
            array_push($liste, $res[0]);
        }
        $str = "";
        $compt = 0;
        $listResClient = array();
        foreach($liste as $nomBalise => $valchamps){
            //$str = str_replace('{{'.$valchamps['Nom_Balise'].'}}', $valchamps['Attribut_Balise'], $compt == 0 ? $value['Corps_Modele_Message'] : $str);
            if($compt == 0){
                $str = "REPLACE(Corps_Modele_Message,'{{".$valchamps['Nom_Balise']."}}',".$valchamps['Attribut_Balise'].")";
            }else{
                $str = "REPLACE(".$str.",'{{".$valchamps['Nom_Balise']."}}',".$valchamps['Attribut_Balise'].")";
            }
            $compt++;
        }
        if(strlen($str) > 0){
            $sql = "SELECT DISTINCT ".$str." AS CorpsMessage, c.Nom_Client, c.Prenom_Client, c.Adresse_Mail_Client  FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client WHERE m.ID_Modele_Message = ?";
            $contenu = $pdo->prepare($sql);
            $contenu->execute(array($id));
            $listResClient = $contenu->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $sql = "SELECT DISTINCT Corps_Modele_Message AS CorpsMessage, c.Nom_Client, c.Prenom_Client, c.Adresse_Mail_Client FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client WHERE m.ID_Modele_Message = ?";
            $contenu = $pdo->prepare($sql);
            $contenu->execute(array($value["ID_Modele_Message"]));
            $listResClient = $contenu->fetchAll(PDO::FETCH_ASSOC);
        }


        foreach($listResClient as $cle => $client){
            
            
            try{
                // Instantiation and passing `true` enables exceptions
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->SMTPDebug = 2;                                       // Enable verbose debug output
                    $mail->isSMTP();                                            // Set mailer to use SMTP
                    $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   = 'fideliialaval@gmail.com';                     // SMTP username
                    $mail->Password   = 'BossGroup51';                               // SMTP password
                    $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
                    $mail->Port       = 465;                                    // TCP port to connect to
                    $mail->SMTPAutoTLS = false;
                    //Recipients
                    $mail->setFrom('fideliialaval@gmail.com', 'fidelia');
                    $mail->addAddress($client['Adresse_Mail_Client'], $client['Nom_Client']." ".$client['Prenom_Client']);  //$client['Adresse_Mail_Client']  // Add a recipient
                    $sql = "SELECT File_path_piece_jointe FROM piece_jointe pj WHERE ID_modele_message_Piece_Jointe = ?";
                    
                    $contenu = $pdo->prepare($sql);
                    $contenu->execute(array($id));
                    $listpj = $contenu->fetchAll(PDO::FETCH_ASSOC);



                    foreach($listpj as $piece => $jointe){
                        var_dump(basename($jointe['File_path_piece_jointe']).PHP_EOL);
                        $mail->AddAttachment($jointe['File_path_piece_jointe'], basename($jointe['File_path_piece_jointe']).PHP_EOL);
                    }
                    // Content
                    $mail->isHTML(true);                                // Set email format to HTML
                    $mail->Subject = $value['Objet_Modele_Message'];
                    $mail->Body    = $client['CorpsMessage'] ;
                    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    $mail->send();
                    echo json_encode( array('success' => true, 'message' => 'Le message a ete envoye'));
                } catch (Exception $e) {
                    echo json_encode( array('success' => false, 'message' => $e->getMessage()));
                }
            
            }catch(Exception $e){
                echo json_encode( array('success' => false, 'message' => $e->getMessage()));
            }
        }

        // Fais ce que tu as à faire !
    }
    return $response;
});




$app->run();
