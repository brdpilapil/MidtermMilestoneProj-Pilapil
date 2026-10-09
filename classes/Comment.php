<?php
class Comment {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getByRecipe($recipeId) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name, u.last_name 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.recipe_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function create($recipeId, $userId, $content) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$recipeId, $userId, $content]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function update($commentId, $userId, $content) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE comments SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
            $stmt->execute([$content, $commentId, $userId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function delete($commentId, $userId) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
            $stmt->execute([$commentId, $userId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }
}