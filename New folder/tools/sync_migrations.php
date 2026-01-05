<?php
$dir = __DIR__ . '/../database/migrations';
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=active_mafra','root','123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $r = $pdo->query('SELECT IFNULL(MAX(batch),0) as maxb FROM migrations');
    $max = $r->fetch(PDO::FETCH_ASSOC);
    $batch = intval($max['maxb']);

    $files = glob($dir . '/*.php');
    sort($files);
    $inserted = 0;
    foreach ($files as $f) {
        $name = basename($f, '.php');
        $s = $pdo->prepare('SELECT COUNT(*) as c FROM migrations WHERE migration = ?');
        $s->execute([$name]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['c'] > 0) continue;
        $batch++;
        $ins = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        $ins->execute([$name, $batch]);
        $inserted++;
        echo "Inserted: $name with batch $batch" . PHP_EOL;
    }
    if ($inserted === 0) echo "No new migrations to insert." . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
