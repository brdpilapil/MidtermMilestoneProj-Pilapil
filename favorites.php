<?php
require_once 'config/Database.php';
require_once 'classes/Favorite.php';
require_once 'auth.php';
require_auth();

$favObj = new Favorite(Database::getInstance());
$favorites = $favObj->getUserFavorites($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Favorites - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🍲 Lutong Barangay</h1>
        <button class="menu-toggle" id="menuToggle">☰</button>
        <nav id="navMenu">
            <a href="index.php">Feed</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Your Saved Favorites</h2><br>

        <?php if (empty($favorites)): ?>
            <p>You haven't saved any recipes yet.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($favorites as $r): ?>
                    <div class="card">
                        <span class="badge"><?= htmlspecialchars($r['category_name']) ?></span>
                        <h3><?= htmlspecialchars($r['title']) ?></h3>
                        <p style="font-size:0.85em; color:#666;">By <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></p>
                        <p style="margin: 10px 0;"><?= htmlspecialchars(substr($r['description'], 0, 100)) ?>...</p>
                        <a href="recipe_details.php?id=<?= $r['id'] ?>" class="btn">View Recipe</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
    </script>
</body>
</html>