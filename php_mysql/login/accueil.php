<?php
session_start(); // Démarrage ou reprise de la session

// Vérification si l'utilisateur est connecté avec la session "user"
if (!isset($_SESSION["user"])) {
    header("Location: index.php"); // Redirection
    exit;
}

// Vérification si l'utilisateur a cliqué sur le lien de déconnexion avec le paramètre GET
if(isset($_GET["action"]) && $_GET["action"] == "disconnection") {
    session_unset(); // Vidage de la session
    header("location: index.php"); // Redirection
}
?>

<!-- Message de bienvenue -->
<h1>Bienvenue, <?= htmlspecialchars($_SESSION["user"]["email"]) ?> !</h1>
<!-- Lien de déconnexion -->
<p><a href="?action=disconnection">Se déconnecter</a></p>
