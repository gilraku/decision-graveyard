<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $method === 'POST' ? ($_POST['action'] ?? '') : ($_GET['action'] ?? 'list');
$id     = (int) ($method === 'POST' ? ($_POST['id'] ?? 0) : ($_GET['id'] ?? 0));
$sort   = $_REQUEST['sort'] ?? 'latest';
$sort   = array_key_exists($sort, DecisionRepository::SORTS) ? $sort : 'latest';

function back(string $view = 'graveyard', array $extra = []): never
{
    $_SESSION['flash'] = $extra['flash'] ?? null;
    unset($extra['flash']);
    header('Location: index.php?' . http_build_query(['view' => $view] + $extra), true, 303);
    exit;
}

function toast(string $message, array $extra = []): void
{
    $events = ['toast' => ['value' => $message, 'target' => 'body']];
    foreach ($extra as $name) {
        $events[$name] = ['target' => 'body'];
    }
}

function removed_card_response(DecisionRepository $repo, string $view): void
{
    $status = $view === 'archive' ? 'resurrected' : 'buried';
    $count  = $repo->count($status);
    echo view('count', ['count' => $count, 'oob' => true]);
    echo view('empty', ['view' => $view, 'show' => $count === 0, 'oob' => true]);
}

if ($method === 'POST') {
    verify_csrf();
}

switch ($action) {

    case 'list':
        $view   = ($_GET['view'] ?? '') === 'archive' ? 'archive' : 'graveyard';
        $items  = $repo->all($view === 'archive' ? 'resurrected' : 'buried', $sort);
        header('HX-Push-Url: index.php?' . http_build_query(['view' => $view, 'sort' => $sort]));
        echo view('results', ['items' => $items, 'view' => $view]);
        break;

    case 'edit':
        $d = $repo->find($id);
        if (!$d) {
            http_response_code(404);
            exit('<p>Nisan tidak ditemukan.</p>');
        }
        echo '<h1 class="modal__title" id="modal-title">Ubah nisan</h1>';
        echo view('decision_form', ['mode' => 'edit', 'data' => $d, 'id' => $id]);
        break;

    case 'create':
        [$data, $errors] = validate_decision($_POST);
        if ($errors) {
            if (!is_htmx()) {
                back('graveyard', ['flash' => reset($errors)]);
            }
            header('HX-Retarget: #bury-form');
            header('HX-Reswap: outerHTML');
            echo view('decision_form', ['mode' => 'create', 'data' => $data, 'errors' => $errors]);
            break;
        }
        $newId = $repo->create($data);
        if (!is_htmx()) {
            back('graveyard', ['flash' => '“' . $data['title'] . '” dikubur.']);
        }
        toast('“' . $data['title'] . '” dikubur.');
        echo view('results', ['items' => $repo->all('buried', $sort), 'view' => 'graveyard', 'newId' => $newId]);
        echo view('count', ['count' => $repo->count('buried'), 'oob' => true]);
        echo view('decision_form', ['mode' => 'create', 'data' => [], 'oob' => true]);
        break;

    case 'update':
        $existing = $repo->find($id);
        if (!$existing) {
            http_response_code(404);
            exit('Nisan tidak ditemukan.');
        }
        [$data, $errors] = validate_decision($_POST);
        if ($errors) {
            if (!is_htmx()) {
                back($existing['status'] === 'buried' ? 'graveyard' : 'archive', ['edit' => $id, 'flash' => reset($errors)]);
            }
            header('HX-Retarget: #edit-form');
            header('HX-Reswap: outerHTML');
            echo view('decision_form', ['mode' => 'edit', 'data' => $data + ['id' => $id], 'errors' => $errors, 'id' => $id]);
            break;
        }
        $repo->update($id, $data);
        if (!is_htmx()) {
            back($existing['status'] === 'buried' ? 'graveyard' : 'archive', ['flash' => 'Nisan disimpan.']);
        }
        toast('Nisan disimpan.', ['closeModal']);
        echo view('card', ['d' => $repo->find($id)]);
        break;

    case 'resurrect':
    case 'rebury':
        $d = $repo->find($id);
        $repo->setStatus($id, $action === 'resurrect' ? 'resurrected' : 'buried');
        $from  = $action === 'resurrect' ? 'graveyard' : 'archive';
        $title = '“' . ($d['title'] ?? 'Keputusan') . '”';
        $msg   = $action === 'resurrect'
            ? $title . ' dibangkitkan. Sekarang ada di halaman Hidup lagi.'
            : $title . ' dikubur lagi.';
        if (!is_htmx()) {
            back($from, ['flash' => $msg]);
        }
        toast($msg);
        removed_card_response($repo, $from);
        break;

    case 'delete':
        $d = $repo->find($id);
        $repo->delete($id);
        $from = ($d && $d['status'] === 'resurrected') ? 'archive' : 'graveyard';
        if (!is_htmx()) {
            back($from, ['flash' => '“' . ($d['title'] ?? 'Keputusan') . '” dilupakan.']);
        }
        toast('“' . ($d['title'] ?? 'Keputusan') . '” dilupakan.');
        removed_card_response($repo, $from);
        break;

    default:
        http_response_code(400);
        echo 'Aksi tidak dikenal.';
}
