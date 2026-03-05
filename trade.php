<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$coin_id = isset($_GET['coin']) ? $_GET['coin'] : 'bitcoin';

// Get Current Balance
$stmt = $pdo->prepare("SELECT wallet_balance_usd FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_balance = $stmt->fetchColumn() ?: 0;

// Coin Mapping
$mapper = ['bitcoin'=>'BTC', 'ethereum'=>'ETH', 'binancecoin'=>'BNB', 'solana'=>'SOL', 'ripple'=>'XRP', 'dogecoin'=>'DOGE', 'tether'=>'USDT'];
$display_symbol = isset($mapper[$coin_id]) ? $mapper[$coin_id] : 'BTC';

// Trade Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['spend_amount'])) {
    $price = floatval($_POST['price']);
    $spend = floatval($_POST['spend_amount']);

    if ($spend > 0 && $current_balance >= $spend) {
        $qty = $spend / $price; // Calculation for fractional amounts
        try {
            $pdo->beginTransaction();
            // Deduct from Balance
            $pdo->prepare("UPDATE users SET wallet_balance_usd = wallet_balance_usd - ? WHERE id = ?")->execute([$spend, $user_id]);
            
            // Log Trade - Column is 'coin_name' to match SQL above
            $sql = "INSERT INTO orders (user_id, coin_name, type, price, amount, status) VALUES (?, ?, 'buy', ?, ?, 'completed')";
            $pdo->prepare($sql)->execute([$user_id, $display_symbol, $price, $qty]);
            
            $pdo->commit();
            echo "<script>alert('Trade Success! Spent $$spend'); window.location.href='portfolio.php';</script>";
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('DB Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Error: You only have $$current_balance USDT. Reduce the amount to trade.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trade <?php echo $display_symbol; ?></title>
    <style>
        body { background: #0b0e11; color: white; font-family: sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .chart { flex: 3; border-right: 1px solid #2b3139; }
        .sidebar { flex: 1; padding: 30px; background: #1e2329; min-width: 350px; }
        .box { background: #2b3139; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #474d57; }
        label { color: #848e9c; font-size: 12px; display: block; margin-bottom: 8px; }
        input { background: transparent; border: none; color: #f0b90b; width: 100%; font-size: 18px; outline: none; }
        .btn { background: #0ecb81; color: white; width: 100%; padding: 18px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <div class="chart" id="tv_chart"></div>
    <div class="sidebar">
        <h2>Buy <?php echo $display_symbol; ?></h2>
        <form method="POST">
            <div class="box">
                <label>Price (USDT)</label>
                <input type="number" name="price" id="lp" step="any" readonly required>
            </div>
            <div class="box">
                <label>I want to spend (USDT)</label>
                <input type="number" name="spend_amount" placeholder="e.g. 1.00" step="any" required>
            </div>
            <button class="btn">Buy <?php echo $display_symbol; ?></button>
        </form>
        <p style="margin-top:20px; color:#848e9c;">Balance: <b>$<?php echo number_format($current_balance, 2); ?></b></p>
    </div>

    <script src="https://s3.tradingview.com/tv.js"></script>
    <script>
        new TradingView.widget({"autosize": true, "symbol": "BINANCE:<?php echo $display_symbol; ?>USDT", "theme": "dark", "container_id": "tv_chart"});
        async function getPrice() {
            const r = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=<?php echo $coin_id; ?>&vs_currencies=usd');
            const d = await r.json();
            document.getElementById('lp').value = d['<?php echo $coin_id; ?>'].usd;
        }
        getPrice();
        setInterval(getPrice, 15000);
    </script>
</body>
</html>
