<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=active_mafra','root','123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $s = $pdo->query("SHOW CREATE TABLE personal_access_tokens");
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo $row['Create Table'] . PHP_EOL;
    } else {
        echo "NO_TABLE" . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
