<?php
session_start();

function verifierUtilisateurConnecte() {
    if (!isset($_SESSION['token'])) {
        // Si l'utilisateur n'est pas connecté, rediriger vers l'accueil
        header("Location: /FootAPI/gestion_api_front/accueil");
        exit;
    }
}
?>