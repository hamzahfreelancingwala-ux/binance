<?php
include 'db.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$user, $email, $pass]);
        $msg = "Account Created Successfully!";
        echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 2000);</script>";
    } catch (PDOException $e) {
        $msg = "Error: Email or Username already exists.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Binance Clone</title>
    <style>
        :root { --bg: #0b0e11; --card: #1e2329; --gold: #f0b90b; --text: #eaecef; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: var(--card); padding: 40px; border-radius: 10px; width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: var(--gold); margin-bottom: 25px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #2b3139; border: 1px solid #474d57; color: white; border-radius: 5px; box-sizing: border-box; }
        .btn-auth { width: 100%; padding: 12px; background: var(--gold); border: none; font-weight: bold; border-radius: 5px; cursor: pointer; margin-top: 15px; }
        .link { display: block; text-align: center; margin-top: 15px; color: #848e9c; text-decoration: none; font-size: 14px; }
        .msg { color: #0ecb81; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Create Account</h2>
        <?php if($msg) echo "<p class='msg'>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-auth">Register Now</button>
        </form>
        <a href="login.php" class="link">Already have an account? Log In</a>
    </div>
</body>
</html>
