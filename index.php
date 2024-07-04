<?php
$host = 'tmosspace.b14tvc56.ru';
$dbname = 'x168nhtem20t5a';
$username = '8ecrdjyhobtwqs';
$password = 'W$x~{3N*aZCj';
$ssl_ca = '/crt/lm0/pl-cDem-cert-20.pem';

$options = [
    PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function encrypt_data($data) {
    $encryption_key = 'my_secret_key';
    return openssl_encrypt($data, 'AES-128-ECB', $encryption_key);
}

function decrypt_data($data) {
    $encryption_key = 'my_secret_key';
    return openssl_decrypt($data, 'AES-128-ECB', $encryption_key);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = encrypt_data($_POST['card_number']);
    $purchase_date = encrypt_data($_POST['purchase_date']);
    $expiry_date = encrypt_data($_POST['expiry_date']);

    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE card_number = :card_number");
    $stmt->execute(['card_number' => $card_number]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        $stmt = $pdo->prepare("UPDATE tickets SET purchase_date = :purchase_date, expiry_date = :expiry_date WHERE card_number = :card_number");
        $stmt->execute([
            'purchase_date' => $purchase_date,
            'expiry_date' => $expiry_date,
            'card_number' => $card_number
        ]);
        echo "Данные обновлены успешно!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tickets (card_number, purchase_date, expiry_date) VALUES (:card_number, :purchase_date, :expiry_date)");
        $stmt->execute([
            'card_number' => $card_number,
            'purchase_date' => $purchase_date,
            'expiry_date' => $expiry_date
        ]);
        echo "Данные сохранены успешно!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <form method="post" action="">
        <label for="card_number">Номер карты:</label>
        <input type="text" id="card_number" name="card_number" required><br><br>

        <label for="purchase_date">Дата покупки:</label>
        <input type="date" id="purchase_date" name="purchase_date" required><br><br>

        <label for="expiry_date">Срок истечения билета:</label>
        <input type="date" id="expiry_date" name="expiry_date" required><br><br>

        <button type="submit">Обновить</button>
    </form>
</body>
</html>
