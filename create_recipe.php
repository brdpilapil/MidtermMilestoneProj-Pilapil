<?php
require_once 'config/Database.php';
require_once 'classes/Recipe.php';
require_once 'auth.php';
require_auth();

$recipeObj = new Recipe(Database::getInstance());
$categories =$recipeObj->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Recipe - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🍲 Lutong Barangay</h1>
        <button class="menu-toggle" id="menuToggle">☰</button>
        <nav id="navMenu"><a href="index.php">Back to Feed</a></nav>
    </header>

    <div class="container" style="max-width: 700px;">
        <div class="card">
            <h2>Share a New Recipe</h2>
            <form method="POST" action="recipe_actions.php">
                <input type="hidden" name="action" value="create">
                
                <label>Recipe Title</label>
                <input type="text" name="title" required placeholder="e.g. Special Pork Adobo">

                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as$cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <div style="display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Prep/Cooking Time (Mins)</label>
                        <input type="number" name="prep_time_mins" value="30" min="1" required>
                    </div>
                    <div style="flex:1;">
                        <label>Difficulty Level</label>
                        <select name="difficulty" required>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                </div>

                <label>Short Description</label>
                <textarea name="description" rows="2" required placeholder="Briefly describe your dish..."></textarea>

                <label>Ingredients</label>
                <div id="ingredients-wrapper">
                    <div class="ingredient-input">
                        <input type="text" name="ingredients[]" required placeholder="Ingredient 1 (e.g., 1kg Pork Belly)">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addIngredientField()" style="margin-top:8px;">+ Add Another Ingredient</button>

                <label>Cooking Steps / Instructions</label>
                <textarea name="instructions" rows="5" required placeholder="Step-by-step instructions..."></textarea>

                <br><br>
                <button type="submit" style="width: 100%;">Publish Recipe</button>
            </form>
        </div>
    </div>

    <!-- Dynamic Ingredients DOM Script -->
    <script>
    function addIngredientField() {
        const wrapper = document.getElementById('ingredients-wrapper');
        const div = document.createElement('div');
        div.className = 'ingredient-input';
        div.innerHTML = `
            <input type="text" name="ingredients[]" required placeholder="Next Ingredient">
            <button type="button" onclick="this.parentElement.remove()">X</button>
        `;
        wrapper.appendChild(div);
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