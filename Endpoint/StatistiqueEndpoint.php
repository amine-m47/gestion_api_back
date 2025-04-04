<?php

use Controleur\StatistiqueControleur;

require_once '../Controleur/StatistiqueControleur.php';

$statistiqueControleur = new StatistiqueControleur();

$http_method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$bearer_token = get_bearer_token();
$user_role = null; // Default to unauthenticated

if ($bearer_token) {
    $auth_url = 'https://footballmanagerauth.alwaysdata.net/auth';
    $response = file_get_contents($auth_url, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer $bearer_token\r\nContent-Type: application/json\r\n",
        ]
    ]));

    if ($response !== false) {
        $auth_data = json_decode($response, true);
        if (isset($auth_data['valid']) && $auth_data['valid'] === true) {
            $user_role = $auth_data['role'];
            $user_id = $auth_data['user_id'] ?? null;
        }
    }
}

switch ($http_method) {
    case "GET":
        $statsRencontres = $statistiqueControleur->get_stats_rencontres();
        $joueurs = $statistiqueControleur->get_joueurs();
        if (isset($_GET['id'])) {
            $numero_licence = htmlspecialchars($_GET['id']);
            $statsJoueur = $statistiqueControleur->get_stats_joueur($numero_licence);
            $response = [
                'statsJoueur' => $statsJoueur
            ];
            deliver_response(200, "Success", $response);
        } else {
            $response = [
                'statsRencontres' => $statsRencontres,
                'joueurs' => $joueurs
            ];
            deliver_response(200, "Success", $response);
        }
        break;
    case "OPTIONS":
        deliver_response(204, "CORS and options accepted");
        break;
    default:
        deliver_response(405, "Method Not Allowed");
        break;
}

function get_bearer_token() {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
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