<?php
namespace Modele;
require_once '../Config/database.php';
use Config\Database;

class Utilisateur {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getUtilisateur($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur");
        $stmt->execute(['id_utilisateur' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }



    public function modifierUtilisateur($id, $nom, $prenom, $nomEquipe) {
        $stmt = $this->pdo->prepare("
        UPDATE utilisateur 
        SET nom = :nom, prenom = :prenom, nom_equipe = :nom_equipe 
        WHERE id_utilisateur = :id_utilisateur
    ");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'nom_equipe' => $nomEquipe,
            'id_utilisateur' => $id
        ]);
    }

}
?>