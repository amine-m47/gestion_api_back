<?php
namespace App\Controleurs;

use App\Modeles\Joueur;

class JoueurControleur {

    private $joueurModel;

    public function __construct() {
        $this->joueurModel = new Joueur();
    }

    // Afficher la liste des joueurs
    public function liste_joueurs() {
        try {
            // Récupérer les joueurs depuis le modèle
            return $this->joueurModel->getAllJoueurs();
        } catch (\Exception $e) {
            // Gérer les erreurs
            error_log("Erreur lors de la récupération des joueurs : " . $e->getMessage());
            return [];
        }
    }

    // Afficher la liste des joueurs actifs
    public function liste_joueurs_actifs() {
        try {
            // Récupérer les joueurs actifs depuis le modèle
            return $this->joueurModel->getJoueursActifs();
        } catch (\Exception $e) {
            // Gérer les erreurs
            error_log("Erreur lors de la récupération des joueurs actifs : " . $e->getMessage());
            return [];
        }
    }

    // Afficher le formulaire d'ajout et gérer la soumission
    public function ajouter_joueur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $numero_licence = trim($_POST['numero_licence']);
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $date_naissance = $_POST['date_naissance'];
            $taille = isset($_POST['taille']) ? (float)$_POST['taille'] : null;
            $poids = isset($_POST['poids']) ? (float)$_POST['poids'] : null;
            $statut = $_POST['statut'] ?? 'Actif';
            $position_preferee = isset($_POST['position_preferee']) ? trim($_POST['position_preferee']) : null;
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;

            // Validation des champs
            if (empty($nom) || empty($prenom) || empty($date_naissance)) {
                echo "Tous les champs obligatoires doivent être remplis !";
                return;
            }

            if ($taille < 1.00 || $taille > 2.50) {
                echo"La taille doit être comprise entre 1.00 mètre et 2.50 mètres.";
                return;
             }

            if ($poids !== null && ($poids < 15 || $poids > 300)) {
                echo "Le poids doit être compris entre 15 kg et 300 kg.";
                return;
            }

            // Ajouter le joueur via le modèle
            try {
                $this->joueurModel->ajouterJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille, $poids, $statut, $position_preferee, $commentaire);

                header("Location: /football_manager/joueurs");
                exit;
            } catch (\Exception $e) {
                echo "Erreur lors de l'ajout du joueur : " . $e->getMessage();
            }
        }
    }

    // Modifier un joueur
    public function modifier_joueur($numero_licence) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $date_naissance = $_POST['date_naissance'];
            $taille = isset($_POST['taille']) ? (float)$_POST['taille'] : null;
            $poids = isset($_POST['poids']) ? (float)$_POST['poids'] : null;
            $statut = $_POST['statut'] ?? 'Actif';
            $position_preferee = isset($_POST['position_preferee']) ? trim($_POST['position_preferee']) : null;
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;

            // Validation des champs
            if (empty($nom) || empty($prenom) || empty($date_naissance)) {
                echo "Tous les champs obligatoires doivent être remplis !";
                return;
            }

            if ($taille < 1.00 || $taille > 2.50) {
                echo"La taille doit être comprise entre 1.00 mètre et 2.50 mètres.";
                return;
            }

            // Modifier le joueur via le modèle
            try {
                $this->joueurModel->modifierJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille, $poids, $statut, $position_preferee, $commentaire);

                header("Location: /football_manager/joueurs");
                exit;
            } catch (\Exception $e) {
                echo "Erreur lors de la modification du joueur : " . $e->getMessage();
            }
        } else {
            // Récupérer les informations du joueur pour pré-remplir le formulaire
            try {
                return $this->joueurModel->getJoueurByNumeroLicence($numero_licence);
            } catch (\Exception $e) {
                echo "Erreur lors de la récupération des informations du joueur : " . $e->getMessage();
                return null;
            }
        }
    }

    // Supprimer un joueur
    public function supprimer_joueur($numero_licence) {
        try {
            if ($this->joueurModel->estJoueurSelectionne($numero_licence)) {
                echo "<p style='color: red'>Le joueur ne peut pas être supprimé car il est dans une selection en cours.</p>";
            } else {
                $this->joueurModel->supprimerJoueur($numero_licence);

                header("Location: /football_manager/joueurs");
                exit;
            }
        } catch (\Exception $e) {
            echo "Erreur lors de la suppression du joueur : " . $e->getMessage();
        }
    }

    public function getStatistiquesJoueur($numero_licence) {
        try {
            return $this->joueurModel->getStatistiquesJoueur($numero_licence);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des statistiques du joueur : " . $e->getMessage());
            return [
                'titularisations' => 0,
                'remplacements' => 0,
                'moyenne_notes' => 0,
                'pourcentage_victoires' => 0,
            ];
        }
    }
    public function estJoueurSelectionnePourMatchAVenir($numero_licence) {
        return $this->joueurModel->estJoueurSelectionne($numero_licence, true);
    }
}
