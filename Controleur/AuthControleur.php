<?php

namespace App\Controleurs;

use App\Modeles\Utilisateur;
use App\Config\Database;
class AuthControleur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($email, $password) {
        $utilisateurModel = new Utilisateur($this->db);
        $utilisateur = $utilisateurModel->trouverParEmail($email);

        if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
            session_start();
            $_SESSION['utilisateur_id'] = $utilisateur['id_utilisateur'];
            $_SESSION['email'] = $utilisateur['email'];
            header("Location: /football_manager/accueil");
            exit;
        }
    }
    
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: /football_manager/accueil");
        exit;
    }
}
?>