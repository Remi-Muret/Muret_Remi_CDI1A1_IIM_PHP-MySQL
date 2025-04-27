<?php
session_start(); // Démarrage ou reprise de la session
require_once("connexion.php"); // Récupération de la connexion

// Vérification si le formulaire a été soumis
if ($_POST) {
    // Nettoyage des données envoyées via le formulaire
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide";
        exit;
    }

    // Requête SQL sécurisée
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence de l'utilisateur en BDD et correspondance du mot de passe
    if ($user && password_verify($password, $user['password'])) {
        // Stockage des informations de l'utilisateur en session
        $_SESSION["id"] = $user["id"];
        $_SESSION["email"] = $user["email"];
        header("Location: accueil.php"); // Redirection
        exit;
    } else {
        echo "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>

    <h1>Connexion</h1>

    <!-- Formulaire de connexion -->
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="password">Mot de passe:</label>
        <input type="password" name="password" required>

        <button type="submit">Se connecter</button>
    </form>

    <br>

    <!-- Bouton de redirection vers la page d'inscription -->
    <form action="inscription.php" method="GET">
        <button type="submit">Créer un compte</button>
    </form>

</body>
</html>
