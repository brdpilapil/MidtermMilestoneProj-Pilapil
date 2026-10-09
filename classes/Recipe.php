<?php
class Recipe {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function create($userId, $categoryId, $title, $description, $instructions, $prepTime, $difficulty, $ingredients) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO recipes (user_id, category_id, title, description, instructions, prep_time_mins, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $categoryId, $title, $description, $instructions, $prepTime, $difficulty]);
            $recipeId = $this->db->lastInsertId();

            $ingStmt = $this->db->prepare("INSERT INTO ingredients (recipe_id, ingredient_name) VALUES (?, ?)");
            foreach ($ingredients as $ingredient) {
                $cleanIng = trim($ingredient);
                if (!empty($cleanIng)) {
                    $ingStmt->execute([$recipeId, $cleanIng]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function getAll($keyword = '', $categoryId = null) {
        $sql = "SELECT r.*, c.name AS category_name, u.first_name, u.last_name 
                FROM recipes r
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        if (!empty($categoryId)) {
            $sql .= " AND r.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, c.name AS category_name, u.first_name, u.last_name 
            FROM recipes r
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();

        if ($recipe) {
            $ingStmt = $this->db->prepare("SELECT ingredient_name FROM ingredients WHERE recipe_id = ?");
            $ingStmt->execute([$id]);
            $recipe['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $recipe;
    }

    public function update($recipeId, $userId, $categoryId, $title, $description, $instructions, $prepTime, $difficulty, $ingredients) {
        try {
            $this->db->beginTransaction();

            $chk = $this->db->prepare("SELECT user_id FROM recipes WHERE id = ?");
            $chk->execute([$recipeId]);
            $rec = $chk->fetch();

            if (!$rec || $rec['user_id'] != $userId) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE recipes SET category_id=?, title=?, description=?, instructions=?, prep_time_mins=?, difficulty=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->execute([$categoryId, $title, $description, $instructions, $prepTime, $difficulty, $recipeId]);

            $delIng = $this->db->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $delIng->execute([$recipeId]);

            $ingStmt = $this->db->prepare("INSERT INTO ingredients (recipe_id, ingredient_name) VALUES (?, ?)");
            foreach ($ingredients as $ingredient) {
                $cleanIng = trim($ingredient);
                if (!empty($cleanIng)) {
                    $ingStmt->execute([$recipeId, $cleanIng]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function delete($recipeId, $userId) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
            $stmt->execute([$recipeId, $userId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function getCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }
}