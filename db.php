<?php
$host = "localhost";
$dbname = "rsoa_rsoa112_28";
$username = "rsoa_rsoa112_28";
$password = "654321#";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
