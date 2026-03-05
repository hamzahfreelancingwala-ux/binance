<?php
session_start();
include 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_balance = 0;

if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT username, wallet_balance_usd FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $user_balance = $user['wallet_balance_usd'] ?? 0;
}

$coins = [
    ['id' => 'bitcoin', 'name' => 'Bitcoin', 'symbol' => 'BTC'],
    ['id' => 'ethereum', 'name' => 'Ethereum', 'symbol' => 'ETH'],
    ['id' => 'binancecoin', 'name' => 'BNB', 'symbol' => 'BNB'],
    ['id' => 'solana', 'name' => 'Solana', 'symbol' => 'SOL'],
    ['id' => 'ripple', 'name' => 'Ripple', 'symbol' => 'XRP']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Binance Pro | Market</title>
    <style>
        :root { --gold: #f0b90b; --bg: #0b0e11; --row-hover: #2b3139; --red: #f6465d; }
        body { 
            background: var(--bg); color: white; font-family: 'Inter', sans-serif; margin: 0; padding: 0;
            background: radial-gradient(circle at center, #1e2329 0%, #0b0e11 100%);
        }
        .header { padding: 40px 20px; text-align: center; }
        .header h1 { font-size: 40px; text-transform: uppercase; letter-spacing: 4px; margin: 0; }
        .header span { color: var(--gold); text-shadow: 0 0 20px rgba(240, 185, 11, 0.5); }
        .container { max-width: 1000px; margin: 0 auto 50px; padding: 0 20px; }
        
        .market-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        .market-table th { text-align: left; padding: 10px 25px; color: #848e9c; font-size: 12px; }
        .market-table tr td { 
            background: rgba(30, 35, 41, 0.8); padding: 15px 25px; 
            border-bottom: 3px solid rgba(0,0,0,0.3); transition: 0.3s; 
        }
        .market-table tr td:first-child { border-radius: 10px 0 0 10px; }
        .market-table tr td:last-child { border-radius: 0 10px 10px 0; }
        .market-table tr:hover td { transform: translateY(-3px); background: var(--row-hover); border-bottom: 3px solid var(--gold); }
        
        .price-text { font-size: 20px; font-weight: bold; color: var(--gold); text-shadow: 0 0 5px rgba(240, 185, 11, 0.2); }
        
        /* 3D Buttons */
        .btn-3d { 
            background: #0ecb81; color: white; padding: 8px 20px; border-radius: 6px; 
            text-decoration: none; font-weight: bold; box-shadow: 0 4px 0 #0a9d63; display: inline-block; 
            transition: all 0.1s;
        }
        .btn-3d:active { transform: translateY(2px); box-shadow: 0 2px 0 #0a9d63; }

        .btn-logout { 
            background: var(--red); box-shadow: 0 4px 0 #b02d3e; margin-left: 10px;
        }
        .btn-logout:active { box-shadow: 0 2px 0 #b02d3e; }

        .nav { padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.5); border-bottom: 1px solid rgba(255,255,255,0.1); }
        #debug-log { color: #f6465d; text-align: center; font-size: 12px; margin-bottom: 10px; }
    </style>
</head>
<body>

<nav class="nav">
    <div style="font-weight:bold; font-size:20px; color:var(--gold);">BINANCE PRO</div>
    <div style="display: flex; align-items: center;">
        <?php if ($is_logged_in): ?>
            <span style="margin-right:15px; font-size:14px; color: #848e9c;">Balance: <strong style="color: white;">$<?php echo number_format($user_balance, 2); ?></strong></span>
            <a href="portfolio.php" style="color:white; text-decoration:none; font-size:14px; margin-right:15px;">Portfolio</a>
            <a href="logout.php" class="btn-3d btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-3d">Login</a>
        <?php endif; ?>
    </div>
</nav>

<div class="header">
    <h1>The Future of <span>Trading</span></h1>
</div>

<div class="container">
    <div id="debug-log"></div>
    <table class="market-table">
        <thead>
            <tr>
                <th>Asset</th>
                <th>Price (USDT)</th>
                <th>24h Change</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coins as $coin): ?>
            <tr>
                <td>
                    <div style="font-weight:bold;"><?php echo $coin['name']; ?></div>
                    <div style="color:#848e9c; font-size:11px;"><?php echo $coin['symbol']; ?></div>
                </td>
                <td class="price-text" id="price-<?php echo $coin['id']; ?>">---</td>
                <td id="change-<?php echo $coin['id']; ?>" style="font-weight:bold;">--</td>
                <td>
                    <a href="trade.php?coin=<?php echo $coin['id']; ?>" class="btn-3d">Trade</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    async function fetchMarkets() {
        const debug = document.getElementById('debug-log');
        // Map of CoinGecko IDs to Binance Symbols
        const coinMap = {
            'bitcoin': 'BTCUSDT',
            'ethereum': 'ETHUSDT',
            'binancecoin': 'BNBUSDT',
            'solana': 'SOLUSDT',
            'ripple': 'XRPUSDT'
        };

        try {
            // Using Binance API directly (much faster and no rate limits for basic use)
            const symbols = Object.values(coinMap).map(s => `"${s}"`).join(',');
            const response = await fetch(`https://api.binance.com/api/v3/ticker/24hr?symbols=[${symbols}]`);
            
            if (!response.ok) throw new Error("API Error");

            const data = await response.json();
            
            data.forEach(ticker => {
                // Find the coin ID from the symbol
                const coinId = Object.keys(coinMap).find(key => coinMap[key] === ticker.symbol);
                if (coinId) {
                    const priceEl = document.getElementById(`price-${coinId}`);
                    const changeEl = document.getElementById(`change-${coinId}`);
                    
                    const price = parseFloat(ticker.lastPrice);
                    const change = parseFloat(ticker.priceChangePercent);

                    priceEl.innerText = '$' + price.toLocaleString(undefined, {minimumFractionDigits: 2});
                    changeEl.innerText = (change >= 0 ? '+' : '') + change.toFixed(2) + '%';
                    changeEl.style.color = change >= 0 ? '#0ecb81' : '#f6465d';
                }
            });

            debug.innerText = ""; 
        } catch (e) {
            console.error("API Error, trying fallback...", e);
            debug.innerText = "Connection slow. Updating...";
        }
    }

    fetchMarkets();
    setInterval(fetchMarkets, 5000); // Faster updates with Binance API (every 5 seconds)
</script>

</body>
</html>
