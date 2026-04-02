<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=genshin_db', 'root', '');
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$tables = ['personnage_video', 'artefact', 'personnage_arme_recommandee', 'personnage_artefact_recommandee'];
foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `$table`");
    echo "Dropped $table\n";
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
