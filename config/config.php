<?php
$url = getenv('DATABASE_URL') ?: '';
$u   = $url !== '' ? parse_url($url) : [];

return [
    'db' => [
        'host'    => $u['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1'),
        'port'    => (string) ($u['port'] ?? (getenv('DB_PORT') ?: '5432')),
        'name'    => isset($u['path']) ? ltrim($u['path'], '/') : (getenv('DB_NAME') ?: 'decision_graveyard'),
        'user'    => isset($u['user']) ? urldecode($u['user']) : (getenv('DB_USER') ?: 'postgres'),
        'pass'    => isset($u['pass']) ? urldecode($u['pass']) : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'postgres'),
        'sslmode' => getenv('DB_SSLMODE') ?: 'prefer',
    ],
    'app' => [
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Makassar',
        'debug'    => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
    ],
];
