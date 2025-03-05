<?php
namespace App\Modeles;

use PDO;
use App\Config\Database;
use Exception;

class Joueur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Récupérer tous les joueurs
    public function getAllJoueurs() {
        $stmt = $this->db->prepare("SELECT * FROM joueur ORDER BY nom, prenom");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les joueurs actifs
    public function getJoueursActifs() {
        $stmt = $this->db->prepare("SELECT * FROM joueur WHERE statut = 'Actif' ORDER BY nom, prenom");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un joueur
    public function ajouterJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille = null, $poids = null, $statut = 'Actif', $position_preferee = null, $commentaire = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO joueur (numero_licence, nom, prenom, date_naissance, taille, poids, statut, position_preferee, commentaire) 
                                        VALUES (:numero_licence, :nom, :prenom, :date_naissance, :taille, :poids, :statut, :position_preferee, :commentaire)");

            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':date_naissance', $date_naissance, PDO::PARAM_STR);
            $stmt->bindParam(':taille', $taille, PDO::PARAM_STR);
            $stmt->bindParam(':poids', $poids, PDO::PARAM_STR);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
            $stmt->bindParam(':position_preferee', $position_preferee, PDO::PARAM_STR);
            $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);

            $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'insertion du joueur : " . $e->getMessage());
        }
    }

    // Modifier un joueur
    public function modifierJoueur($numero_licence, $nom, $prenom, $date_naissance, $taille = null, $poids = null, $statut = 'Actif', $position_preferee = null, $commentaire = null) {
        try {
            $stmt = $this->db->prepare("UPDATE joueur SET nom = :nom, prenom = :prenom, date_naissance = :date_naissance, 
                                        taille = :taille, poids = :poids, statut = :statut, position_preferee = :position_preferee, 
                                        commentaire = :commentaire WHERE numero_licence = :numero_licence");

            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':date_naissance', $date_naissance, PDO::PARAM_STR);
            $stmt->bindParam(':taille', $taille, PDO::PARAM_STR);
            $stmt->bindParam(':poids', $poids, PDO::PARAM_STR);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
            $stmt->bindParam(':position_preferee', $position_preferee, PDO::PARAM_STR);
            $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);

            $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du joueur : " . $e->getMessage());
        }
    }

    // Supprimer un joueur
    public function supprimerJoueur($numero_licence) {
        try {
            $stmt = $this->db->prepare("DELETE FROM joueur WHERE numero_licence = :numero_licence");
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression du joueur : " . $e->getMessage());
        }
    }

    // Récupérer un joueur par son numéro de licence
    public function getJoueurByNumeroLicence($numero_licence) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM joueur WHERE numero_licence = :numero_licence");
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération du joueur : " . $e->getMessage());
        }
    }

    // Récupérer les statistiques d'un joueur
    public function getStatistiquesJoueur($numero_licence) {
        try {
            $sql = "
                SELECT 
                    COUNT(CASE WHEN s.poste NOT IN ('R1', 'R2', 'R3', 'R4', 'R5') THEN 1 END) AS titularisations,
                    COUNT(CASE WHEN s.poste LIKE 'R%' THEN 1 END) AS remplacements,
                    AVG(CASE WHEN s.note IS NOT NULL THEN s.note END) AS moyenne_notes,
                    COUNT(CASE WHEN r.resultat = 'Victoire' THEN 1 END) AS victoires,
                    COUNT(r.id_rencontre) AS total_matchs
                FROM selection s
                LEFT JOIN rencontre r ON s.id_rencontre = r.id_rencontre
                WHERE s.numero_licence = :numero_licence
                AND CONCAT(r.date_rencontre, ' ', r.heure_rencontre) < NOW()
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculer le pourcentage de victoires
            $result['pourcentage_victoires'] = $result['total_matchs'] > 0
                ? round(($result['victoires'] / $result['total_matchs']) * 100, 2)
                : 0;

            return [
                'titularisations' => $result['titularisations'] ?? 0,
                'remplacements' => $result['remplacements'] ?? 0,
                'moyenne_notes' => $result['moyenne_notes'] !== null ? round($result['moyenne_notes'], 2) : 0,
                'pourcentage_victoires' => $result['pourcentage_victoires'],
            ];

        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
            return [
                'titularisations' => 0,
                'remplacements' => 0,
                'moyenne_notes' => 0,
                'pourcentage_victoires' => 0,
            ];
        }
    }

    // Vérifier si un joueur est sélectionné pour un match passé
    public function estJoueurSelectionne($numero_licence, $matchAVenir = false) {
        try {
            $operator = $matchAVenir ? '>' : '<';
            $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM selection s
            JOIN rencontre r ON s.id_rencontre = r.id_rencontre
            WHERE s.numero_licence = :numero_licence 
            AND CONCAT(r.date_rencontre, ' ', r.heure_rencontre) $operator NOW()
        ");
            $stmt->bindParam(':numero_licence', $numero_licence, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de la sélection du joueur : " . $e->getMessage());
        }
    }
}
?>
