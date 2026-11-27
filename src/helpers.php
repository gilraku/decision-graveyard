<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

const CATEGORIES = [
    'rip'       => ['label' => 'R.I.P. (umum)',      'base' => 'tombstone-rip',    'icon' => null],
    'code'      => ['label' => 'Coding / Startup',   'base' => 'tombstone-cracked', 'icon' => 'icon-code'],
    'laptop'    => ['label' => 'Project / Website',  'base' => 'tombstone-cracked', 'icon' => 'icon-laptop'],
    'rocket'    => ['label' => 'Bisnis / Ambisi',    'base' => 'tombstone-cracked', 'icon' => 'icon-rocket'],
    'fitness'   => ['label' => 'Olahraga',           'base' => 'tombstone-cracked', 'icon' => 'icon-fitness'],
    'book'      => ['label' => 'Membaca',            'base' => 'tombstone-cracked', 'icon' => 'icon-book'],
    'education' => ['label' => 'Belajar / Kuliah',   'base' => 'tombstone-cracked', 'icon' => 'icon-education'],
    'travel'    => ['label' => 'Traveling',          'base' => 'tombstone-cracked', 'icon' => 'icon-travel'],
    'money'     => ['label' => 'Keuangan / Nabung',  'base' => 'tombstone-cracked', 'icon' => 'icon-money'],
    'game'      => ['label' => 'Gaming',             'base' => 'tombstone-cracked', 'icon' => 'icon-game'],
    'movie'     => ['label' => 'Film / Konten',      'base' => 'tombstone-cracked', 'icon' => 'icon-movie'],
    'coffee'    => ['label' => 'Kebiasaan Harian',   'base' => 'tombstone-cracked', 'icon' => 'icon-coffee'],
    'plant'     => ['label' => 'Hobi / Berkebun',    'base' => 'tombstone-cracked', 'icon' => 'icon-plant'],
    'skull'     => ['label' => 'Ide Buruk',          'base' => 'tombstone-skull',  'icon' => null],
    'laurel'    => ['label' => 'Dikenang Terhormat', 'base' => 'tombstone-laurel', 'icon' => null],
    'cross'     => ['label' => 'Klasik (salib)',     'base' => 'tombstone-cross',  'icon' => null],
];

function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/');
}

const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function month_label(?string $date): string
{
    if (!$date) {
        return '—';
    }
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $d = new DateTimeImmutable($date);
    return $bulan[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
}

function view(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/views/' . $name . '.php';
    return (string) ob_get_clean();
}

function is_htmx(): bool
{
    return isset($_SERVER['HTTP_HX_REQUEST']);
}

function validate_decision(array $in): array
{
    $title    = trim((string) ($in['title'] ?? ''));
    $cause    = trim((string) ($in['cause_of_death'] ?? ''));
    $category = (string) ($in['category'] ?? 'rip');
    $ym = fn(string $f) => isset($in[$f . '_month'], $in[$f . '_year'])
        ? trim((string) $in[$f . '_year']) . '-' . trim((string) $in[$f . '_month'])
        : trim((string) ($in[$f . '_on'] ?? ''));
    $born = $ym('born');
    $died = $ym('died');

    $errors = [];

    if ($title === '') {
        $errors['title'] = 'Isi nama keputusan atau rencananya.';
    } elseif (mb_strlen($title) > 100) {
        $errors['title'] = 'Nama keputusan maksimal 100 karakter.';
    }

    if ($cause === '') {
        $errors['cause_of_death'] = 'Isi penyebab kematiannya, misalnya “kurang waktu”.';
    } elseif (mb_strlen($cause) > 255) {
        $errors['cause_of_death'] = 'Penyebab kematian maksimal 255 karakter.';
    }

    if (!array_key_exists($category, CATEGORIES)) {
        $category = 'rip';
    }

    $toDate = function (string $v, string $key, string $nama) use (&$errors): string {
        if ($v === '') {
            return date('Y-m-01');
        }
        if (preg_match('/^(\d{4})-(\d{2})$/', $v, $m) && checkdate((int) $m[2], 1, (int) $m[1])) {
            $date = "{$m[1]}-{$m[2]}-01";
            if ($date > date('Y-m-01')) {
                $errors[$key] = "Bulan $nama tidak boleh setelah bulan ini.";
            }
            return $date;
        }
        $errors[$key] = "Pilih bulan dan tahun $nama.";
        return date('Y-m-01');
    };
    $bornOn = $toDate($born, 'born_on', 'mulai');
    $diedOn = $toDate($died, 'died_on', 'berhenti');
    if (!isset($errors['born_on']) && !isset($errors['died_on']) && $diedOn < $bornOn) {
        $errors['died_on'] = 'Bulan berhenti tidak boleh sebelum bulan mulai.';
    }

    return [[
        'title'          => $title,
        'cause_of_death' => $cause,
        'category'       => $category,
        'born_on'        => $bornOn,
        'died_on'        => $diedOn,
    ], $errors];
}
