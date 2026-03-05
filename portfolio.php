<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current balance
$stmt = $pdo->prepare("SELECT wallet_balance_usd FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$balance = $stmt->fetchColumn() ?: 0;

// FETCH ORDERS - Fixed to use 'coin_name' to match the new SQL table
$query = "SELECT coin_name, type, price, amount, (price * amount) as total_usd, created_at 
          FROM orders 
          WHERE user_id = ? 
          ORDER BY created_at DESC";
$orders = $pdo->prepare($query);
$orders->execute([$user_id]);
$trades = $orders->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Portfolio</title>
    <style>
        body { background: #0b0e11; color: white; font-family: sans-serif; padding: 40px; }
        .card { background: #1e2329; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #848e9c; font-size: 12px; border-bottom: 1px solid #2b3139; padding: 10px; }
        td { padding: 15px 10px; border-bottom: 1px solid #2b3139; font-size: 14px; }
        .buy { color: #0ecb81; font-weight: bold; }
        .balance-num { font-size: 32px; color: #f0b90b; font-weight: bold; }
    </style>
</head>
<body>

    <div class="card">
        <label style="color: #848e9c;">Total Balance</label><br>
        <span class="balance-num">$<?php echo number_format($balance, 2); ?></span> <small>USDT</small>
    </div>

    <div class="card">
        <h3>Trade History</h3>
        <table>
            <thead>
                <tr>
                    <th>Coin</th>
                    <th>Type</th>
                    <th>Entry Price</th>
                    <th>Amount Obtained</th>
                    <th>Total Spent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trades) > 0): ?>
                    <?php foreach ($trades as $trade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trade['coin_name']); ?></td>
                            <td class="buy"><?php echo strtoupper($trade['type']); ?></td>
                            <td>$<?php echo number_format($trade['price'], 2); ?></td>
                            <td><?php echo number_format($trade['amount'], 8); ?></td>
                            <td>$<?php echo number_format($trade['total_usd'], 2); ?></td>
                            <td><?php echo $trade['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:#848e9c;">No trades found yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p><a href="index.php" style="color:#f0b90b; text-decoration:none;">← Back to Markets</a></p>

</body>
</html>
