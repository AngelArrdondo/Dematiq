<?php
require_once __DIR__ . '/../../includes/auth.php';

Auth::require('/pages/corporativo/login.php');

header('Content-Type: application/json; charset=utf-8');
global $pdo;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── GET: scan image folders ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $base = realpath(__DIR__ . '/../../assets/images');
    $folders = [
        'general'  => $base . '/general',
        'partners' => $base . '/partners',
        'avatares' => realpath(__DIR__ . '/../assets/avatars'),
    ];

    $result = [];
    $exts   = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    foreach ($folders as $key => $dir) {
        $result[$key] = [];
        if (!$dir || !is_dir($dir)) continue;
        $files = @scandir($dir);
        if (!$files) continue;
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $exts, true)) continue;
            if ($key === 'avatares') {
                $webPath = '/admin/assets/avatars/' . $f;
            } else {
                $webPath = '/assets/images/' . $key . '/' . $f;
            }
            $result[$key][] = [
                'name' => $f,
                'path' => $webPath,
                'size' => filesize($dir . '/' . $f),
            ];
        }
    }

    echo json_encode(['ok' => true, 'data' => $result]);
    exit;
}

// ── POST: delete image ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    if (!Auth::csrfVerify()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
        exit;
    }

    $rawPath = $_POST['path'] ?? '';
    $base    = realpath(__DIR__ . '/../../');

    // Resolve to absolute and verify it stays inside project
    $abs = realpath($base . '/' . ltrim($rawPath, '/'));

    if (!$abs || strpos($abs, $base . '/') !== 0) {
        echo json_encode(['ok' => false, 'error' => 'Ruta no válida']);
        exit;
    }

    // Only allow images inside safe folders
    $allowed = [
        realpath($base . '/assets/images/general'),
        realpath($base . '/assets/images/partners'),
        realpath(__DIR__ . '/../assets/avatars'),
    ];
    $inAllowed = false;
    foreach ($allowed as $dir) {
        if ($dir && strpos($abs, $dir . '/') === 0) { $inAllowed = true; break; }
    }
    if (!$inAllowed) {
        echo json_encode(['ok' => false, 'error' => 'Carpeta no permitida']);
        exit;
    }

    $exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    if (!in_array(strtolower(pathinfo($abs, PATHINFO_EXTENSION)), $exts, true)) {
        echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido']);
        exit;
    }

    if (!file_exists($abs)) {
        echo json_encode(['ok' => false, 'error' => 'Archivo no encontrado']);
        exit;
    }

    if (@unlink($abs)) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo eliminar']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no reconocida']);
