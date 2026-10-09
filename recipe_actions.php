<?php
require_once 'config/Database.php';
require_once 'classes/Recipe.php';
require_once 'auth.php';
require_auth();

$action = $_POST['action'] ?? '';
$recipeObj = new Recipe(Database::getInstance());

if ($action === 'create') {
    $categoryId   = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $prepTime     = filter_var($_POST['prep_time_mins'] ?? 15, FILTER_VALIDATE_INT);
    $difficulty   = $_POST['difficulty'] ?? 'Easy';
    $ingredients  = $_POST['ingredients'] ?? [];

    if ($categoryId && !empty($title) && !empty($description) && !empty($instructions) && !empty($ingredients)) {
        $recipeObj->create($_SESSION['user_id'], $categoryId, $title, $description, $instructions, $prepTime, $difficulty, $ingredients);
    }
} 
elseif ($action === 'edit') {
    $recipeId     = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT);
    $categoryId   = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $prepTime     = filter_var($_POST['prep_time_mins'] ?? 15, FILTER_VALIDATE_INT);
    $difficulty   = $_POST['difficulty'] ?? 'Easy';
    $ingredients  = $_POST['ingredients'] ?? [];

    if ($recipeId && $categoryId && !empty($title) && !empty($description) && !empty($instructions)) {
        $recipeObj->update($recipeId, $_SESSION['user_id'], $categoryId, $title, $description, $instructions, $prepTime, $difficulty, $ingredients);
        header("Location: recipe_details.php?id=$recipeId");
        exit;
    }
} 
elseif ($action === 'delete') {
    $recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT);
    if ($recipeId) {
        $recipeObj->delete($recipeId, $_SESSION['user_id']);
    }
}

header("Location: index.php");
exit;