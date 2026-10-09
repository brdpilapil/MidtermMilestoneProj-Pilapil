<?php
require_once 'config/Database.php';
require_once 'classes/Recipe.php';
require_once 'classes/Favorite.php';
require_once 'auth.php';
require_auth();

$pdo = Database::getInstance();
$recipeObj = new Recipe($pdo);
$favObj = new Favorite($pdo);

$keyword = trim($_GET['keyword'] ?? '');
$categoryFilter = $_GET['category'] ?? '';

$recipes = $recipeObj->getAll($keyword, $categoryFilter);
$categories = $recipeObj->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lutong Barangay - Feed</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🍲 Lutong Barangay</h1>
            <button class="menu-toggle" id="menuToggle">☰</button>
        <nav id="navMenu">
            <a href="index.php">Feed</a>
            <a href="create_recipe.php">+ Share Recipe</a>
            <a href="favorites.php">Saved Favorites</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
        </nav>
    </header>

    <div class="container">
        <!-- Search & Filter Controls -->
        <div class="card">
            <form method="GET" action="index.php" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="keyword" placeholder="Search recipe or keyword..." value="<?= htmlspecialchars($keyword) ?>" style="flex: 2; min-width: 200px;">
                <select name="category" style="flex: 1; min-width: 150px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filter</button>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <h2>Latest Recipes</h2><br>

        <?php if (empty($recipes)): ?>
            <p>No recipes found matching your criteria.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($recipes as $r): 
                    $isFav = $favObj->isFavorite($_SESSION['user_id'], $r['id']);
                ?>
                    <div class="card">
                        <span class="badge"><?= htmlspecialchars($r['category_name']) ?></span>
                        <span class="badge badge-diff"><?= htmlspecialchars($r['difficulty']) ?></span>
                        <h3 style="margin-top:10px;"><?= htmlspecialchars($r['title']) ?></h3>
                        <p style="font-size:0.85em; color:#666;">By <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?> • ⏱️ <?= $r['prep_time_mins'] ?> mins</p>
                        <p style="margin: 10px 0;"><?= htmlspecialchars(substr($r['description'], 0, 100)) ?>...</p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <a href="recipe_details.php?id=<?= $r['id'] ?>" class="btn">View Recipe</a>
                            <button class="fav-btn <?= $isFav ? 'active' : '' ?>" onclick="toggleFavorite(<?= $r['id'] ?>, this)">
                                <?= $isFav ? '❤️ Saved' : '🤍 Save' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Favorites script -->
    <script>
    function toggleFavorite(recipeId, btn) {
        fetch('favorite_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'recipe_id=' + recipeId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_favorite) {
                    btn.classList.add('active');
                    btn.innerHTML = '❤️ Saved';
                } else {
                    btn.classList.remove('active');
                    btn.innerHTML = '🤍 Save';
                }
            }
        });
    }

   
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