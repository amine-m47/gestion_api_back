<?php
require_once '../Controleur/RencontreControleur.php';

use Controleurs\RencontreControleur;

$rencontreControleur = new RencontreControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

switch ($http_method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $matchingData = $rencontreControleur->getRencontreById($id_rencontre);
            deliver_response(200, "Success", $matchingData);
        } else {
            $matchingData = $rencontreControleur->liste_rencontres();
            deliver_response(200, "Success", $matchingData);
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);
        if (isset($data['equipe_adverse']) && isset($data['date_rencontre']) && isset($data['heure_rencontre']) && isset($data['lieu'])) {
            $_POST = $data;
            $matchingData = 'r'
                //$rencontreControleur->ajouter_rencontre()
                ;
            deliver_response(201, "Created", $matchingData);
        } else {
            deliver_response(400, "Bad Request", ["message" => "Données manquantes"]);
        }
        break;
    case "PUT":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData, true);
            if (isset($data['equipe_adverse']) && isset($data['date_rencontre']) && isset($data['heure_rencontre']) && isset($data['lieu'])) {
                $_POST = $data;
                $rencontreControleur->modifier_rencontre($id_rencontre);
                deliver_response(200, "Updated", ["message" => "Rencontre modifiée avec succès"]);
            } else {
                deliver_response(400, "Bad Request", ["message" => "Données manquantes"]);
            }
        } else {
            deliver_response(400, "Bad Request", ["message" => "ID de rencontre manquant"]);
        }
        break;
    case "DELETE":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $rencontreControleur->supprimer_rencontre($id_rencontre);
            deliver_response(200, "Deleted", ["message" => "Rencontre supprimée avec succès"]);
        } else {
            deliver_response(400, "Bad Request", ["message" => "ID de rencontre manquant"]);
        }
        break;
    case "OPTIONS":
        deliver_response(204, "CORS and options accepted");
        break;
    default:
        deliver_response(405, "Method Not Allowed");
        break;
}

function deliver_response($status_code, $status_message, $data = null) {
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    $json_response = json_encode($response);
    if ($json_response === false) {
        die('json encode ERROR: ' . json_last_error_msg());
    }

    echo $json_response;
}
?>
