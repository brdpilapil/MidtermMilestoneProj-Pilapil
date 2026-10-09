<?php
require_once 'config/Database.php';
require_once 'classes/Recipe.php';
require_once 'auth.php';
require_auth();

$pdo = Database::getInstance();
$recipeObj = new Recipe($pdo);
$recipeId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$recipe = $recipeObj->getById($recipeId);

if (!$recipe || $recipe['user_id'] !=$_SESSION['user_id']) {
    header("Location: index.php");
    exit;
}
$categories =$recipeObj->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🍲 Lutong Barangay</h1>
        <nav><a href="recipe_details.php?id=<?= $recipe['id'] ?>">Cancel</a></nav>
    </header>

    <div class="container" style="max-width: 700px;">
        <div class="card">
            <h2>Edit Recipe</h2>
            <form method="POST" action="recipe_actions.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">

                <label>Recipe Title</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($recipe['title']) ?>">

                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach ($categories as$cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $recipe['category_id'] ==$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div style="display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Prep/Cooking Time (Mins)</label>
                        <input type="number" name="prep_time_mins" min="1" required value="<?= $recipe['prep_time_mins'] ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Difficulty Level</label>
                        <select name="difficulty" required>
                            <option value="Easy" <?= $recipe['difficulty'] == 'Easy' ? 'selected' : '' ?>>Easy</option>
                            <option value="Medium" <?= $recipe['difficulty'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="Hard" <?= $recipe['difficulty'] == 'Hard' ? 'selected' : '' ?>>Hard</option>
                        </select>
                    </div>
                </div>

                <label>Short Description</label>
                <textarea name="description" rows="2" required><?= htmlspecialchars($recipe['description']) ?></textarea>

                <label>Ingredients</label>
                <div id="ingredients-wrapper">
                    <?php foreach ($recipe['ingredients'] as$ing): ?>
                        <div class="ingredient-input">
                            <input type="text" name="ingredients[]" required value="<?= htmlspecialchars($ing) ?>">
                            <button type="button" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addIngredientField()" style="margin-top:8px;">+ Add Ingredient</button>

                <label>Cooking Steps / Instructions</label>
                <textarea name="instructions" rows="5" required><?= htmlspecialchars($recipe['instructions']) ?></textarea>

                <br><br>
                <button type="submit" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
    function addIngredientField() {
        const wrapper = document.getElementById('ingredients-wrapper');
        const div = document.createElement('div');
        div.className = 'ingredient-input';
        div.innerHTML = `<input type="text" name="ingredients[]" required><button type="button" onclick="this.parentElement.remove()">X</button>`;
        wrapper.appendChild(div);
    }
    </script>
</body>
</html>