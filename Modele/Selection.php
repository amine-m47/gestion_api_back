<?php
namespace Modele;
require_once '../Config/database.php';
use PDO;
use Config\Database;
use Exception; // Add this line

class Selection {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getJoueursSelectionnes($id_rencontre) {
        $stmt = $this->db->prepare("
            SELECT j.numero_licence, j.nom, j.prenom, j.position_preferee, s.poste, s.note
            FROM joueur j
            JOIN selection s ON j.numero_licence = s.numero_licence
            WHERE s.id_rencontre = :id_rencontre
        ");
        $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function modifierPoste($id_rencontre, $numero_licence, $poste) {
        try {
            // Vérification si la sélection existe
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM selection
                WHERE id_rencontre = :id_rencontre AND numero_licence = :numero_licence
            ");
            $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->execute();

            $existe = $stmt->fetchColumn();

            if (!empty($poste)) {
                // Mise à jour ou insertion selon l'existence de la sélection
                $sql = $existe ? "
                    UPDATE selection
                    SET poste = :poste
                    WHERE id_rencontre = :id_rencontre AND numero_licence = :numero_licence
                " : "
                    INSERT INTO selection (id_rencontre, numero_licence, poste)
                    VALUES (:id_rencontre, :numero_licence, :poste)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
                $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
                $stmt->bindParam(':poste', $poste, PDO::PARAM_STR);
                $stmt->execute();
            } elseif ($existe) {
                // Suppression si aucun poste n'est attribué
                $stmt = $this->db->prepare("
                    DELETE FROM selection
                    WHERE id_rencontre = :id_rencontre AND numero_licence = :numero_licence
                ");
                $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
                $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
                $stmt->execute();
            }
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du poste : " . $e->getMessage());
        }
    }

    public function modifierNote($id_rencontre, $id_joueur, $note) {
        try {
            // Ensure the note is within the allowed range (e.g., 0 to 10)
            if ($note < 0 || $note > 5) {
                throw new Exception("La note doit être comprise entre 0 et 5.");
            }

            // Replace empty note with NULL
            $note = empty($note) ? null : (int)$note;
            $stmt = $this->db->prepare("
            UPDATE selection
            SET note = :note
            WHERE id_rencontre = :id_rencontre AND numero_licence = :id_joueur
        ");
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
            $stmt->bindParam(':id_joueur', $id_joueur, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la note : " . $e->getMessage());
        }
    }

    public function getNotes($id_rencontre) {
        try {
            $stmt = $this->db->prepare("
                SELECT numero_licence, note
                FROM selection
                WHERE id_rencontre = :id_rencontre
            ");
            $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formater les résultats sous forme de tableau [id_joueur => note]
            $notes = [];
            foreach ($result as $row) {
                $notes[$row['numero_licence']] = $row['note'];
            }
            return $notes;
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de la récupération des notes : " . $e->getMessage());
        }
    }

    public function getNbNotes($id_rencontre) {
        try {
            $notes = $this->getNotes($id_rencontre);

            // Compter les notes non nulles
            $notesNonNulles = array_filter($notes, fn($note) => $note !== null);
            return count($notesNonNulles);
        } catch (\Exception $e) {
            throw new Exception("Erreur lors du comptage des joueurs notés : " . $e->getMessage());
        }
    }

    public function supprimerSelection($id_rencontre) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM selection
                WHERE id_rencontre = :id_rencontre
            ");
            $stmt->bindParam(':id_rencontre', $id_rencontre, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de la suppression de la sélection : " . $e->getMessage());
        }
    }
    public function ajouterOuModifier($id_rencontre, $numero_licence, $poste) {

        $stmt = $this->db->prepare("INSERT INTO selection (id_rencontre, numero_licence, poste) VALUES (?, ?, ?)");
        $stmt->execute([$id_rencontre, $numero_licence, $poste]);
    }

}
?>