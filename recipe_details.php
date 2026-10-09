<?php
require_once 'config/Database.php';
require_once 'classes/Recipe.php';
require_once 'classes/Comment.php';
require_once 'classes/Favorite.php';
require_once 'auth.php';
require_auth();

$pdo = Database::getInstance();
$recipeObj = new Recipe($pdo);
$commentObj = new Comment($pdo);
$favObj = new Favorite($pdo);

$recipeId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$recipe = $recipeObj->getById($recipeId);

if (!$recipe) {
    header("Location: index.php");
    exit;
}

$comments = $commentObj->getByRecipe($recipeId);
$isFav = $favObj->isFavorite($_SESSION['user_id'], $recipeId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🍲 Lutong Barangay</h1>
        <nav><a href="index.php">Back to Feed</a></nav>
    </header>

    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
                <div>
                    <h2><?= htmlspecialchars($recipe['title']) ?></h2>
                    <p style="color:#666;">
                        Posted by <strong><?= htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']) ?></strong> 
                        on <?= $recipe['created_at'] ?>
                        <?php if ($recipe['updated_at']): ?><span class="edited-tag">(Edited)</span><?php endif; ?>
                    </p>
                </div>
                <button class="fav-btn <?= $isFav ? 'active' : '' ?>" onclick="toggleFavorite(<?= $recipe['id'] ?>, this)">
                    <?= $isFav ? '❤️ Saved' : '🤍 Save Favorite' ?>
                </button>
            </div>

            <br>
            <span class="badge"><?= htmlspecialchars($recipe['category_name']) ?></span>
            <span class="badge badge-diff"><?= htmlspecialchars($recipe['difficulty']) ?></span>
            <span class="badge" style="background:#2980b9;">⏱️ <?= $recipe['prep_time_mins'] ?> mins</span>

            <p style="margin: 15px 0; font-size:1.1em;"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>

            <!-- Edit/Delete controls for author -->
            <?php if ($recipe['user_id'] == $_SESSION['user_id']): ?>
                <a href="edit_recipe.php?id=<?= $recipe['id'] ?>" class="btn btn-secondary">Edit Recipe</a>
                <form method="POST" action="recipe_actions.php" style="display:inline;" onsubmit="return confirm('Delete this recipe permanently?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete Recipe</button>
                </form>
            <?php endif; ?>

            <hr style="margin:20px 0;">

            <h3>Ingredients</h3>
            <ul style="margin: 10px 0 20px 20px;">
                <?php foreach ($recipe['ingredients'] as $ing): ?>
                    <li><?= htmlspecialchars($ing) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>Cooking Instructions</h3>
            <p style="white-space: pre-line; margin-top:10px;"><?= htmlspecialchars($recipe['instructions']) ?></p>
        </div>

        <!-- Comments Section -->
        <div class="card">
            <h3>Community Feedback & Comments</h3><br>

            <form method="POST" action="comment_actions.php">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                <textarea name="content" rows="2" placeholder="Write feedback..." required></textarea>
                <button type="submit" style="margin-top: 5px;">Submit Feedback</button>
            </form>

            <hr style="margin:20px 0;">

            <?php foreach ($comments as $c): ?>
                <div style="background:#f9f9f9; padding:10px; border-left:3px solid var(--primary-color); margin-bottom:10px; border-radius:3px;">
                    <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                    <span style="font-size:0.8em; color:#777;">• <?= $c['created_at'] ?></span>
                    <?php if ($c['updated_at']): ?><span class="edited-tag">(Edited)</span><?php endif; ?>
                    
                    <p style="margin-top:5px;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>

                    <?php if ($c['user_id'] == $_SESSION['user_id']): ?>
                        <div style="margin-top:5px;">
                            <button onclick="toggleElement('edit-comment-<?= $c['id'] ?>')" class="btn-secondary" style="padding:2px 6px; font-size:0.75em;">Edit</button>
                            <form method="POST" action="comment_actions.php" style="display:inline;" onsubmit="return confirm('Delete comment?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                <button type="submit" class="btn-danger" style="padding:2px 6px; font-size:0.75em;">Delete</button>
                            </form>
                        </div>

                        <div id="edit-comment-<?= $c['id'] ?>" style="display:none; margin-top:8px;">
                            <form method="POST" action="comment_actions.php">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                <textarea name="content" rows="2" required><?= htmlspecialchars($c['content']) ?></textarea>
                                <button type="submit" style="padding:4px 8px; font-size:0.8em; margin-top:3px;">Update</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

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
                btn.classList.toggle('active');
                btn.innerHTML = data.is_favorite ? '❤️ Saved' : '🤍 Save Favorite';
            }
        });
    }

    function toggleElement(id) {
        const el = document.getElementById(id);
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
    </script>
</body>
</html>