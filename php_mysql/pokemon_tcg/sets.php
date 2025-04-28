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

require 'vendor/autoload.php'; // Charge automatique des classes Composer
require 'db.php'; // Connexion à la BDD

// Importation des classes nécessaires depuis la bibliothèque Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client(); // Création d'un client HTTP Guzzle
$sets = ['base1', 'base2', 'base3']; // Identifiants des sets à afficher
$setsData = []; // Tableau pour stocker les données complètes

// Fonctions pour parser depuis la BDD
function parseSetRow($row) {
    $json = json_decode($row['json_data'], true);
    if (!$json) return null;

    return [
        'id' => $row['id'],
        'logo' => $json['images']['logo'] ?? '',
    ];
}

function parseCardRow($row) {
    $json = json_decode($row['json_data'], true);
    if (!$json) return null;

    return [
        'id' => $row['id'],
        'name' => $json['name'] ?? '',
        'image_small' => $json['images']['small'] ?? '',
        'image_large' => $json['images']['large'] ?? '',
        'set_id' => $json['set']['id'] ?? '',
    ];
}

// Fonctions pour charger depuis la BDD
function loadSetFromDb($pdo, $setId) {
    $stmt = $pdo->prepare("SELECT id, json_data FROM sets WHERE id = :id");
    $stmt->execute(['id' => $setId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function loadCardsFromDb($pdo, $setId) {
    $stmt = $pdo->prepare("SELECT id, json_data FROM cards WHERE JSON_EXTRACT(json_data, '$.set.id') = :set_id");
    $stmt->execute(['set_id' => $setId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonctions pour charger depuis l'API
function fetchSetFromApi($client, $setId) {
    $response = $client->request('GET', "https://api.pokemontcg.io/v2/sets/$setId", [
        'verify' => 'C:\wamp64\bin\php\php8.3.14\cacert.pem'
    ]);
    $data = json_decode($response->getBody(), true);
    return $data['data'] ?? null;
}

function fetchCardsFromApi($client, $setId) {
    $response = $client->request('GET', "https://api.pokemontcg.io/v2/cards?q=set.id:$setId", [
        'verify' => 'C:\wamp64\bin\php\php8.3.14\cacert.pem'
    ]);
    $data = json_decode($response->getBody(), true);
    return $data['data'] ?? [];
}

// Fonctions pour insérer dans la BDD
function insertSetIntoDb($pdo, $setId, $setData) {
    // Vérification si le set existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sets WHERE json_data = :json_data");
    $stmt->execute(['json_data' => json_encode($setData)]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insertion seulement si non existant
        $stmt = $pdo->prepare("INSERT INTO sets (json_data) VALUES (:json_data)");
        $stmt->execute([
            'json_data' => json_encode($setData)
        ]);
    }
}

function insertCardIntoDb($pdo, $card) {
    // Vérification si la carte existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cards WHERE id = :id");
    $stmt->execute(['id' => $card['id']]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insertion seulement si non existante
        $stmt = $pdo->prepare("INSERT INTO cards (id, json_data) VALUES (:id, :json_data)");
        $stmt->execute([
            'id' => $card['id'],
            'json_data' => json_encode($card)
        ]);
    }
}

try {
    foreach ($sets as $setId) {
        // Chargement du set depuis la BDD
        $setRow = loadSetFromDb($pdo, $setId);
        $parsedSet = null;

        if ($setRow) {
            // Si set trouvé en BDD, parsage du set
            $parsedSet = parseSetRow($setRow);
        } else {
            // Sinon, récupération depuis l'API
            $setData = fetchSetFromApi($client, $setId);
            // Si trouvé via l'API, insérer en BDD et parser
            if ($setData) {
                insertSetIntoDb($pdo, $setId, $setData); // insertion uniquement si le set n'existe pas
                $parsedSet = [
                    'id' => $setId,
                    'logo' => $setData['images']['logo'] ?? ''
                ];
            } else {
                // Sinon, création d'un set vide
                $parsedSet = [
                    'id' => $setId,
                    'logo' => ''
                ];
            }
        }

        // Chargement des cartes du set depuis la BDD
        $cardRows = loadCardsFromDb($pdo, $setId);
        $cardsData = [];

        if ($cardRows) {
            // Si cartes trouvées en BDD, parsage de chacune
            foreach ($cardRows as $row) {
                $parsedCard = parseCardRow($row);
                if ($parsedCard) {
                    $cardsData[] = $parsedCard;
                }
            }
        } else {
            // Sinon, récupération depuis l'API
            $cards = fetchCardsFromApi($client, $setId);
            if ($cards) {
                foreach ($cards as $card) {
                    // Si trouvé via l'API contient toutes les données nécessaires, insérer en BDD et parser
                    if (isset($card['id'], $card['images']['small'], $card['images']['large'], $card['name'], $card['set']['id'])) {
                        insertCardIntoDb($pdo, $card);
                        $parsedCard = [
                            'id' => $card['id'],
                            'name' => $card['name'],
                            'image_small' => $card['images']['small'],
                            'image_large' => $card['images']['large'],
                            'set_id' => $card['set']['id']
                        ];
                        $cardsData[] = $parsedCard;
                    }
                }
            }
        }

        // Stockage des infos dans un tableau
        $setsData[] = [
            'id' => $parsedSet['id'],
            'logo' => $parsedSet['logo'],
            'cards' => $cardsData
        ];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon TCG Base Jungle Fossil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        /* Réinitialisation de base et styles globaux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            object-fit: contain;
            background-color: transparent;
        }
        html, body {
            height: auto;
        }
        body {
            background: linear-gradient(#14A8E9 1000px, #EED149);
            text-align: center;
        }

        /* --- Style du swiper des logos --- */
        .logos-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        .logos-container .swiper-slide {
            margin-top: 15px;
        }
        .set-logo {
            height: 150px;
            object-fit: contain;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .set-logo:hover {
            transform: translateY(-10px);
        }

        /* --- Style des boutons de avigation et de la pagination --- */
        .swiper-button-prev, .swiper-button-next {
            position: absolute;
            top: 50%;
            z-index: 1;
            color: #FBC32C;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }
        .swiper-button-next:hover, .swiper-button-prev:hover {
            color: #E01C2F;
            transition: 0.3s;
        }
        .swiper-pagination {
            position: relative;
            margin-top: 15px;
        }
        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background-color: #FBC32C;
        }

        /* --- Style du swiper des cartes --- */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 50px;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .cards-container img {
            max-width: 180px;
            height: auto;
            box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            border-radius: 6px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .cards-container img:hover {
            transform: translateY(-20px);
        }

        /* --- Style de la modale --- */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        .modal img {
            max-width: 270px;
            height: auto;
            border-radius: 9px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .modal img:hover {
            transform: translateY(-20px);
        }

        /* --- Style du bouton de favori --- */
        .favorite-btn {
            position: absolute;
            top: 24px;
            right: 96px;
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

        /* --- Style du bouton croix --- */
        .close-btn {
            position: absolute;
            top: 24px;
            right: 32px;
            font-size: 28px;
            width: 32px;
            height: 32px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background-color: #fff;
            color: #E01C2F;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
            transition: background-color 0.3s ease, transform 0.3s ease, color 0.3s ease;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            cursor: pointer;
        }
        .close-btn:hover {
            background-color: #E01C2F;
            color: #fff;
            transform: rotate(90deg);
        }

        /* --- Style du message d'erreur --- */
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php if (isset($errorMessage)): ?>
        <!-- Affiche un message d'erreur si une exception a été capturée -->
        <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
    <?php else: ?>

    <?php include 'sidebar.php'; ?><?php endif; ?>

    <!-- Swiper des logos des sets -->
    <div class="logos-container">
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php foreach ($setsData as $set): ?>
                    <div class="swiper-slide">
                        <a href="set_cards.php?set=<?= htmlspecialchars($set['id']) ?>">
                            <img class="set-logo" src="<?= htmlspecialchars($set['logo']) ?>" alt="Set Logo">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Flèches de navigation et pagination -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    </div>

    <!-- Swiper des cartes de chaque set -->
    <div class="swiper">
        <div class="swiper-wrapper">
            <?php foreach ($setsData as $set): ?>
                <div class="swiper-slide">
                    <div class="cards-container">
                        <?php foreach ($set['cards'] as $card): ?>
                            <img src="<?= htmlspecialchars($card['image_small']) ?>"
                                    alt="<?= htmlspecialchars($card['name']) ?>" 
                                    class="card-img" 
                                    data-id="<?= htmlspecialchars($card['id']) ?>"
                                    data-set="<?= htmlspecialchars($set['id']) ?>" />
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modale pour afficher une carte agrandie -->
    <div class="modal" id="modal">
        <span class="close-btn" id="close-btn">&times;</span>
        <span id="favorite-btn" class="favorite-btn">
            <p>&#9825;</p>
        </span>
        <img id="modal-image" src="" alt="Card Image" data-id="" data-set="">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Initialisation de Swiper
        var swiper = new Swiper(".swiper", {
            slidesPerView: 1,
            spaceBetween: 500,
            loop: true,
            autoHeight: true,
            allowTouchMove: false,
            pagination: {
                el: ".swiper-pagination",
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        // Gestion du clic sur une carte pour ouvrir la modale
        document.querySelectorAll('.card-img').forEach(card => {
            card.addEventListener('click', function() {
                const cardId = this.getAttribute('data-id');
                const setId = this.getAttribute('data-set');
                const imageSrc = this.getAttribute('src');

                const modal = document.getElementById('modal');
                const modalImage = document.getElementById('modal-image');
                modalImage.src = imageSrc;
                modalImage.setAttribute('data-id', cardId);
                modalImage.setAttribute('data-set', setId);

                fetch('favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cardId: cardId,
                        action: 'check'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const favoriteBtn = document.getElementById('favorite-btn');
                    if (data.isFavorite) {
                        favoriteBtn.classList.add('active');
                    } else {
                        favoriteBtn.classList.remove('active');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

                modal.style.display = 'flex';
            });
        });

        // Ajout d'une carte en favori
        document.getElementById('favorite-btn').addEventListener('click', function() {
            const modalImage = document.getElementById('modal-image');
            const cardId = modalImage.getAttribute('data-id');
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

        // Redirection vers la page d'une carte depuis la modale
        document.getElementById('modal-image').addEventListener('click', function () {
            const cardId = this.getAttribute('data-id');
            const setId = this.getAttribute('data-set');
            if (cardId && setId) {
                window.location.href = `set_cards.php?id=${cardId}&set=${setId}`;
            }
        });

        // Fermeture de la modale
        document.getElementById('close-btn').addEventListener('click', function() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('favorite-btn').classList.remove('active');
        });
    </script>
</body>
</html>
