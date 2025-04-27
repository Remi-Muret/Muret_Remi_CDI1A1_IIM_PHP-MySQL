<?php
require_once("connexion.php"); // Récupération de la connexion

// Insertion d'un livre
if ($_POST) {
    $title = $_POST["title"];
    $author = $_POST["author"];
    $year_publication = $_POST["year_publication"];
    $available = $_POST["available"];

    try {
        $stmt = $pdo->prepare("INSERT INTO book (title, author, year_publication, available) 
                               VALUES (:title, :author, :year_publication, :available)");
        $stmt->execute([
            "title" => $title,
            "author" => $author,
            "year_publication" => $year_publication,
            "available" => $available
        ]);

        echo "Livre ajouté avec l'ID : " . $pdo->lastInsertId() . "<br>";
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

// Suppression d'un livre
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM book WHERE id = :id");
        $stmt->execute(["id" => $id]);
        echo "Livre supprimé<br>";
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

// Modification de la disponibilité
if (isset($_GET['action']) && $_GET['action'] == 'modify' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE book SET available = 1 - available WHERE id = :id");
        $stmt->execute(["id" => $id]);
        echo "Disponibilité modifiée<br>";
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

// Tri dynamique
$orderBy = "id"; // par défaut
$allowedSort = ["id", "year_publication", "title"];
if (isset($_GET["sort"]) && in_array($_GET["sort"], $allowedSort)) {
    $orderBy = $_GET["sort"];
}

// Lecture des livres triés
$stmt = $pdo->query("SELECT * FROM book ORDER BY $orderBy ASC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Livres publiés après 2000 par ordre alphabétique
$stmt2 = $pdo->prepare("SELECT * FROM book WHERE year_publication > :annee ORDER BY title ASC");
$stmt2->execute(["annee" => 1999]);
$filteredBooks = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue de livres</title>
</head>
<body>

<!-- Formulaire d’ajout de livre -->
<h2>Ajouter un livre</h2>

<form method="POST">
    <label for="title">Titre:</label>
    <input type="text" name="title" required>

    <label for="author">Auteur:</label>
    <input type="text" name="author" required>

    <label for="year_publication">Année de publication:</label>
    <input type="number" name="year_publication" required>

    <label for="available">Disponible:</label>
    <select name="available">
        <option value="1">Oui</option>
        <option value="0">Non</option>
    </select>

    <input type="submit" value="Ajouter">
</form>

<hr>

<!-- Tableau du catalogue complet -->
<h1>Catalogue complet</h1>

<p>Trier par : 
    <a href="?sort=id">ID</a> | 
    <a href="?sort=year_publication">Année</a> | 
    <a href="?sort=title">Titre</a>
</p>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Année</th>
            <th>Disponible</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book["title"]) ?></td>
                <td><?= htmlspecialchars($book["author"]) ?></td>
                <td><?= $book["year_publication"] ?></td>
                <td><?= $book["available"] ? "Oui" : "Non" ?></td>
                <td>
                    <a href="?id=<?= $book["id"] ?>&action=delete">Supprimer</a> |
                    <a href="?id=<?= $book["id"] ?>&action=modify">Changer disponibilité</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<hr>

<!-- Liste filtrée des livres après 2000 par ordre alphabétique -->
<h2>Livres publiés après 2000 par ordre alphabétique</h2>

<ul>
    <?php foreach ($filteredBooks as $book): ?>
        <li><?= htmlspecialchars($book["title"]) ?> - <?= htmlspecialchars($book["author"]) ?> (<?= $book["year_publication"] ?>)</li>
    <?php endforeach; ?>
</ul>

</body>
</html>
