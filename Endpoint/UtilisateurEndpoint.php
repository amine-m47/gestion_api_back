<?php
require_once '../Controleur/UtilisateurControleur.php';

use Controleur\UtilisateurControleur;

$utilisateurControleur = new UtilisateurControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

switch ($http_method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id = htmlspecialchars($_GET['id']);
            $matchingData = $utilisateurControleur->get_utilisateur($id);
            deliver_response(200, "Success", $matchingData);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "PUT":
        if (isset($_GET['id'])) {
            $id = htmlspecialchars($_GET['id']);
            $utilisateurControleur->modifier_utilisateur($id);
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