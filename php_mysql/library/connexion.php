<?php
// D�finition des param�tres de connexion � la base de donn�es
$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';
$port = 3306;

try {
    // Cr�ation d'une instance PDO avec les informations de connexion
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configuration du mode d'erreur pour g�n�rer des exceptions en cas de probl�me
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Affiche le contenu de l'instance PDO
    var_dump ($pdo);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
