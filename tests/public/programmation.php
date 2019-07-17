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



$app->get('/sendMessage/{str}', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
    $str = $args['str'];
    $html = $str;
    $needle = "{{";
    $lastPos = 0;
    $positions = array();
    $dirPositions = array();

    //On recupere la position des balise dans le corps du message
    while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos = $lastPos + strlen($needle);
    }
    //On recupere le nom des balise correspondant
    $liste = array();
    for($i = 0 ; $i < count($positions) ; $i++){
        $contenu = $pdo->prepare('SELECT `ID_Balise`,`Attribut_Balise`,`Nom_Balise`,`Table_Balise` FROM `balise` WHERE `Nom_Balise` = ?');
		$contenu->execute(array(strtoupper(get_string_between(substr ( $str, $positions[$i], ($i == count($positions) - 1) ? strlen($str) - $positions[$i] : $positions[$i + 1] - $positions[$i] ), "{{", "}}"))));
        $res = $contenu->fetchAll(PDO::FETCH_ASSOC);
        array_push($liste, $res[0]);
    }

    $sql = "SELECT ID_Modele_Message, Corps_Modele_Message, Objet_Modele_Message FROM programmation p INNER JOIN modele_message m ON p.ID_Modele_Message_programmation = m.ID_Modele_Message WHERE m.Statut_Message = 'EN COUR' AND CURDATE() = DATE_ADD(p.DateEnvoi_programmation, INTERVAL p.NbTempsJour_programmation DAY) AND Type_Modele_Message = 'Mail'";
    $contenu = $pdo->prepare($sql);
	$contenu->execute();
    $res = $contenu->fetchAll(PDO::FETCH_ASSOC);

    if(count($res) > 0){
        foreach($res as $key => $value){
            $contenu = $pdo->prepare("SELECT DISTINCT c.ID_client ,c.Adresse_Mail_Client FROM tagmessage tm INNER JOIN modele_message m ON tm.ID_message = m.ID_Modele_Message INNER JOIN tagclient tc ON tm.ID_tag = tc.ID_Tag INNER JOIN client c ON tc.ID_Client = c.ID_Client WHERE tm.ID_message =  ?");
            $contenu->execute(array($value['ID_Modele_Message']));
            $listResClient = $contenu->fetchAll(PDO::FETCH_ASSOC);
            
            try{
                // Instantiation and passing `true` enables exceptions
                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->SMTPDebug = 2;                                       // Enable verbose debug output
                    $mail->isSMTP();                                            // Set mailer to use SMTP
                    $mail->Host       = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   = 'ousmane16diarra@gmail.com';                     // SMTP username
                    $mail->Password   = 'streete58';                               // SMTP password
                    $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
                    $mail->Port       = 465;                                    // TCP port to connect to
                    $mail->SMTPAutoTLS = false;
                    //Recipients
                    $mail->setFrom('ousmane16diarra@gmail.com', 'Mailer');
                    $mail->addAddress('ousmane.diarra1@outlook.fr', 'Ousmane Diarra');     // Add a recipient

                    // Content
                    $mail->isHTML(true);                                // Set email format to HTML
                    $mail->Subject = $value['Objet_Modele_Message'];
                    $mail->Body    = $value['Corps_Modele_Message'];
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    $mail->send();
                    echo json_encode( array('success' => true, 'message' => 'Message has been sent'));
                } catch (Exception $e) {
                    echo json_encode( array('success' => false, 'message' => $e->getMessage()));
                }
            
            }catch(Exception $e){
                echo json_encode( array('success' => false, 'message' => $e->getMessage()));
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
