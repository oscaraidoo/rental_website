<?php
$pdo = new PDO('mysql:host=localhost;dbname=rental_website;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$cols = $pdo->query('SHOW COLUMNS FROM landlords')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' (' . $c['Type'] . ")\n";
