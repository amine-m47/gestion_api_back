<?php
namespace Controleur;
require_once '../Modele/Rencontre.php';
require_once '../Modele/Joueur.php';

use Modele\Joueur;
use Modele\Rencontre;
class StatistiqueControleur {
    private $rencontreModel;
    private $joueurModel;

    public function __construct() {
        $this->joueurModel = new Joueur();
        $this->rencontreModel = new Rencontre();
    }

    public function get_joueurs() {
        try {
            return $this->joueurModel->getAllJoueurs();
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des joueurs", "error" => $e->getMessage()]);
        }
    }
    public function get_stats_joueur($numero_licence) {
        try {
            return $this->joueurModel->getStatistiquesJoueur($numero_licence);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des stats du joueur $numero_licence", "error" => $e->getMessage()]);
        }
    }

    public function get_stats_rencontres() {
        try {
            return $this->rencontreModel->getStatistiquesRencontres();
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors du comptage des joueurs notés", "error" => $e->getMessage()]);
        }
    }

    // Fonction utilitaire pour répondre en JSON
    private function reponseJSON($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>