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


$app->get('/signin/{email}/{password}', function (Request $request, Response $response, array $args) use ($pdo) {
     
        try{
            $email = $args['email'];
            $password = $args['password'];
            
            $contenu = $pdo->prepare('SELECT ID_Utilisateur, Login_Utilisateur, Password_Utilisateur  FROM utilisateur WHERE Adresse_Mail_Utilisateur = ? OR Login_Utilisateur = ?'  );//->execute(array($name,$password));
            $contenu->execute(array($email, $email));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

            if(count($liste)>0)
            {
                if(password_verify($password, $liste[0]['Password_Utilisateur'])){
                    $code = true;
                    $message = "Bienvenue dans la ZONE ".$liste[0]['Login_Utilisateur'];
                    $res = array("success" => $code, "message" => $message, "result" => $liste[0]["ID_Utilisateur"]);
                    $response->getBody()->write(json_encode($res));
                }else{
                    $code = false;
                    $message = "Mot de passe ou email non valide";
                    $res= array("success"=>$code,"message"=>$message);
                    $response->getBody()->write(json_encode($res));
                }
            }
            else
            {
                $code = false;
                $message = "Mot de passe ou email non valide";
                $res = array("success"=>$code,"message"=>$message);
                $response->getBody()->write(json_encode($res));
                
            }
        }catch(Exception $ex){
            $response->getBody()->write(json_encode(array("success" => fale, "message" => $ex->getMessage())));
        }
    $pdo = null;
    return $response;
});

$app->post('/signin', function (Request $request, Response $response, array $args) use ($pdo) {
    if (isset($_POST['email']) && isset($_POST['password']))
    {	 
        try{
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $contenu = $pdo->prepare('SELECT ID_Utilisateur, Login_Utilisateur, Password_Utilisateur  FROM utilisateur WHERE Adresse_Mail_Utilisateur = ? OR Login_Utilisateur = ?'  );//->execute(array($name,$password));
            $contenu->execute(array($email, $email));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);

            if(count($liste)>0)
            {
                if(password_verify($password, $liste[0]['Password_Utilisateur'])){
                    $code = true;
                    $message = "Bienvenue dans la ZONE ".$liste[0]['Login_Utilisateur'];
                    $res = array("success" => $code, "message" => $message, "result" => $liste[0]["ID_Utilisateur"]);
                    $response->getBody()->write(json_encode($res));
                }else{
                    $code = false;
                    $message = "Mot de passe ou email non valide";
                    $res= array("success"=>$code,"message"=>$message);
                    $response->getBody()->write(json_encode($res));
                }
            }
            else
            {
                $code = false;
                $message = "Mot de passe ou email non valide";
                $res = array("success"=>$code,"message"=>$message);
                $response->getBody()->write(json_encode($res));
                
            }
        }catch(Exception $ex){
            $response->getBody()->write(json_encode(array("success" => fale, "message" => $ex->getMessage())));
        }
        
    }
    else
    {
        $code = false;
        $message = "Données non valide";
        $res = array("success"=>$code,"message"=>$message);
        $response->getBody()->write(json_encode($res));
    }

    $pdo = null;
    return $response;
});

$app->post('/get', function (Request $request, Response $response, array $args) use ($pdo) {
    try{
        if (isset($_POST['userid'])) {
            $request = json_decode($postdata);
            $userid = $request->userid;
            $query = "SELECT `Nom_Utilisateur`, `Prenom_Utilisateur`, `Adresse_Mail_Utilisateur`, `Fonction_Utilisateur`, `Login_Utilisateur`, `Type_Utilisateur` FROM 	utilisateur";
            $req = $pdo->prepare($query);
            $req->execute(array($userid));
            $result = $req->fetchAll(PDO::FETCH_ASSOC);
            $code = true;
            $message = "Utilisateur existant";
            $res= array("success"=>$code,"message"=>$message, "result" => $result[0]);
            $response->getBody()->write(json_encode($res));
        }else{
            $response->getBody()->write(json_encode(array("success" => false, "message" => "Erreur de données")));
        }
    }catch(Exception $ex){
        $response->getBody()->write(json_encode(array("success" => false, "message" => $ex->getMessage())));
    }
    $pdo = null;
    return $response;
});

$app->post('/signup', function (Request $request, Response $response, array $args) use ($pdo) {
    $postdata = file_get_contents("php://input");
    try{
        if (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['fonction']) && isset($_POST['login']) && isset($_POST['password']) && isset($_POST['type'])) {
            $request = json_decode($postdata);
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $email = $_POST['email'];
            $fonction = $_POST['fonction'];
            $login = $_POST['login'];
            $password = $_POST['password'];
            $type = $_POST['type'];

            $contenu = $pdo->prepare('SELECT ID_Utilisateur FROM utilisateur WHERE Adresse_Mail_Utilisateur = ? OR Login_Utilisateur = ?');
            $contenu->execute(array($email, $login));
            $liste = $contenu->fetchAll();
            if(count($liste)>0)
            {
                $code = false;
                $message = "Utilisateur login ou mail deja existant";
                $res = array("success"=>$code,"message"=>$message);
                $response->getBody()->write(json_encode($res));
            }
            else
            {
                $query = $pdo->prepare("INSERT INTO `utilisateur` (`ID_Utilisateur`, `Nom_Utilisateur`, `Prenom_Utilisateur`, `Adresse_Mail_Utilisateur`, `Fonction_Utilisateur`, `Login_Utilisateur`, `Password_Utilisateur`, `Type_Utilisateur`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
                $options =  array('cost' => 11);
                $hash = password_hash($password, PASSWORD_BCRYPT, $options);
                $query->execute(array($nom, $prenom, $email, $fonction, $login, $password, $type));

                if(!$query)
                {
                    $code = false;
                    $message = "Une erreur serveur ... Recommencez ...";
                    $res = array("success"=>$code,"message"=>$message);
                    $response->getBody()->write(json_encode($res));
                }
                else
                {
                    $code = true;
                    $message = "Enregistrement réussi.";
                    $res = array("success"=>$code,"message"=>$message);
                    $response->getBody()->write(json_encode($res));
                }
            }
        }	
        else{
            $code = false;
            $message = "Données non valide";
            $res= array("success"=>$code,"message"=>$message);
            $response->getBody()->write(json_encode($res));
        }
    }catch(Exception $ex){
        $response->getBody()->write(json_encode(array("success" => false, "message" => $ex->getMessage())));
    }

    $pdo = null;
    });

    $app->get('/signup/{nom}/{prenom}/{email}/{fonction}/{login}/{password}/{type}', function (Request $request, Response $response, array $args) use ($pdo) {
        try{
           
                $request = json_decode($postdata);
                $nom = $args['nom'];
                $prenom = $args['prenom'];
                $email = $args['email'];
                $fonction = $args['fonction'];
                $login = $args['login'];
                $password = $args['password'];
                $type = $args['type'];
    
                $contenu = $pdo->prepare('SELECT ID_Utilisateur FROM utilisateur WHERE Adresse_Mail_Utilisateur = ? OR Login_Utilisateur = ?');
                $contenu->execute(array($email, $login));
                $liste = $contenu->fetchAll();
                if(count($liste)>0)
                {
                    $code = false;
                    $message = "Utilisateur login ou mail deja existant";
                    $res = array("success"=>$code,"message"=>$message);
                    $response->getBody()->write(json_encode($res));
                }
                else
                {
                    $query = $pdo->prepare("INSERT INTO `utilisateur` (`ID_Utilisateur`, `Nom_Utilisateur`, `Prenom_Utilisateur`, `Adresse_Mail_Utilisateur`, `Fonction_Utilisateur`, `Login_Utilisateur`, `Password_Utilisateur`, `Type_Utilisateur`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
                    $options =  array('cost' => 11);
                    $hash = password_hash($password, PASSWORD_BCRYPT, $options);
                    $query->execute(array($nom, $prenom, $email, $fonction, $login, $hash, $type));
    
                    if(!$query)
                    {
                        $code = false;
                        $message = "Une erreur serveur ... Recommencez ...";
                        $res = array("success"=>$code,"message"=>$message);
                        $response->getBody()->write(json_encode($res));
                    }
                    else
                    {
                        $code = true;
                        $message = "Enregistrement réussi.";
                        $res = array("success"=>$code,"message"=>$message);
                        $response->getBody()->write(json_encode($res));
                    }
                }
           
        }catch(Exception $ex){
            $response->getBody()->write(json_encode(array("success" => false, "message" => $ex->getMessage())));
        }
    
        $pdo = null;
        });




$app->run();
