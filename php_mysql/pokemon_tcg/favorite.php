<?php
require 'vendor/autoload.php'; // Charge automatique des classes Composer
require 'db.php'; // Connexion à la BDD

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['cardId'], $data['action'])) {
    $cardId = $data['cardId'];
    $action = $data['action'];
    
    // Fonction pour vérifier si la carte est déjà en favori
    function isFavorite($pdo, $cardId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE card_id = :card_id");
        $stmt->execute(['card_id' => $cardId]);
        return $stmt->fetchColumn() > 0;
    }

    // Fonction pour ajouter une carte en favori
    function addFavorite($pdo, $cardId) {
        $stmt = $pdo->prepare("INSERT INTO favorites (card_id) VALUES (:card_id)");
        $stmt->execute(['card_id' => $cardId]);
    }

    // Fonction pour retirer une carte des favoris
    function removeFavorite($pdo, $cardId) {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE card_id = :card_id");
        $stmt->execute(['card_id' => $cardId]);
    }

    // Fonction pour mettre à jour une carte favorite
    function updateFavorite($pdo, $oldCardId, $newCardId) {
        $stmt = $pdo->prepare("UPDATE favorites SET card_id = :new_card_id WHERE card_id = :old_card_id");
        $stmt->execute(['old_card_id' => $oldCardId, 'new_card_id' => $newCardId]);
    }

    if ($action === 'add') {
        // Ajouter la carte en favori si elle ne l'est pas
        if (!isFavorite($pdo, $cardId)) {
            addFavorite($pdo, $cardId);
            echo json_encode(['success' => true]);
        }
    } elseif ($action === 'remove') {
        // Retirer la carte des favoris
        if (isFavorite($pdo, $cardId)) {
            removeFavorite($pdo, $cardId);
            echo json_encode(['success' => true]);
        }
    } elseif ($action === 'update' && isset($data['newCardId'])) {
        // Mettre à jour la carte favorite
        $newCardId = $data['newCardId'];
        if (isFavorite($pdo, $cardId)) {
            updateFavorite($pdo, $cardId, $newCardId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Carte non trouvée dans les favoris']);
        }
    } elseif ($action === 'check') {
        // Vérifier si la carte est en favoris
        $isFavorite = isFavorite($pdo, $cardId);
        echo json_encode(['success' => true, 'isFavorite' => $isFavorite]);
    }
}
?>
