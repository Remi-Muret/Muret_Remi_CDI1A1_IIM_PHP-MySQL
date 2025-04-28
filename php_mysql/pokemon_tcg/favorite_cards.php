<?php
session_start(); // Démarrage ou reprise de la session

// Vérification si l'utilisateur est connecté avec la session "user"
if (!isset($_SESSION["user"])) {
    session_unset();
    header("Location: index.php"); // Redirection
    exit;
}

// Vérification si l'utilisateur a cliqué sur le lien de déconnexion avec le paramètre GET
if (isset($_GET['action']) && $_GET['action'] == 'disconnection') {
    session_unset();
    header("Location: index.php"); // Redirection
    exit;
}

require 'vendor/autoload.php'; // Chargement des classes Composer
require 'db.php'; // Connexion à la BDD

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client();
$favorites = []; // Stockage des cartes favorites
$errorMessage = null;

try {
    // Récupérer les ID des cartes favorites
    $stmt = $pdo->query("
        SELECT c.* 
        FROM favorites f
        JOIN cards c ON f.card_id = c.id
        ORDER BY f.id ASC
    ");
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (RequestException $e) {
    $errorMessage = 'Erreur API : ' . $e->getMessage();
} catch (Exception $e) {
    $errorMessage = 'Erreur : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cartes Favorites</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        /* Réinitialisation de base et styles globaux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            object-fit: contain;
            background-color: transparent;
        }
        body {
            background-color: #14A8E9;
            text-align: center;
            overflow-x: hidden;
        }

        /* --- Style du swiper des cartes --- */
        .swiper-3d .swiper-slide-shadow {
            background: rgba(0,0,0,0)
        }
        .swiper-cards .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 420px;
            margin-top: 20px;
        }
        .swiper-cards .swiper-slide img {
            max-width: 270px;
            height: auto;
            border-radius: 9px;
            transition: transform 0.3s ease;
        }
        .swiper-cards .swiper-slide img:hover {
            transform: translateY(-20px);
        }

        /* --- Style du swiper d'infos des cartes --- */
        .swiper-infos {
            width: 80%;
            height: 250px;
            margin-left: 20px;
            margin-top: 10px;
            margin-right: 20px;
            padding-left: 20px;
            padding-right: 20px;
            justify-self: center;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            border-radius: 9px;
        }
        .swiper-infos .swiper-slide {
            background-color: white;
            width: 90%;
            height: 90%;
            border-radius: 9px;
            padding-left: 15px;
            padding-right: 15px;
            overflow-y: auto;
            box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            margin-top: 20px;
        }
        .swiper-slide::-webkit-scrollbar {
            display: none;
        }
        .card-info h2, .card-info h3, .card-info p {
            padding-bottom: 10px;
        }

        /* --- Style du bouton de favori --- */
        .favorite-btn {
            position: absolute;
            top: 24px;
            right: 32px;
            z-index: 1;
            font-size: 28px;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            border-radius: 50%;
            background-color: #fff;
            color: #777;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            transition: background-color 0.3s ease, transform 0.3s ease, color 0.3s ease;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            cursor: pointer;
        }
        .favorite-btn:hover {
            background-color: #E01C2F;
            color: white;
        }
        .favorite-btn.active {
            background-color: #E01C2F;
            color: white;
        }
        .favorite-btn p {
            transition: transform 0.3s ease;
        }
        .favorite-btn:hover p {
            transform: scale(1.2);
        }

        /* --- Style du message d'erreur --- */
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php if ($errorMessage): ?>
        <!-- Affiche un message d'erreur si une exception a été capturée -->
        <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
    <?php elseif (empty($favorites)): ?>
        <p class="error">No favorite card.</p>
    <?php else: ?>

    <?php include 'sidebar.php'; ?><?php endif; ?>

    <span id="favorite-btn" class="favorite-btn">
        <p>&#9825;</p>
    </span>

    <!-- Swiper des cartes du set -->
    <div class="swiper-cards">
        <div class="swiper-wrapper">
            <?php foreach ($favorites as $card): ?>
                <?php
                    $cardData = json_decode($card['json_data'], true);
                    $image = $cardData['images']['large'] ?? 'default_image.png';
                    $name = $cardData['name'] ?? 'Unknown card';
                    $cardId = $card['id'];
                ?>
                <div class="swiper-slide" data-card-id="<?= $cardId ?>" data-card-name="<?= htmlspecialchars($name) ?>">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name) ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Swiper d'infos des cartes -->
    <div class="swiper-infos">
        <div class="swiper-wrapper">
            <?php foreach ($favorites as $card): ?>
                <?php
                    $cardData = json_decode($card['json_data'], true);
                    $name = $cardData['name'] ?? 'Carte sans nom';
                    $hp = $cardData['hp'] ?? null;
                    $types = $cardData['types'] ?? [];
                    $rarity = $cardData['rarity'] ?? null;
                    $abilities = $cardData['abilities'] ?? [];
                    $attacks = $cardData['attacks'] ?? [];
                    $flavorText = $cardData['flavorText'] ?? null;
                ?>
                <div class="swiper-slide">
                    <div class="card-info">
                        <br><br>
                        <h2><?= htmlspecialchars($name) ?></h2>

                        <?php if (!empty($hp)): ?>
                            <p><strong>HP:</strong> <?= htmlspecialchars($hp) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($types)): ?>
                            <p><strong>Type:</strong> <?= htmlspecialchars(implode(', ', $types)) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($rarity)): ?>
                            <p><strong>Rarity:</strong> <?= htmlspecialchars($rarity) ?></p>
                        <?php endif; ?>
                        <br><br>

                        <?php foreach ($abilities as $ability): ?>
                            <h3><strong>Pokémon Power: <?= htmlspecialchars($ability['name']) ?></strong></h3>
                            <p><?= htmlspecialchars($ability['text']) ?></p>
                            <br><br>
                        <?php endforeach; ?>

                        <?php foreach ($attacks as $attack): ?>
                            <h3><strong><?= htmlspecialchars($attack['name']) ?></strong></h3>
                            <?php if (!empty($attack['text'])): ?>
                                <p><?= htmlspecialchars($attack['text']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($attack['cost'])): ?>
                                <p><strong>Cost: </strong><?= htmlspecialchars(implode(', ', $attack['cost'])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($attack['damage'])): ?>
                                <p><strong>Damage: </strong><?= htmlspecialchars($attack['damage']) ?></p>
                            <?php endif; ?>
                            <br><br>
                        <?php endforeach; ?>

                        <?php if (isset($flavorText)): ?>
                            <p><strong><?= nl2br(htmlspecialchars($flavorText)) ?></strong></p>
                            <br><br>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Initialisation du swiper des cartes du set
        var swiperCards = new Swiper(".swiper-cards", {
            effect: "cards",
            grabCursor: true,
            loop: false,
        });

        // Initialisation du swiper d'infos des cartes
        var swiperInfos = new Swiper(".swiper-infos", {
            slidesPerView: 1,
            spaceBetween: 500,
            allowTouchMove: false,
            loop: false,
        });

        // Synchronisation des 2 swipers
        swiperCards.on('slideChangeTransitionStart', function () {
            swiperInfos.slideToLoop(swiperCards.realIndex);
            updateFavoriteButton(swiperCards.realIndex);
        });

        // Mise à jour du bouton favori au chargement de la page
        document.addEventListener("DOMContentLoaded", function() {
            updateFavoriteButton(0);
        });

        // Fonction pour vérifier si une carte est en favori
        function isFavorite(cardId) {
            return fetch('favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'check', cardId: cardId }),
            })
            .then(response => response.json())
            .then(data => data.isFavorite);
        }

        // Fonction pour mettre à jour l'état du bouton favori en fonction de la carte visible
        function updateFavoriteButton(index) {
            const cardElement = swiperCards.slides[index];
            const cardId = cardElement.getAttribute('data-card-id');
    
            isFavorite(cardId).then(isFav => {
                const favoriteBtn = document.getElementById('favorite-btn');
                if (isFav) {
                    favoriteBtn.classList.add('active');
                } else {
                    favoriteBtn.classList.remove('active');
                }
            });
        }

        // Ajout d'une carte en favori ou suppression selon l'état du bouton
        document.getElementById('favorite-btn').addEventListener('click', function() {
            const activeCard = swiperCards.slides[swiperCards.realIndex];
            const cardId = activeCard.getAttribute('data-card-id');
            const favoriteBtn = this;

            if (cardId) {
                const isActive = favoriteBtn.classList.contains('active');
        
                fetch('favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cardId: cardId,
                        action: isActive ? 'remove' : 'add'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        favoriteBtn.classList.toggle('active');
                    } else {
                        console.error('Error');
                    }
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                });
            }
        });
    </script>
</body>
</html>
