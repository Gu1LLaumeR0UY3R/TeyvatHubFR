<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=genshin_db', 'root', '');
$stmt = $pdo->query("DESCRIBE rareté");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
