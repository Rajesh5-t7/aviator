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
    $db_status = "<p class='success'>✔ UPDATED </p>";

    // ✅ Fetch Latest UPI_ID from SQL Table
    $stmt = $pdo->query("SELECT upi_id FROM bankdetails ORDER BY id DESC LIMIT 1");
    $latestUpiId = $stmt->fetchColumn();
    $currentUpiId = $envData['UPI_ID'] ?? '';

    if ($latestUpiId && $latestUpiId !== $currentUpiId) {
        // ✅ Update .env File with Latest UPI_ID
        $newEnvData = [];
        $updated = false;
        
        foreach ($envLines as $line) {
            if (strpos($line, 'UPI_ID=') !== false) {
                $newEnvData[] = "UPI_ID=$latestUpiId";
                $updated = true;
            } else {
                $newEnvData[] = $line;
            }
        }

        if ($updated) {
            file_put_contents($envFile, implode("\n", $newEnvData));
            echo "<p class='success'>✅YOUR NEW UPI ID UPDATED  ($latestUpiId)!/p>";
        }
    } else {
        echo "<p class='info'>DONE </p>";
    }

} catch (PDOException $e) {
    $db_status = "<p class='error'>❌ Database Connection Failed: " . $e->getMessage() . "</p>";
}

echo $db_status;
?>
