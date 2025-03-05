<?php

namespace App\Modeles;

use App\Config\Database;

class Utilisateur {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function trouverParEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }



    public function mettreAJourInfos($id, $nom, $prenom, $nomEquipe) {
        $stmt = $this->pdo->prepare("
        UPDATE utilisateur 
        SET nom = :nom, prenom = :prenom, nom_equipe = :nom_equipe 
        WHERE id_utilisateur = :id
    ");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'nom_equipe' => $nomEquipe,
            'id' => $id
        ]);
    }

}
?>