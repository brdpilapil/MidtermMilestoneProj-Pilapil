<?php
require_once 'config/Database.php';
require_once 'classes/Favorite.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT);

if ($recipeId) {
    $favObj = new Favorite(Database::getInstance());
    $isFav = $favObj->toggle($_SESSION['user_id'], $recipeId);

    if ($isFav !== null) {
        echo json_encode(['success' => true, 'is_favorite' => $isFav]);
        exit;
    }
}

echo json_encode(['success' => false]);