<?php
$envFile = $_SERVER['DOCUMENT_ROOT'] . "/laravel/.env";

// ⚠️ Allowed Keys
$allowedKeys = [
    'APP_NAME', 'APP_URL', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'TELEGRAM_ID', 'UPI_ID', 'SUPPORT_WHATSAPP_NUMBER'
];

// ✅ Read .env file
if (!file_exists($envFile)) {
    die("<p class='error'>⚠️ .env file not found!</p>");
}
$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);
$envData = [];

foreach ($envLines as $line) {
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', trim($line), 2);
        if (in_array($key, $allowedKeys)) {
            $envData[$key] = trim($value);
        }
    }
}

// ✅ Database Connection Check
$db_host = "localhost";
$db_name = $envData['DB_DATABASE'] ?? '';
$db_user = $envData['DB_USERNAME'] ?? '';
$db_pass = $envData['DB_PASSWORD'] ?? '';
$db_status = "";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_status = "<p class='success'>✅ Database Connected Successfully!</p>";
} catch (PDOException $e) {
    $db_status = "<p class='error'>❌ Database Connection Failed: " . $e->getMessage() . "</p>";
}

// ✅ Import Database Logic
if (isset($_POST['import_db']) && file_exists("import.sql")) {
    try {
        $sql = file_get_contents("import.sql");
        $pdo->exec($sql);
        echo "<p class='success'>✅ Database imported successfully!</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database import failed: " . $e->getMessage() . "</p>";
    }
}

// ✅ Update .env File Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['import_db'])) {
    $newEnvData = [];
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', trim($line), 2);
            if (in_array($key, $allowedKeys)) {
                $newValue = trim($_POST[$key]);
                if (strpos($newValue, ' ') !== false) {
                    die("<p class='error'>❌ Error: Space not allowed in values!</p>");
                }
                $newEnvData[] = "$key=$newValue";
            } else {
                $newEnvData[] = $line;
            }
        } else {
            $newEnvData[] = $line;
        }
    }
    file_put_contents($envFile, implode("\n", $newEnvData));
    echo "<p class='success'>✅ .env file updated successfully!</p>";
    header("Refresh:0");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>.env Editor | DigiRG Cloud</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #111; color: white; }
        table { width: 90%; max-width: 600px; margin: auto; border-collapse: collapse; background: #222; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #444; }
        input { width: 100%; padding: 8px; border: none; background: #333; color: white; }
        button { background: limegreen; color: black; padding: 12px; border: none; cursor: pointer; font-size: 16px; margin-top: 15px; transition: 0.3s; }
        button:hover { background: darkgreen; color: white; }
        .error { color: red; font-size: 18px; }
        .success { color: lime; font-size: 18px; }
        .watermark { position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 14px; color: cyan; font-weight: bold; animation: neon 1s infinite alternate; }
        @keyframes neon { 0% { text-shadow: 0 0 5px cyan, 0 0 10px cyan; } 100% { text-shadow: 0 0 10px cyan, 0 0 20px cyan; } }
    </style>
</head>
<body>
    <h2>🛠 Edit .env File</h2>
    <?= $db_status; ?>
    <form method="POST">
        <table border="1">
            <?php foreach ($envData as $key => $value): ?>
                <tr>
                    <th><?= htmlspecialchars($key) ?></th>
                    <td><input type="text" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>" required></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <button type="submit">💾 Save Changes</button>
    </form>
    <br>
    <?php if ($db_status === "<p class='success'>✅ Database Connected Successfully!</p>") : ?>
        <form method="POST">
            <button type="submit" name="import_db">📥 Import Database</button>
        </form>
    <?php endif; ?>
    <div class="watermark">CODE BY <a href="https://digirg.cloud" target="_blank" style="color: cyan; text-decoration: none;">DIGIRG CLOUD</a></div>
</body>
</html>
