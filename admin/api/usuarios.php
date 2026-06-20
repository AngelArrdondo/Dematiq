<?php
require_once __DIR__ . '/../../includes/auth.php';

Auth::require('/pages/corporativo/login.php');

if (!Auth::csrfVerify()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
global $pdo;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── GET: list users ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $rows = $pdo->query(
        'SELECT id, username, nombre, primer_nombre, apellido_paterno, email_contacto,
                telefono, activo, ultimo_acceso, creado_en
         FROM usuarios ORDER BY creado_en ASC'
    )->fetchAll();
    echo json_encode(['ok' => true, 'data' => $rows]);
    exit;
}

// ── POST actions ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

function validUsername(string $u): bool {
    return preg_match('/^[a-zA-Z0-9_]{3,60}$/', $u) === 1;
}

switch ($action) {

    // ── Crear usuario ────────────────────────────────────────
    case 'create': {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nombre   = trim($_POST['nombre'] ?? '');

        if (!$username || !$password || !$nombre) {
            echo json_encode(['ok' => false, 'error' => 'Campos requeridos faltantes']); exit;
        }
        if (!validUsername($username)) {
            echo json_encode(['ok' => false, 'error' => 'Usuario solo puede tener letras, números y guion bajo (3–60 chars)']); exit;
        }
        if (strlen($password) < 8) {
            echo json_encode(['ok' => false, 'error' => 'Contraseña mínimo 8 caracteres']); exit;
        }

        $check = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if ($check->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'El usuario ya existe']); exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare(
            'INSERT INTO usuarios (username, password_hash, nombre, primer_nombre, apellido_paterno, email_contacto, telefono, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
        )->execute([
            $username, $hash,
            $nombre,
            trim($_POST['primer_nombre']    ?? $nombre),
            trim($_POST['apellido_paterno'] ?? ''),
            trim($_POST['email_contacto']   ?? '') ?: null,
            trim($_POST['telefono']         ?? '') ?: null,
        ]);

        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        break;
    }

    // ── Actualizar datos de usuario ─────────────────────────
    case 'update': {
        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$id || !$nombre) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']); exit;
        }
        $pdo->prepare(
            'UPDATE usuarios SET nombre=?, primer_nombre=?, apellido_paterno=?, email_contacto=?, telefono=?
             WHERE id=?'
        )->execute([
            $nombre,
            trim($_POST['primer_nombre']    ?? $nombre),
            trim($_POST['apellido_paterno'] ?? ''),
            trim($_POST['email_contacto']   ?? '') ?: null,
            trim($_POST['telefono']         ?? '') ?: null,
            $id,
        ]);
        echo json_encode(['ok' => true]);
        break;
    }

    // ── Activar / desactivar ─────────────────────────────────
    case 'toggle_activo': {
        $id     = (int) ($_POST['id'] ?? 0);
        $activo = ($_POST['activo'] ?? '') === '1' ? 1 : 0;
        if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID requerido']); exit; }
        $pdo->prepare('UPDATE usuarios SET activo=? WHERE id=?')->execute([$activo, $id]);
        if (!$activo) {
            $pdo->prepare('DELETE FROM sesiones WHERE usuario_id=?')->execute([$id]);
        }
        echo json_encode(['ok' => true]);
        break;
    }

    // ── Reset de contraseña ──────────────────────────────────
    case 'reset_password': {
        $id          = (int) ($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        if (!$id || strlen($new_password) < 8) {
            echo json_encode(['ok' => false, 'error' => 'Contraseña mínimo 8 caracteres']); exit;
        }
        $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare(
            'UPDATE usuarios SET password_hash=?, intentos_fallidos=0, bloqueado_hasta=NULL WHERE id=?'
        )->execute([$hash, $id]);
        $pdo->prepare('DELETE FROM sesiones WHERE usuario_id=?')->execute([$id]);
        echo json_encode(['ok' => true]);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción desconocida']);
}
