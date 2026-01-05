<?php
if ($argc < 2) {
    echo "Usage: php mark_migration_generic.php <migration_name>\n";
    exit(1);
}
$name = $argv[1];
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=active_mafra','root','123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $s = $pdo->prepare('SELECT COUNT(*) as c FROM migrations WHERE migration = ?');
    $s->execute([$name]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['c'] > 0) {
        echo "Migration already recorded in migrations table.".PHP_EOL;
        exit(0);
    }

    $r = $pdo->query('SELECT IFNULL(MAX(batch),0) as maxb FROM migrations');
    $max = $r->fetch(PDO::FETCH_ASSOC);
    $batch = intval($max['maxb']) + 1;

    $ins = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
    $ins->execute([$name, $batch]);

    echo "Inserted migration '$name' with batch $batch" . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
