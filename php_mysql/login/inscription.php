<?php
session_start(); // Démarrage ou reprise de la session
require_once("connexion.php"); // Récupération de la connexion

// Vérification si le formulaire a été soumis
if ($_POST) {
    // Nettoyage des données envoyées via le formulaire
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide";
        exit;
    }

    // Vérification que l'email n'est pas déjà enregistré en BDD
    $check = $pdo->prepare("SELECT * FROM user WHERE email = :email");
    $check->execute(['email' => $email]);
    if ($check->rowCount() > 0) {
        echo "Cet email est déjà utilisé.";
        exit;
    }

    // Insertion du nouvel utilisateur en BDD avec mot de passe haché
    $stmt = $pdo->prepare("INSERT INTO user (email, password) VALUES (:email, :password)");
    $stmt->execute([
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    // Récupération du nouvel utilisateur
    $userId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Stockage en session
    $_SESSION["id"] = $user["id"];
    $_SESSION["email"] = $user["email"];

    // Redirection
    header("Location: accueil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

    <h1>Créer un compte</h1>

    <!-- Formulaire d'inscription -->
    <form method="POST">
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" placeholder="Email" required><br><br>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" placeholder="Mot de passe" required><br><br>

        <input type="submit" value="Inscription">
    </form>

</body>
</html>
