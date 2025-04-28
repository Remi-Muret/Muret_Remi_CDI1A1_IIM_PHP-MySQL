<?php
session_start(); // Démarrage ou reprise de la session
require_once("db.php"); // Récupération de la connexion

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
    $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $check->execute(['email' => $email]);
    if ($check->rowCount() > 0) {
        echo "This email already exists.";
        exit;
    }

    // Insertion du nouvel utilisateur en BDD avec mot de passe haché
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
    $stmt->execute([
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    // Récupération du nouvel utilisateur
    $userId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Stockage en session
    $_SESSION["id"] = $user["id"];
    $_SESSION["email"] = $user["email"];

    // Redirection
    header("Location: sets.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sign in</title>
    <style>
        /* Réinitialisation de base et styles globaux */
        body {
            background: linear-gradient(135deg, #14A8E9, #E01C2F);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.25);
            text-align: center;
            width: 320px;
        }
        h1 {
            margin-bottom: 24px;
            font-size: 28px;
            color: #3A519B;
            font-weight: bold;
        }

        /* --- Style du formulaire --- */
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
            font-weight: bold;
            color: #3A519B;
        }
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3A519B;
            outline: none;
        }
        button {
            background-color: #3A519B;
            color: white;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #E01C2F;
        }

        /* --- Style du bouton de retour --- */
        .return-btn {
            position: absolute;
            top: 200px;
            left: 450px;
            font-size: 28px;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            border-radius: 50%;
            background-color: #fff;
            color: #3A519B;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            transition: background-color 0.3s ease, transform 0.3s ease, color 0.3s ease;
            transform: scaleX(-1);
            cursor: pointer;
            text-decoration: none;
        }
        .return-btn:hover {
            background-color: #E01C2F;
            color: #fff;
            transform: translateX(-5px) scaleX(-1);
        }
        </style>
</head>
<body>
    <!-- Bouton retour -->
    <a href="index.php" id="return-btn" class="return-btn">&#10140;</a>

    <div class="container">
        <h1>Create account</h1>

        <!-- Formulaire d'inscription -->
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Sign up</button>
        </form>
    </div>
</body>

</html>
