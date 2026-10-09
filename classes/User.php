<?php
class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function register($firstName, $lastName, $email, $password) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return "Email is already registered.";
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return "Registration failed: " . $e->getMessage();
        }
    }

    public function login($email, $password) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $this->db->commit();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                return true;
            }
            return "Invalid email or password.";
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return "Login error occurred.";
        }
    }
}