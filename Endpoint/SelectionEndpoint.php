<?php

use Controleur\SelectionControleur;

require_once '../Controleur/SelectionControleur.php';

$selectionControleur = new SelectionControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

switch ($http_method) {
    case "GET":
        if (isset($_GET['id_rencontre'])) {
            $id_rencontre = htmlspecialchars($_GET['id_rencontre']);
            $joueurs_actifs = $selectionControleur->get_joueurs_actifs();
            $joueurs_selectionnes = $selectionControleur->get_joueurs_selectionnes($id_rencontre);
            $response = [
                'joueurs_actifs' => $joueurs_actifs,
                'joueurs_selectionnes' => $joueurs_selectionnes
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
        if (isset($_GET['id_rencontre'])) {
            $id_rencontre = htmlspecialchars($_GET['id_rencontre']);
            $selectionControleur->supprimer_selection($id_rencontre);
        } else {
            deliver_response(400, "Bad Request");
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData, true); // Décodage du JSON

        // Vérifie si id_rencontre et selections sont présents et non vides
        if (isset($data['id_rencontre'], $data['selections']) && !empty($data['selections']) && is_array($data['selections'])) {
            $id_rencontre = htmlspecialchars($data['id_rencontre']);
            $selections = $data['selections'];
            $selectionControleur->supprimerSelect($id_rencontre);

            // Traitement des sélections
            foreach ($selections as $selection) {
                if (isset($selection['numero_licence'], $selection['poste'])) {
                    // Vérifie que les valeurs nécessaires sont présentes
                    if (!empty($selection['numero_licence']) && $selection['poste'] != "") {
                        // Appel à la fonction pour ajouter ou mettre à jour la sélection
                        $selectionControleur->ajouter_ou_modifier_selection($id_rencontre, $selection['numero_licence'], $selection['poste']);
                    }
                } else {
                    deliver_response(400, "Les données de sélection sont incomplètes.");
                    return;
                }
            }
            deliver_response(201, "Sélections ajoutées ou mises à jour avec succès");
        } else {
            deliver_response(400, "Données de sélection manquantes ou mal formées.");
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
