<?php
namespace Controleur;
require_once '../Modele/Rencontre.php';

use Modele\Rencontre;

class RencontreControleur {

    private $rencontreModel;

    public function __construct() {
        $this->rencontreModel = new Rencontre();
    }

    // Récupérer les détails d'une rencontre par ID
    public function get_rencontre($id_rencontre) {
        try {
            $rencontre = $this->rencontreModel->getRencontre($id_rencontre);
            $this->reponseJSON(200, $rencontre);

        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération de la rencontre", "error" => $e->getMessage()]);
        }
    }


    // Afficher la liste des rencontres
    public function get_rencontres() {
        try {
            // Récupérer les rencontres depuis le modèle
            $rencontres = $this->rencontreModel->getRencontres();
            $this->reponseJSON(200, $rencontres);
        } catch (\Exception $e) {
            // Gérer les erreurs
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des rencontres", "error" => $e->getMessage()]);
            return [];
        }
    }

    // Afficher le formulaire d'ajout et gérer la soumission
    public function ajouter_rencontre() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
                return;
            }

            // Récupérer les données du formulaire
            $equipe_adverse = trim($input['equipe_adverse']);
            $date_rencontre = $input['date_rencontre'];
            $heure_rencontre = $input['heure_rencontre'];
            $lieu = trim($input['lieu']);


            // Validation des champs
            if (empty($equipe_adverse) || empty($date_rencontre) || empty($heure_rencontre) || empty($lieu)) {
                $this->reponseJSON(400, ["message" => "Tous les champs obligatoires doivent être remplis"]);
                return;
            }

            // Ajouter la rencontre via le modèle
            try {
                $this->rencontreModel->ajouterRencontre($equipe_adverse, $date_rencontre, $heure_rencontre, $lieu);
                $this->reponseJSON(201, ["message" => "Rencontre ajoutée avec succès"]);
                exit();
            } catch (\Exception $e) {
                $this->reponseJSON(500, ["message" => "Erreur lors de l'ajout de la rencontre", "error" => $e->getMessage()]);
            }
        }
        else {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }
    }

    // Modifier une rencontre
    public function modifier_rencontre($id_rencontre) {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
                return;
            }

            // Récupérer les données du formulaire
            $equipe_adverse = trim($input['equipe_adverse']);
            $date_rencontre = $input['date_rencontre'];
            $heure_rencontre = $input['heure_rencontre'];
            $lieu = trim($input['lieu']);

            // Validation des champs
            if (empty($equipe_adverse) || empty($date_rencontre) || empty($heure_rencontre) || empty($lieu)) {
                $this->reponseJSON(400, ["message" => "Tous les champs obligatoires doivent être remplis"]);
                return;
            }

            // Modifier la rencontre via le modèle
            try {
                $this->rencontreModel->modifierRencontre($id_rencontre, $equipe_adverse, $date_rencontre, $heure_rencontre, $lieu);
                $this->reponseJSON(201, ["message" => "Rencontre modifiée avec succès"]);
                exit;
            } catch (\Exception $e) {
                $this->reponseJSON(500, ["message" => "Erreur lors de la modification de la rencontre", "error" => $e->getMessage()]);
            }
        } else {
            // Récupérer les informations de la rencontre pour pré-remplir le formulaire
            try {
                return $this->rencontreModel->getRencontre($id_rencontre);
            } catch (\Exception $e) {
                $this->reponseJSON(500, ["message" => "Erreur lors de la recuperation de la rencontre", "error" => $e->getMessage()]);
                return null;
            }
        }
    }

    // Supprimer une rencontre
    public function supprimer_rencontre($id_rencontre) {
        try {
            $this->rencontreModel->supprimerRencontre($id_rencontre);
            $this->reponseJSON(201, ["message" => "Rencontre supprimée avec succès"]);
            exit;
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la suppression de la rencontre", "error" => $e->getMessage()]);
        }
    }

    public function ajouter_resultat($id_rencontre) {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
                return;
            }

            $score_equipe = (int)$input['score_equipe'];
            $score_adverse = (int)$input['score_adverse'];

            $resultat = 'Nul';
            if ($score_equipe > $score_adverse) {
                $resultat = 'Victoire';
            } elseif ($score_equipe < $score_adverse) {
                $resultat = 'Défaite';
            }

            try {
                $this->rencontreModel->ajouterResultat($id_rencontre, $score_equipe, $score_adverse, $resultat);
                $this->reponseJSON(201, ["message" => "Resultat modifié avec succès"]);
                exit;
            } catch (\Exception $e) {
                $this->reponseJSON(500, ["message" => "Erreur lors de la modification du resultat de la rencontre", "error" => $e->getMessage()]);
            }
        } else {
            if ($id_rencontre) {
                try {
                    $rencontre = $this->rencontreModel->getRencontre($id_rencontre);
                    $this->reponseJSON(200, $rencontre);
                } catch (\Exception $e) {
                    $this->reponseJSON(500, ["message" => "Erreur lors de la récupération de la rencontre", "error" => $e->getMessage()]);
                }
            } else {
                $this->reponseJSON(400, ["message" => "ID de rencontre manquant"]);
            }
        }
    }

    public function statistiquesRencontres() {
        try {
            $statistiques = $this->rencontreModel->getStatistiquesRencontres();
            $this->reponseJSON(200, $statistiques);

        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération de la rencontre", "error" => $e->getMessage()]);
        }
    }

    private function reponseJSON($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

}
