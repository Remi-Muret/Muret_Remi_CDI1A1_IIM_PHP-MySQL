<?php
require 'vendor/autoload.php'; // Charge automatique des classes Composer
require 'db.php'; // Connexion à la BDD

// Importation des classes nécessaires depuis la bibliothèque Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client(); // Création d'un client HTTP Guzzle
$cardId = $_GET['id'] ?? ''; // ID de la carte sélectionnée
$setId = $_GET['set'] ?? ''; // ID du set de cartes
$cards = []; // Tableau pour stocker les données complètes

try {
    // Vérification si des cartes existent déjà pour ce set
    $stmt = $pdo->prepare("SELECT * FROM cards WHERE JSON_EXTRACT(json_data, '$.set.id') = :set_id");
    $stmt->execute(['set_id' => $setId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tri des cartes par numéro
    usort($cards, function($a, $b) {
        $aData = json_decode($a['json_data'], true);
        $bData = json_decode($b['json_data'], true);
    
        $aNumber = isset($aData['number']) ? intval($aData['number']) : 0;
        $bNumber = isset($bData['number']) ? intval($bData['number']) : 0;
    
        return $aNumber <=> $bNumber;
    });

    // Recherche de la position de la carte sélectionnée
    $currentIndex = 0;
    if (!empty($cardId)) {
        $currentIndex = array_search($cardId, array_column($cards, 'id'));
    }
} catch (RequestException $e) {
    $errorMessage = 'Error API: ' . $e->getMessage(); // Gestion des erreurs d’appel API
} catch (Exception $e) {
    $errorMessage = 'Error: ' . $e->getMessage(); // Gestion des erreurs
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Set Cards</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        /* Réinitialisation de base et styles globaux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            object-fit: contain;
        }
        body {
            background-color: #14A8E9;
            text-align: center;
            overflow-x: hidden;
        }
        .main-content {
            padding: 20px;
            padding-left: 160px;
        }

        /* --- Style du swiper des cartes --- */
        .swiper-3d .swiper-slide-shadow {
            background: rgba(0,0,0,0)
        }
        .swiper-cards .swiper-wrapper {
            margin-top: 20px;
        }
        .swiper-cards .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 420px;
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
            height: 220px;
            margin: 20px;
            overflow: hidden;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }
        .swiper-infos .swiper-slide {
            justify-content: center;
            background-color: white;
            border-radius: 9px;
            padding: 15px;
            overflow-y: auto;
        }
        .card-info h2, .card-info h3, .card-info p {
            padding-bottom: 10px;
        }

        /* --- Style du message d'erreur --- */
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <section class="main-content">
        <?php if (isset($errorMessage)): ?>
            <!-- Affiche un message d'erreur si une exception a été capturée -->
            <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
        <?php else: ?>
            <!-- Swiper des cartes du set -->
            <div class="swiper-cards">
                <div class="swiper-wrapper">
                    <?php foreach ($cards as $card): ?>
                        <?php
                            $cardData = json_decode($card['json_data'], true);
                            $image = $cardData['images']['large'] ?? 'default_image.png';
                            $name = $cardData['name'] ?? 'No card';
                        ?>
                        <div class="swiper-slide">
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Swiper d'infos des cartes -->
            <div class="swiper-infos">
                <div class="swiper-wrapper">
                    <?php foreach ($cards as $card): ?>
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
        <?php endif; ?>

        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
            // Initialisation du swiper des cartes du set
            var swiperCards = new Swiper(".swiper-cards", {
                effect: "cards",
                grabCursor: true,
                loop: false,
                initialSlide: <?= $currentIndex ?>,
            });

            // Initialisation du swiper d'infos des cartes
            var swiperInfos = new Swiper(".swiper-infos", {
                slidesPerView: 1,
                spaceBetween: 500,
                allowTouchMove: false,
                loop: false,
                initialSlide: <?= $currentIndex ?>,
            });

            // Synchronisation des 2 swipers
            swiperCards.on('slideChangeTransitionStart', function () {
                swiperInfos.slideToLoop(swiperCards.realIndex);
            });
        </script>
    </section>
</body>
</html>
