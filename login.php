<?php
session_start();
include 'db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo "<script>window.location.href = 'index.php';</script>";
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Binance Clone</title>
    <style>
        :root { --bg: #0b0e11; --card: #1e2329; --gold: #f0b90b; --text: #eaecef; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: var(--card); padding: 40px; border-radius: 10px; width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: var(--gold); margin-bottom: 25px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #2b3139; border: 1px solid #474d57; color: white; border-radius: 5px; box-sizing: border-box; }
        .btn-auth { width: 100%; padding: 12px; background: var(--gold); border: none; font-weight: bold; border-radius: 5px; cursor: pointer; margin-top: 15px; }
        .link { display: block; text-align: center; margin-top: 15px; color: #848e9c; text-decoration: none; font-size: 14px; }
        .error { color: #f6465d; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Login to Binance</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-auth">Log In</button>
        </form>
        <a href="signup.php" class="link">Don't have an account? Sign Up</a>
    </div>
</body>
</html>
