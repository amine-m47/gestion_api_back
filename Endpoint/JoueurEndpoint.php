<?php
require_once '../Controleur/JoueurControleur.php';

use Controleurs\JoueurControleur;

$joueurControleur = new JoueurControleur();

$http_method = $_SERVER['REQUEST_METHOD'];


switch ($http_method) {
    case "GET":
        if (isset($_GET['numero_licence'])) {
            $numero_licence = htmlspecialchars($_GET['numero_licence']);
            $matchingData = $joueurControleur->get_joueur($numero_licence);
            deliver_response(200, "Success", $matchingData);
        } else {
            $matchingData = $joueurControleur->liste_joueurs();
            deliver_response(200, "Success", $matchingData);
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);
        $matchingData = $joueurControleur->ajouter_joueur();
        deliver_response(201, "Created", $matchingData);
        break;
    case "PUT":
        if (isset($_GET['numero_licence'])) {
            $numero_licence = htmlspecialchars($_GET['numero_licence']);
            $joueurControleur->modifier_joueur($numero_licence);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "DELETE":
        if (isset($_GET['numero_licence'])) {
            $numero_licence = htmlspecialchars($_GET['numero_licence']);
            $joueurControleur->supprimer_joueur($numero_licence);
        } else {
            deliver_response(400, "Bad Request");
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