<?php
namespace Controleur;
require_once '../Modele/Selection.php';
require_once '../Modele/Rencontre.php';
require_once '../Modele/Joueur.php';
use Modele\Selection;
use Modele\Rencontre;
use Modele\Joueur;

class SelectionControleur {
    private $selectionModel;
    private $rencontreModel;
    private $joueurModel;

    public function __construct() {
        $this->selectionModel = new Selection();
        $this->joueurModel = new Joueur();
        $this->rencontreModel = new Rencontre();
    }

    // Récupérer les joueurs actifs
    public function get_joueurs_actifs() {
        try {
            return $this->joueurModel->getJoueursActifs();
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des joueurs actifs", "error" => $e->getMessage()]);
        }
    }

    // Récupère les joueurs pour une rencontre
    public function get_joueurs_selectionnes($id_rencontre) {
        try {
            return $this->selectionModel->getJoueursSelectionnes($id_rencontre);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la récupération des joueurs sélectionnés", "error" => $e->getMessage()]);
        }
    }
    public function ajouter_selection($id_rencontre, $joueurs)
    {
        foreach ($joueurs as $joueur) {
            $id_joueur = htmlspecialchars($joueur['id_joueur']);
            $poste = htmlspecialchars($joueur['poste']);

            // Requête pour insérer chaque joueur sélectionné
            $sql = "INSERT INTO Participer (id_rencontre, id_joueur, poste) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE poste = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_rencontre, $id_joueur, $poste, $poste]);
        }

        return true;
    }

    public function ajouterSelection($id_rencontre, $numero_licence, $poste) {
        try {
            $stmt = $this->db->prepare("
            INSERT INTO selection (id_rencontre, numero_licence, poste) 
            VALUES (:id_rencontre, :numero_licence, :poste)
        ");
            $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->bindParam(':poste', $poste, PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de l'ajout de la sélection : " . $e->getMessage());
        }
    }

    public function modifier_selection($id_rencontre, $data) {
        try {
            $this->valider_selection($id_rencontre, $data);
            $this->reponseJSON(200, ["message" => "Modification effectuée avec succès"]);
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la modification", "error" => $e->getMessage()]);
        }
    }

    public function valider_selection($id_rencontre, $data) {
        try {
            $rencontre = $this->rencontreModel->getRencontre($id_rencontre);
            $date_rencontre = $rencontre['date_rencontre'] . ' ' . $rencontre['heure_rencontre'];
            $is_past = strtotime($date_rencontre) < time();

            if ($is_past && isset($data['notes'])) {
                foreach ($data['notes'] as $id_joueur => $note) {
                    // Log the values for debugging
                    error_log("Updating note for player $id_joueur with note $note");
                    $this->selectionModel->modifierNote($id_rencontre, $id_joueur, $note);
                }
            } elseif (!$is_past) {
                if (isset($data['notes'])) {
                    throw new \Exception("Les notes ne peuvent pas être modifiées avant la rencontre.");
                }

                $postes_postes = $data['postes_postes'] ?? [];

                // Define mandatory positions
                $postes_obligatoires = [
                    "GB", "DG", "DCG", "DCD", "DD", "MD", "MCG", "MCD", "AD", "AG", "BU"
                ];

                // Get all selected players
                $joueursSelectionnes = $this->selectionModel->getJoueursSelectionnes($id_rencontre);

                // Get already assigned positions
                $postesAssignes = array_column($joueursSelectionnes, 'poste');

                // Find remaining mandatory positions
                $postesRestants = array_diff($postes_obligatoires, $postesAssignes);

                // Get players without a position or with a position starting with 'R'
                $joueursSansPoste = array_filter($joueursSelectionnes, function($joueur) {
                    return empty($joueur['poste']) || strpos($joueur['poste'], 'R') === 0;
                });

                // Assign remaining mandatory positions to players without a position
                foreach ($joueursSansPoste as $index => $joueur) {
                    if (isset($postesRestants[$index])) {
                        $postes_postes[$joueur['numero_licence']] = $postesRestants[$index];
                    }
                }

                // Assign new positions
                foreach ($postes_postes as $numero_licence => $poste) {
                    if (empty($poste)) {
                        $poste = null;  // Replace the position with NULL
                    }
                    $this->selectionModel->modifierPoste($id_rencontre, $numero_licence, $poste);
                }

                // Verify that all mandatory positions are filled
                $postesAssignes = array_column($this->selectionModel->getJoueursSelectionnes($id_rencontre), 'poste');
                $postesRestants = array_diff($postes_obligatoires, $postesAssignes);
                if (!empty($postesRestants)) {
                    throw new \Exception("Tous les postes obligatoires doivent être assignés.");
                }
            } else {
                throw new \Exception("Modification non autorisée pour cette rencontre.");
            }
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la validation de la sélection", "error" => $e->getMessage()]);
        }
    }

    public function supprimer_selection($id_rencontre) {
        try {
            $joueursSelectionnes = $this->selectionModel->getJoueursSelectionnes($id_rencontre);
            $joueursTitulaires = array_filter($joueursSelectionnes, function($joueur) {
                return strpos($joueur['poste'], 'R') !== 0;
            });

            if (count($joueursTitulaires) != 11) {
                $this->selectionModel->supprimerSelection($id_rencontre);
                $this->reponseJSON(200, ["message" => "Sélection supprimée avec succès"]);
            } else {
                $this->reponseJSON(400, ["message" => "La sélection ne peut pas être supprimée car elle est complète"]);
            }
        } catch (\Exception $e) {
            $this->reponseJSON(500, ["message" => "Erreur lors de la vérification et de la suppression de la sélection", "error" => $e->getMessage()]);
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