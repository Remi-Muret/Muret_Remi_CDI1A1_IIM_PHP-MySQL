<?php
require 'db.php'; // Connexion à la BDD

// Préparation de la requête pour récupérer l'image
$stmt = $pdo->query("SELECT json_data FROM sets LIMIT 1");
$set = $stmt->fetch(PDO::FETCH_ASSOC);

$imageBack = ''; // Initialisation de l'image

if ($set) {
    $jsonData = json_decode($set['json_data'], true);
    $imageBack = $jsonData['images']['symbol'] ?? '';
}
?>

<style>
.sidebar {
    position: fixed;
    width: 140px;
    height: 100vh;
    background-color: #3A519B;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
    color: #EED149;
    font-family: Anton, sans-serif;
    font-weight: bold;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.sidebar h2 {
    margin-bottom: 20px;
    font-size: 16px;
}
.sidebar ul {
    list-style: none;
    padding: 0;
    width: 100%;
}
.sidebar ul li {
    margin-top: 40px;
}
.sidebar ul li a {
    display: block;
    text-align: center;
    background-color: #EED149;
    color: #3A519B;
    text-decoration: none;
    padding: 10px;
    border-radius: 8px;
    font-size: 12px;
    transition: background-color 0.3s, color 0.3s;
    font-weight: bold;
}
.sidebar ul li a:hover {
    background-color: #E01C2F;
    color: white;
}

.sidebar img {
    width: 90px;
    height: auto;
    box-shadow: 0 8px 16px rgba(0,0,0,0.5);
    border-radius: 3px;
    margin-bottom: 40px;
    transition: background-color 0.3s ease, transform 0.3s ease, color 0.3s ease;
    cursor: pointer;
}
.sidebar img:hover {
    transform: rotate(-8deg);
}
</style>

<aside class="sidebar">
    <?php if ($imageBack): ?>
        <a href="sets.php">
            <img src="https://images.pokemontcg.io/card-back.png" alt="Back of Pokémon Card">
        </a>
    <?php else: ?>
        <p>Image not found</p>
    <?php endif; ?>

    <ul>
        <li><a href="set_cards.php?set=base1">Base set</a></li>
        <li><a href="set_cards.php?set=base2">Jungle set</a></li>
        <li><a href="set_cards.php?set=base3">Fossil set</a></li>
        <li><a href="favorite_cards.php">Favorites</a></li>
    </ul>
</aside>
