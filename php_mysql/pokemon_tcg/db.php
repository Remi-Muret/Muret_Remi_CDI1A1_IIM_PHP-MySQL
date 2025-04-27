<?php
// Définition des paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'pokemon_tcg';
$username = 'root';
$password = '';
$port = 3306;

try {
    // Création d'une instance PDO avec les informations de connexion
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configuration du mode d'erreur pour générer des exceptions en cas de problème
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
