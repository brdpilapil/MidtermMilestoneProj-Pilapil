<?php
require_once 'config/Database.php';
require_once 'classes/Comment.php';
require_once 'auth.php';
require_auth();

$action = $_POST['action'] ?? '';
$commentObj = new Comment(Database::getInstance());
$recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT);

if ($action === 'create' && $recipeId) {
    $content = trim($_POST['content'] ?? '');
    if (!empty($content)) {
        $commentObj->create($recipeId, $_SESSION['user_id'], $content);
    }
} 
elseif ($action === 'edit' && $recipeId) {
    $commentId = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');
    if ($commentId && !empty($content)) {
        $commentObj->update($commentId, $_SESSION['user_id'], $content);
    }
} 
elseif ($action === 'delete' && $recipeId) {
    $commentId = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);
    if ($commentId) {
        $commentObj->delete($commentId, $_SESSION['user_id']);
    }
}

header("Location: recipe_details.php?id=$recipeId");
exit;