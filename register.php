<?php
require_once 'config/Database.php';
require_once 'classes/User.php';
require_once 'auth.php';
require_guest();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName))  $errors[] = "Last name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email address is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $user = new User(Database::getInstance());
        $result = $user->register($firstName, $lastName, $email, $password);
        if ($result === true) {
            header("Location: login.php?registered=1");
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
    <title>Register - Lutong Barangay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 450px; margin-top: 50px;">
        <div class="card">
            <h2>🍳 Join Lutong Barangay</h2>
            <?php if (!empty($errors)): ?>
                <div class="error-list"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="POST" action="register.php" onsubmit="return validateRegister()">
                <label>First Name</label>
                <input type="text" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                <label>Last Name</label>
                <input type="text" name="last_name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                <label>Email Address</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label>Password (min 6 chars)</label>
                <input type="password" id="password" name="password" minlength="6" required>
                <label>Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                <br><br>
                <button type="submit" style="width: 100%;">Create Account</button>
            </form>
            <p style="margin-top: 15px; text-align: center;">Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
    <script>
    function validateRegister() {
        if (document.getElementById('password').value !== document.getElementById('confirm_password').value) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>