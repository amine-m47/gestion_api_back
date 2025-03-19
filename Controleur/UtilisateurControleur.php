<?php
namespace App\Controleurs;

use App\Modeles\Utilisateur;

class UtilisateurControleur {
    private $utilisateurModel;
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->utilisateurModel = new Utilisateur(); // Créer une instance du modèle Utilisateur
    }
    public function get_utilisateur($email) {
        try {
            $utilisateur = $this->utilisateurModel->trouverParEmail($email);
            $this->reponseJSON(200, $utilisateur);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération de l'utilisateur", "error" => $e->getMessage()]);
        }
    }

    public function modifier_utilisateur($email) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
            return;
        }

        $existingUtilisateur = $this->utilisateurModel->trouverParEmail($email);

        $nom = trim($input['nom'] ?? $existingUtilisateur['nom']);
        $prenom = trim($input['prenom'] ?? $existingUtilisateur['prenom']);
        $nom_equipe = trim($input['nom_equipe'] ?? $existingUtilisateur['nom_equipe']);

        if (empty($nom) || empty($prenom)) {
            $this->reponseJSON(400, ["message" => "Tous les champs obligatoires: nom, prenom doivent être remplis"]);
            return;
        }

        try {
            $this->utilisateurModel->mettreAJourInfos($existingUtilisateur['id_utilisateur'], $nom, $prenom, $nom_equipe);
            $this->reponseJSON(200, ["message" => "Utilisateur modifié avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la modification de l'utilisateur", "error" => $e->getMessage()]);
        }
    }

}
