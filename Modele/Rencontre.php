<?php
namespace App\Modeles;

use PDO;
use App\Config\Database;

class Rencontre {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Récupérer toutes les rencontres
    public function getAllRencontres() {
        $stmt = $this->db->prepare("SELECT * FROM rencontre ORDER BY date_rencontre, heure_rencontre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Ajouter une rencontre
    public function ajouterRencontre($equipe_adverse, $date_rencontre, $heure_rencontre, $lieu, $resultat = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO rencontre (equipe_adverse, date_rencontre, heure_rencontre, lieu, score_equipe, score_adverse, resultat) 
                                    VALUES (:equipe_adverse, :date_rencontre, :heure_rencontre, :lieu, :score_equipe, :score_adverse, :resultat)");

            $stmt->bindParam(':equipe_adverse', $equipe_adverse);
            $stmt->bindParam(':date_rencontre', $date_rencontre);
            $stmt->bindParam(':heure_rencontre', $heure_rencontre);
            $stmt->bindParam(':lieu', $lieu);
            $stmt->bindValue(':score_equipe', null, PDO::PARAM_NULL);
            $stmt->bindValue(':score_adverse', null, PDO::PARAM_NULL);
            $stmt->bindParam(':resultat', $resultat);

            // Exécuter la requête
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'ajout de la rencontre : " . $e->getMessage());
        }
    }

    // Modifier une rencontre
    public function modifierRencontre($id_rencontre, $equipe_adverse, $date_rencontre, $heure_rencontre, $lieu, $resultat = null) {
        try {
            $stmt = $this->db->prepare("UPDATE rencontre 
                                        SET equipe_adverse = :equipe_adverse, date_rencontre = :date_rencontre, 
                                            heure_rencontre = :heure_rencontre, lieu = :lieu, resultat = :resultat 
                                        WHERE id_rencontre = :id_rencontre");

            // Lier les paramètres aux valeurs
            $stmt->bindParam(':id_rencontre', $id_rencontre);
            $stmt->bindParam(':equipe_adverse', $equipe_adverse);
            $stmt->bindParam(':date_rencontre', $date_rencontre);
            $stmt->bindParam(':heure_rencontre', $heure_rencontre);
            $stmt->bindParam(':lieu', $lieu);
            $stmt->bindParam(':resultat', $resultat);

            // Exécuter la requête
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la modification de la rencontre : " . $e->getMessage());
        }
    }

    // Supprimer une rencontre
    public function supprimerRencontre($id_rencontre) {
        try {
            $stmt = $this->db->prepare("DELETE FROM rencontre WHERE id_rencontre = :id_rencontre");
            $stmt->bindParam(':id_rencontre', $id_rencontre);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la suppression de la rencontre : " . $e->getMessage());
        }
    }

    public function mettreAJourResultat($id_rencontre, $score_equipe, $score_adverse, $resultat) {
        $sql = "UPDATE rencontre SET score_equipe = ?, score_adverse = ?, resultat = ? WHERE id_rencontre = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$score_equipe, $score_adverse, $resultat, $id_rencontre]);
    }

    // Récupérer une rencontre par son ID
    public function getRencontreById($id_rencontre) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM rencontre WHERE id_rencontre = :id_rencontre");
            $stmt->bindParam(':id_rencontre', $id_rencontre);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Retourner la rencontre sous forme de tableau associatif
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la récupération de la rencontre : " . $e->getMessage());
        }
    }
    public function getStatistiquesRencontres() {
        try {
            // Requête SQL pour obtenir le nombre de victoires, défaites et nuls
            $sql = "
            SELECT
                COUNT(CASE WHEN r.resultat = 'Victoire' THEN 1 END) AS victoires,
                COUNT(CASE WHEN r.resultat = 'Défaite' THEN 1 END) AS defaites,
                COUNT(CASE WHEN r.resultat = 'Nul' THEN 1 END) AS nuls,
                COUNT(*) AS total_matchs
            FROM rencontre r
            WHERE r.resultat IS NOT NULL;
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();

            // Calcul des pourcentages
            if ($result['total_matchs'] > 0) {
                $victoires_pourcentage = round(($result['victoires'] / $result['total_matchs']) * 100, 2);
                $defaites_pourcentage = round(($result['defaites'] / $result['total_matchs']) * 100, 2);
                $nuls_pourcentage = round(($result['nuls'] / $result['total_matchs']) * 100, 2);
            } else {
                $victoires_pourcentage = $defaites_pourcentage = $nuls_pourcentage = 0;
            }

            // Retourner les statistiques
            return [
                'total_matchs' => $result['total_matchs'],
                'victoires_pourcentage' => $victoires_pourcentage,
                'victoires' => $result['victoires'],
                'defaites_pourcentage' => $defaites_pourcentage,
                'defaites' => $result['defaites'],
                'nuls_pourcentage' => $nuls_pourcentage,
                'nuls' => $result['nuls']
            ];
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques des rencontres : " . $e->getMessage());
            return [];
        }
    }

}
