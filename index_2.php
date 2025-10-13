<?php

echo "<h1>Witaj student</h1>";

$host = 'db';
$port = '5432';
$dbname = 'db';
$user = 'docker';
$password = 'docker';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SELECT * FROM students");

    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>{$row['id']}. {$row['name']} {$row['surname']}</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
}

?>
