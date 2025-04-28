<style>
    /* --- Style du bouton hamburger --- */
    .hamburger-btn {
        position: absolute;
        top: 24px;
        left: 32px;
        z-index: 20;
        font-size: 20px;
        width: 32px;
        height: 32px;
        line-height: 31px;
        text-align: center;
        border-radius: 50%;
        background-color: #fff;
        color: #777;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        transition: background-color 0.3s ease, transform 0.3s ease, color 0.3s ease;
        font-family: 'Arial', sans-serif;
        font-weight: bold;
        cursor: pointer;
    }
    .hamburger-btn.active {
        transform: translateX(160px);
    }
    .hamburger-btn p {
        transition: transform 0.3s ease;
    }
    .hamburger-btn:hover p {
        transform: rotate(90deg);
    }
    .hamburger-btn.active p {
        transform: rotate(90deg);
    }

    /* --- Style de la sidebar --- */
    .sidebar {
        position: fixed;
        width: 150px;
        height: 100vh;
        background-color: #3A519B;
        padding: 20px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.5);
        z-index: 10;
        left: -160px;
        transition: left 0.3s ease;
        font-family: 'Arial', sans-serif;
        font-weight: bold;
    }
    .sidebar.active {
        left: 0;
    }
    .sidebar ul {
        list-style: none;
        padding: 0;
        width: 100%;
    }
    .sidebar ul li {
        margin-top: 40px;
    }

    .btn a {
        display: block;
        text-align: center;
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 12px;
        transition: background-color 0.3s, color 0.3s;
        font-weight: bold;
        color: #3A519B;
    }
    .btn a:hover {
        background-color: #E01C2F;
        color: white;
    }
    .set-btn {
        background-color: #FBC32C;
    }
    .favorites-btn {
        background-color: #33D140;
    }
    .log-out-btn {
        background-color: #E95233;
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

<!-- Bouton de déploiement de la sidebar -->
<span id="hamburger-btn" class="hamburger-btn">
    <p>&#9776;</p>
</span>

<!-- Sidebar -->
<aside class="sidebar">
    <a href="sets.php">
        <img src="https://images.pokemontcg.io/card-back.png" alt="Back of Pokémon Card">
    </a>

    <ul>
        <li class="btn"><a href="set_cards.php?set=base1" class="set-btn">Base set</a></li>
        <li class="btn"><a href="set_cards.php?set=base2" class="set-btn">Jungle set</a></li>
        <li class="btn"><a href="set_cards.php?set=base3" class="set-btn">Fossil set</a></li>
        <li class="btn"><a href="favorite_cards.php" class="favorites-btn">Favorites</a></li>
        <li class="btn"><a href="?action=disconnection" class="log-out-btn">Log out</a></li>
    </ul>
</aside>

<script>
    // Ouverture et fermeture de la sidebar
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sidebar = document.querySelector('.sidebar');

    hamburgerBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        hamburgerBtn.classList.toggle('active');
    });
</script>