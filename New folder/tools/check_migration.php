<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=active_mafra','root','123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $s = $pdo->prepare('SELECT migration,batch FROM migrations WHERE migration=?');
    $s->execute(['2019_12_14_000001_create_personal_access_tokens_table']);
    $rows = $s->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
