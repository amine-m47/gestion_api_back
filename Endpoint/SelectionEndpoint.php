<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
use Controleur\SelectionControleur;

require_once '../Controleur/SelectionControleur.php';
require_once '../Controleur/RencontreControleur.php';


$selectionControleur = new SelectionControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

switch ($http_method) {
    case "GET":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
                $joueurs_actifs = $selectionControleur->get_joueurs_actifs();
                $joueurs_selectionnes = $selectionControleur->get_joueurs_selectionnes($id_rencontre);
                $notes = $selectionControleur->get_notes($id_rencontre);
                $response = [
                    'joueurs_actifs' => $joueurs_actifs,
                    'joueurs_selectionnes' => $joueurs_selectionnes,
                    'notes' => $notes
                ];
                deliver_response(200, "Success", $response);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "PUT":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);
        if (isset($data['id_rencontre'])) {
            $id_rencontre = htmlspecialchars($data['id_rencontre']);
            $selectionControleur->modifier_selection($id_rencontre, $data);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "DELETE":
        if (isset($_GET['id'])) {
            $id_rencontre = htmlspecialchars($_GET['id']);
            $selectionControleur->supprimer_selection($id_rencontre);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true);

        if (isset($data['id_rencontre'], $data['numero_licence'], $data['poste'])) {
            $id_rencontre = htmlspecialchars($data['id_rencontre']);
            $numero_licence = htmlspecialchars($data['numero_licence']);
            $poste = htmlspecialchars($data['poste']);

            if (!empty($poste)) {
                $selectionControleur->ajouter_selection($id_rencontre, $numero_licence, $poste);
                deliver_response(201, "Selection ajoutée avec succès");
            } else {
                deliver_response(400, "Le poste ne peut pas être vide.");
            }
        } else {
            deliver_response(400, "Données incomplètes.");
        }
        break;
    case "OPTIONS":
        deliver_response(204, "CORS and options accepted");
        break;
    default:
        deliver_response(405, "Method Not Allowed");
        break;
}

function deliver_response($status_code, $status_message, $data = null)
{
    http_response_code($status_code);

    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    $json_response = json_encode($response);
    if ($json_response === false) {
        die('json encode ERROR: ' . json_last_error_msg());
    }

    echo $json_response;
}
