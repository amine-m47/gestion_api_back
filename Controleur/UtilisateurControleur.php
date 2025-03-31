<?php
namespace App\Controleurs;

use App\Modele\Utilisateur;

class UtilisateurControleur {
    private $utilisateurModel;
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->utilisateurModel = new Utilisateur(); // Créer une instance du modèle Utilisateur
    }
    public function getInfosUtilisateur() {
        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['email'])) {
            $email = $_SESSION['email'];

            // Récupérer les informations depuis la base de données
            $user = $this->utilisateurModel->trouverParEmail($email);

            // Retourner les informations ou une valeur par défaut
            if ($user) {
                return $user;
            } else {
                return [
                    'prenom' => '',
                    'nom' => '',
                    'nom_equipe' => '',
                ];
            }
        } else {
            // Si l'utilisateur n'est pas connecté, retourner des valeurs par défaut
            return [
                'prenom' => '',
                'nom' => '',
                'nom_equipe' => '',
            ];
        }
    }

    public function modifierInfos() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier que les champs sont remplis
            $prenom = htmlspecialchars(trim($_POST['prenom']));
            $nom = htmlspecialchars(trim($_POST['nom']));
            $nomEquipe = htmlspecialchars(trim($_POST['nom_equipe']));

            // Validation des champs
            if (empty($prenom) || empty($nom) || empty($nomEquipe)) {
                return "Tous les champs sont obligatoires.";
            }

            // Récupérer l'utilisateur connecté
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];
                $user = $this->utilisateurModel->trouverParEmail($email);

                if ($user) {
                    // Mise à jour des informations
                    $this->utilisateurModel->mettreAJourInfos(
                        $user['id_utilisateur'],
                        $nom,
                        $prenom,
                        $nomEquipe
                    );
                    return "Informations mises à jour avec succès !";
                } else {
                    return "Utilisateur introuvable.";
                }
            } else {
                return "Aucun utilisateur connecté.";
            }
        }
        return "";
    }

}
