<?php
require_once 'config/Database.php';
require_once 'classes/User.php';
require_once 'auth.php';
require_guest();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $user = new User(Database::getInstance());
        $result = $user->login($email, $password);
        if ($result === true) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <div class="card">
            <h2>🥘 Member Login</h2>
            <?php if (isset($_GET['registered'])): ?>
                <div class="success-msg">Account created! You can now log in.</div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="error-list"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label>Password</label>
                <input type="password" name="password" required>
                <br><br>
                <button type="submit" style="width: 100%;">Log In</button>
            </form>
            <p style="margin-top: 15px; text-align: center;">New here? <a href="register.php">Create Account</a></p>
        </div>
    </div>
</body>
</html>