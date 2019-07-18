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



$app->get('/sendMessage', function (Request $request, Response $response, array $args) use ($pdo) {
    try{

    //On recupere la position des balise dans le corps du message
    
    //$sql = "SELECT c.ID_client ,c.Adresse_Mail_Client, Corps_Modele_Message, Objet_Modele_Message ,REPLACE(m.Corps_Modele_Message, \'{{Nom_Client}}\',c.Nom_Client) FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client INNER JOIN programmation p ON p.ID_Modele_Message_programmation = m.ID_Modele_Message WHERE m.Statut_Message = \'EN COUR\' AND CURDATE() = date(DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY)) AND Type_Modele_Message = \'Mail\'";
    
    //$sql = "SELECT ID_Modele_Message, Corps_Modele_Message, Objet_Modele_Message FROM modele_message m INNER JOIN programmation p ON p.ID_Modele_Message_programmation = m.ID_Modele_Message WHERE m.Statut_Message = 'EN COUR' AND CURDATE() = DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY) AND Type_Modele_Message = 'Mail'";
    //$sql = "SELECT c.ID_client ,c.Adresse_Mail_Client,m.ID_Modele_Message , Corps_Modele_Message, Objet_Modele_Message FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client INNER JOIN programmation p ON p.ID_Modele_Message_programmation = m.ID_Modele_Message WHERE m.Statut_Message = 'EN COUR' AND CURDATE() = date(DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY)) AND Type_Modele_Message = 'Mail'";
    $sql = "SELECT m.ID_Modele_Message , Corps_Modele_Message, Objet_Modele_Message, p.Condition_Programmation FROM modele_message m INNER JOIN programmation p ON p.ID_Modele_Message_programmation = m.ID_Modele_Message WHERE m.Statut_Message = 'EN COUR' AND CURDATE() = date(DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY)) AND Type_Modele_Message = 'Mail' AND HOUR(DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY)) = HOUR(CURRENT_TIME)";
    $contenu = $pdo->prepare($sql);
	$contenu->execute();
    $res = $contenu->fetchAll(PDO::FETCH_ASSOC);

    if(count($res) > 0){
        foreach($res as $key => $value){

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
            $liste = array_unique($liste);
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
                $sql = "SELECT ".$str." AS CorpsMessage, c.Nom_Client, c.Prenom_Client, c.Adresse_Mail_Client  FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client WHERE m.ID_Modele_Message = ?";
                $contenu = $pdo->prepare($sql);
                $contenu->execute(array($value["ID_Modele_Message"]));
                $listResClient = $contenu->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $sql = "SELECT Corps_Modele_Message AS CorpsMessage, c.Nom_Client, c.Prenom_Client, c.Adresse_Mail_Client FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message_modele_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag_tagmessage = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client WHERE m.ID_Modele_Message = ?";
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
                        $mail->setFrom('fideliialaval@gmail.com', 'GAINDE');
                        $mail->addAddress($client['Adresse_Mail_Client'], $client['Nom_Client']." ".$client['Prenom_Client']);  //$client['Adresse_Mail_Client']  // Add a recipient

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

        }
    }

    }catch(Exception $ex){
        echo array('success' => false, 'message' => $e->getMessage());
    }
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
