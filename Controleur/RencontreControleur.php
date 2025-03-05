<?php
namespace App\Controleurs;

use App\Modeles\Rencontre;

class RencontreControleur {

    private $rencontreModel;

    public function __construct() {
        $this->rencontreModel = new Rencontre();
    }

    // Récupérer les détails d'une rencontre par ID
    public function getRencontreById($id_rencontre) {
        try {
            return $this->rencontreModel->getRencontreById($id_rencontre);
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de la rencontre : " . $e->getMessage());
            return null;
        }
    }


    // Afficher la liste des rencontres
    public function liste_rencontres() {
        try {
            // Récupérer les rencontres depuis le modèle
            return $this->rencontreModel->getAllRencontres();
        } catch (\Exception $e) {
            // Gérer les erreurs
            error_log("Erreur lors de la récupération des rencontres : " . $e->getMessage());
            return [];
        }
    }

    // Afficher le formulaire d'ajout et gérer la soumission
    public function ajouter_rencontre() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $equipe_adverse = trim($_POST['equipe_adverse']);
            $date_rencontre = $_POST['date_rencontre'];
            $heure_rencontre = $_POST['heure_rencontre'];
            $lieu = $_POST['lieu'];

            // Validation des champs
            if (empty($equipe_adverse) || empty($date_rencontre) || empty($heure_rencontre) || empty($lieu)) {
                echo "Tous les champs obligatoires doivent être remplis !";
                return;
            }

            // Ajouter la rencontre via le modèle
            try {
                $this->rencontreModel->ajouterRencontre($equipe_adverse, $date_rencontre, $heure_rencontre, $lieu);
                // Redirection vers la liste des rencontres après succès
                header("Location: /football_manager/rencontres");
                exit();
            } catch (\Exception $e) {
                echo "Erreur lors de l'ajout de la rencontre : " . $e->getMessage();
            }
        }
    }

    // Modifier une rencontre
    public function modifier_rencontre($id_rencontre) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $equipe_adverse = trim($_POST['equipe_adverse']);
            $date_rencontre = $_POST['date_rencontre'];
            $heure_rencontre = $_POST['heure_rencontre'];
            $lieu = $_POST['lieu'];

            // Validation des champs
            if (empty($equipe_adverse) || empty($date_rencontre) || empty($heure_rencontre) || empty($lieu)) {
                echo "Tous les champs obligatoires doivent être remplis !";
                return;
            }

            // Modifier la rencontre via le modèle
            try {
                $this->rencontreModel->modifierRencontre($id_rencontre, $equipe_adverse, $date_rencontre, $heure_rencontre, $lieu);

                header("Location: /football_manager/rencontres");
                exit;
            } catch (\Exception $e) {
                echo "Erreur lors de la modification de la rencontre : " . $e->getMessage();
            }
        } else {
            // Récupérer les informations de la rencontre pour pré-remplir le formulaire
            try {
                return $this->rencontreModel->getRencontreById($id_rencontre);
            } catch (\Exception $e) {
                echo "Erreur lors de la récupération des informations de la rencontre : " . $e->getMessage();
                return null;
            }
        }
    }

    // Supprimer une rencontre
    public function supprimer_rencontre($id_rencontre) {
        try {
            $this->rencontreModel->supprimerRencontre($id_rencontre);

            header("Location: /football_manager/rencontres");
            exit;
        } catch (\Exception $e) {
            echo "Erreur lors de la suppression de la rencontre : " . $e->getMessage();
        }
    }

    public function ajouter_resultat() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rencontre = $_POST['id_rencontre'];
            $score_equipe = (int)$_POST['score_equipe'];
            $score_adverse = (int)$_POST['score_adverse'];

            $resultat = 'Nul';
            if ($score_equipe > $score_adverse) {
                $resultat = 'Victoire';
            } elseif ($score_equipe < $score_adverse) {
                $resultat = 'Défaite';
            }

            try {
                $this->rencontreModel->mettreAJourResultat($id_rencontre, $score_equipe, $score_adverse, $resultat);
                header("Location: /football_manager/rencontres");
                exit;
            } catch (\Exception $e) {
                echo "Erreur lors de l'ajout du résultat : " . $e->getMessage();
            }
        } else {
            $id_rencontre = $_GET['id_rencontre'] ?? null;
            if ($id_rencontre) {
                try {
                    return $this->rencontreModel->getRencontreById($id_rencontre);
                } catch (\Exception $e) {
                    echo "Erreur lors de la récupération de la rencontre : " . $e->getMessage();
                    return null;
                }
            }
        }
    }

    public function statistiquesRencontres() {
        try {
            return $this->rencontreModel->getStatistiquesRencontres();
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des statistiques des rencontres : " . $e->getMessage());
            return [];
        }
    }

}
