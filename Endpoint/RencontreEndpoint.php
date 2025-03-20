<?php
use Controleur\RencontreControleur;
require_once '../Controleur/RencontreControleur.php';

$rencontreControleur = new RencontreControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

switch ($http_method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $matchingData = $rencontreControleur->get_rencontre($id_rencontre);
            deliver_response(200, "Success", $matchingData);
        } else {
            $matchingData = $rencontreControleur->get_rencontres();
            deliver_response(200, "Success", $matchingData);
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);
        $matchingData = $rencontreControleur->ajouter_rencontre();
        deliver_response(201, "Created", $matchingData);
        break;
    case "PUT":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            if (isset($data['score_equipe']) && isset($data['score_adverse'])) {
                $matchingData = $rencontreControleur->ajouter_resultat($id_rencontre);
                deliver_response(200, "Success", $matchingData);
            } else {
                $matchingData = $rencontreControleur->modifier_rencontre($id_rencontre);
                deliver_response(200, "Success", $matchingData);
            }
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "DELETE":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $rencontreControleur->supprimer_rencontre($id_rencontre);
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