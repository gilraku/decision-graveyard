<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || !empty($_SERVER['HTTPS']),
]);

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone']);

require __DIR__ . '/database.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/DecisionRepository.php';

function db(): PDO
{
    static $pdo = null;
    global $config;

    if ($pdo === null) {
        try {
            $pdo = make_pdo($config['db']);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('<p style="font-family:monospace;padding:2rem">Gagal terhubung ke database. '
                . 'Cek konfigurasi database dan pastikan PostgreSQL berjalan.'
                . ($config['app']['debug'] ? '<br><small>' . e($e->getMessage()) . '</small>' : '')
                . '</p>');
        }
    }
    return $pdo;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa. Muat ulang halaman.');
    }
}

$repo = new DecisionRepository(db());
