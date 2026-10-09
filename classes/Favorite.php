<?php
class Favorite {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function toggle($userId, $recipeId) {
        try {
            $this->db->beginTransaction();

            $check = $this->db->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $check->execute([$userId, $recipeId]);

            if ($check->fetch()) {
                $del = $this->db->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
                $del->execute([$userId, $recipeId]);
                $isFav = false;
            } else {
                $add = $this->db->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
                $add->execute([$userId, $recipeId]);
                $isFav = true;
            }

            $this->db->commit();
            return $isFav;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return null;
        }
    }

    public function isFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        return (bool)$stmt->fetch();
    }

    public function getUserFavorites($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, c.name AS category_name, u.first_name, u.last_name 
            FROM favorites f
            JOIN recipes r ON f.recipe_id = r.id
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}