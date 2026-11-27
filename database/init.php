<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../src/database.php';

$pdo = null;
for ($i = 1; $i <= 30; $i++) {
    try {
        $pdo = make_pdo($config['db']);
        break;
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'could not find driver')) {
            fwrite(STDERR, "[init] ekstensi pdo_pgsql belum terpasang.\n");
            exit(1);
        }
        fwrite(STDERR, "[init] database belum siap ($i/30): {$e->getMessage()}\n");
        sleep(2);
    }
}
if (!$pdo) {
    fwrite(STDERR, "[init] gagal terhubung ke database, inisialisasi dilewati.\n");
    exit(1);
}

$exists = $pdo->query("SELECT to_regclass('public.decisions') IS NOT NULL")->fetchColumn();
if (!$exists) {
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
    echo "[init] tabel decisions dibuat.\n";
}

$pdo->exec("ALTER TABLE decisions ADD COLUMN IF NOT EXISTS died_on DATE NOT NULL DEFAULT CURRENT_DATE");

$seed  = strtolower((string) (getenv('SEED_DEMO') ?: 'true')) !== 'false';
$count = (int) $pdo->query('SELECT COUNT(*) FROM decisions')->fetchColumn();
if ($seed && $count === 0) {
    $pdo->exec(file_get_contents(__DIR__ . '/seed.sql'));
    echo "[init] data contoh dimasukkan.\n";
}

echo "[init] database siap.\n";
