<?php
session_start();

function verifierUtilisateurConnecte() {
    if (isset($_SESSION['utilisateur_id'])) {
        // Si l'utilisateur n'est pas connecté, rediriger vers l'accueil
        header("Location: /football_manager/accueil");
        exit;
    }
}
?>