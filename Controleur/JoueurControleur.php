<?php
namespace Controleurs;
require_once '../Modele/Joueur.php'; // Add this line to include the Joueur class

use Modeles\Joueur;

class JoueurControleur {
    private $joueurModel;

    public function __construct() {
        $this->joueurModel = new Joueur();
    }

    public function get_joueur($numero_licence) {
        try {
            $joueur = $this->joueurModel->getJoueurByNumeroLicence($numero_licence);
            $this->reponseJSON(200, $joueur);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération du joueur", "error" => $e->getMessage()]);
        }
    }
    // Liste des joueurs
    public function liste_joueurs() {
        try {
            $joueurs = $this->joueurModel->getAllJoueurs();
            $this->reponseJSON(200, $joueurs);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des joueurs", "error" => $e->getMessage()]);
        }
    }

    // Liste des joueurs actifs
    public function liste_joueurs_actifs() {
        try {
            $joueurs = $this->joueurModel->getJoueursActifs();
            $this->reponseJSON(200, $joueurs);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des joueurs actifs", "error" => $e->getMessage()]);
        }
    }

    // Ajouter un joueur
    public function ajouter_joueur() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
            return;
        }

        $numero_licence = trim($input['numero_licence'] ?? '');
        $nom = trim($input['nom'] ?? '');
        $prenom = trim($input['prenom'] ?? '');
        $date_naissance = $input['date_naissance'] ?? '';
        $taille = isset($input['taille']) ? (float)$input['taille'] : null;
        $poids = isset($input['poids']) ? (float)$input['poids'] : null;
        $statut = $input['statut'] ?? 'Actif';
        $position_preferee = trim($input['position_preferee'] ?? '');
        $commentaire = trim($input['commentaire'] ?? '');

        // Validation
        if (empty($nom) || empty($prenom) || empty($date_naissance)) {
            $this->reponseJSON(400, ["message" => "Tous les champs obligatoires doivent être remplis"]);
            return;
        }

        if ($taille !== null && ($taille < 1.00 || $taille > 2.50)) {
            $this->reponseJSON(400, ["message" => "La taille doit être comprise entre 1.00 m et 2.50 m"]);
            return;
        }

        if ($poids !== null && ($poids < 15 || $poids > 300)) {
            $this->reponseJSON(400, ["message" => "Le poids doit être compris entre 15 kg et 300 kg"]);
            return;
        }

        // Ajouter le joueur
        try {
            $this->joueurModel->ajouterJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille, $poids, $statut, $position_preferee, $commentaire);
            $this->reponseJSON(201, ["message" => "Joueur ajouté avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de l'ajout du joueur", "error" => $e->getMessage()]);
        }
    }

    // Modifier un joueur
    public function modifier_joueur($numero_licence) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            $this->reponseJSON(400, ["message" => "Données JSON invalides"]);
            return;
        }
        $existingJoueur = $this->joueurModel->getJoueurByNumeroLicence($numero_licence);

        $nom = trim($input['nom'] ?? $existingJoueur['nom']);
        $prenom = trim($input['prenom'] ?? $existingJoueur['prenom']);
        $date_naissance = $input['date_naissance'] ?? $existingJoueur['date_naissance'];
        $taille = isset($input['taille']) ? (float)$input['taille'] : $existingJoueur['taille'];
        $poids = isset($input['poids']) ? (float)$input['poids'] : $existingJoueur['poids'];
        $statut = $input['statut'] ?? 'Actif';
        $position_preferee = trim($input['position_preferee'] ?? $existingJoueur['position_preferee']);
        $commentaire = trim($input['commentaire'] ?? $existingJoueur['commentaire']);

        if (empty($nom) || empty($prenom) || empty($date_naissance)) {
            $this->reponseJSON(400, ["message" => "Tous les champs obligatoires: nom, prenom doivent être remplis"]);
            return;
        }

        try {
            $this->joueurModel->modifierJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille, $poids, $statut, $position_preferee, $commentaire);
            $this->reponseJSON(200, ["message" => "Joueur modifié avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la modification du joueur", "error" => $e->getMessage()]);
        }
    }

    // Supprimer un joueur
    public function supprimer_joueur($numero_licence) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->reponseJSON(405, ["message" => "Méthode non autorisée"]);
            return;
        }

        try {
            if ($this->joueurModel->estJoueurSelectionne($numero_licence)) {
                $this->reponseJSON(400, ["message" => "Le joueur est dans une sélection en cours et ne peut pas être supprimé"]);
                return;
            }

            $this->joueurModel->supprimerJoueur($numero_licence);
            $this->reponseJSON(200, ["message" => "Joueur supprimé avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la suppression du joueur", "error" => $e->getMessage()]);
        }
    }

    // Obtenir les statistiques d'un joueur
    public function getStatistiquesJoueur($numero_licence) {
        try {
            $stats = $this->joueurModel->getStatistiquesJoueur($numero_licence);
            $this->reponseJSON(200, $stats);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des statistiques", "error" => $e->getMessage()]);
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
