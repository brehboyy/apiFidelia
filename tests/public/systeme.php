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
        $contenu = $pdo->prepare('SELECT table_name as nom_table FROM information_schema.tables where table_schema= ? ');//->execute(array($name,$password));
        $contenu->execute(array('fidelia'));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, "message" => 'Message enregistré', 'result' => $liste));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    
});

$app->get('/getAllFromTable/{nom_base}', function (Request $request, Response $response, array $args) use ($pdo) {
   
    $nom_base = $args['nom_base'];
    try{
        $contenu = $pdo->prepare('SELECT * FROM '.$nom_base);//->execute(array($name,$password));
        $contenu->execute();
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        $contenu = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema="fidelia" AND table_name="'.$nom_base.'"');//->execute(array($name,$password));
        $contenu->execute();
        $columns = $contenu->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('success' => true, "message" => 'Message enregistré', 'result' => array('columns' => $columns, 'data' => $liste)));
    }catch(Exception $e){
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    
});

$app->post('/delete', function (Request $request, Response $response, array $args) use ($pdo) {
    if(isset($_POST['id']) && isset($_POST['table']) && isset($_POST['champs'])){
        try{
            $id = intval($_POST['id']);
            $contenu = $pdo->prepare('DELETE FROM '.$_POST['table'].' WHERE '.$_POST['champs'].'=?');//->execute(array($name,$password));
            $contenu->execute(array($id));

            echo json_encode(array('success' => true, "message" => $_POST['table']. ' supprimé'));
        }catch(Exception $e){
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    } 
});

$app->post('/importCSV', function (Request $request, Response $response, array $args) use ($pdo) {
    if ( 0 < $_FILES['file']['error'] ) {
        echo json_encode(array('success' => false, 'message' => $_FILES['file']['error']));
    }
    else {
        //move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);
            $row = 1;
            $sql = "";
            $arrayData = array();
            if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);

                    if($row == 1){
                        $sql .= 'INSERT INTO client VALUES ';
                    }else{
                        $sql .= "(";
                        for ($c=0; $c < $num; $c++) {
                            $sql .=  ($c > 0) ? (($c == ($num - 1)) ? '?' : '?,') : 'NULL,';
                            if($c > 0) array_push($arrayData, $data[$c]);
                        }
                        $sql .= '),';
                    }
                    
                    $row++;
                }
            }
            try{
                $contenu = $pdo->prepare(rtrim($sql, ','));//->execute(array($name,$password));
                $contenu->execute($arrayData);
                echo json_encode(array('success' => true, "message" => 'List de client correctement enregistrer'));
            }catch(Exception $e){
                echo json_encode(array('success' => false, 'message' => $e->getMessage()));
            }
        
    }
    fclose($handle);
       
});

$app->run();
