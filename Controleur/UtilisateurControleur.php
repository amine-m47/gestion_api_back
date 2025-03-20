<?php
namespace Controleur;
require '../Modele/Utilisateur.php';
use Modele\Utilisateur;

class UtilisateurControleur {
    private $utilisateurModel;
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->utilisateurModel = new Utilisateur(); // Créer une instance du modèle Utilisateur
    }
    public function get_utilisateur($id) {
        try {
            $utilisateur = $this->utilisateurModel->getUtilisateur($id);
            $this->reponseJSON(200, $utilisateur);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération de l'utilisateur", "error" => $e->getMessage()]);
        }
    }

    public function modifier_utilisateur($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
            return;
        }

        $existingUtilisateur = $this->utilisateurModel->getUtilisateur($id);

        $nom = trim($input['nom'] ?? $existingUtilisateur['nom']);
        $prenom = trim($input['prenom'] ?? $existingUtilisateur['prenom']);
        $nom_equipe = trim($input['nom_equipe'] ?? $existingUtilisateur['nom_equipe']);

        if (empty($nom) || empty($prenom)) {
            $this->reponseJSON(400, ["message" => "Tous les champs obligatoires: nom, prenom doivent être remplis"]);
            return;
        }

        try {
            $this->utilisateurModel->modifierUtilisateur($existingUtilisateur['id_utilisateur'], $nom, $prenom, $nom_equipe);
            $this->reponseJSON(200, ["message" => "Utilisateur modifié avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la modification de l'utilisateur", "error" => $e->getMessage()]);
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
