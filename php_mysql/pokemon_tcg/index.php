<?php
session_start(); // Démarrage ou reprise de la session
require_once("db.php"); // Récupération de la connexion

$error = ""; // Initialisation du message d'erreur

// Vérification si le formulaire a été soumis
if ($_POST) {
    // Nettoyage des données envoyées via le formulaire
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Requête SQL sécurisée
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence de l'utilisateur en BDD et correspondance du mot de passe
    if ($user && password_verify($password, $user['password'])) {
        // Stockage des informations de l'utilisateur en session
        $_SESSION["user"] = [
            "id" => $user["id"],
            "email" => $user["email"]
        ];
        header("Location: sets.php"); // Redirection
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connection</title>
    <style>
        body {
            background: linear-gradient(135deg, #14A8E9, #EED149);
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
        </style>
</head>
<body>
    <div class="container">

        <h1>Connection</h1>

        <?php if ($error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Log in</button>
        </form>

        <br>

        <!-- Bouton de redirection vers la page d'inscription -->
        <form action="sign_up.php" method="GET">
            <button type="submit">Create account</button>
        </form>
    </div>
</body>
</html>
