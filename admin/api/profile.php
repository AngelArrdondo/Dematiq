<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';

header('Content-Type: application/json');

$user = Auth::check();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

if ($action === 'get') {
    try {
        $stmt = $pdo->prepare('SELECT id, username, nombre, foto FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $profile = $stmt->fetch();
    } catch (PDOException $e) {
        // foto column not yet migrated — fetch without it
        $stmt = $pdo->prepare('SELECT id, username, nombre FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $profile = $stmt->fetch();
        $profile['foto'] = null;
    }
    echo json_encode(['ok' => true, 'data' => $profile]);

} elseif ($action === 'update_info') {
    $nombre = trim($_POST['nombre'] ?? '');
    if (!$nombre) {
        echo json_encode(['ok' => false, 'msg' => 'El nombre es requerido']);
        exit;
    }
    if (mb_strlen($nombre) > 100) {
        echo json_encode(['ok' => false, 'msg' => 'Nombre demasiado largo']);
        exit;
    }
    $pdo->prepare('UPDATE usuarios SET nombre = ? WHERE id = ?')->execute([$nombre, $user['id']]);
    echo json_encode(['ok' => true, 'msg' => 'Perfil actualizado']);

} elseif ($action === 'upload_avatar') {
    if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'msg' => 'Sin archivo o error al recibir']);
        exit;
    }

    $file    = $_FILES['avatar'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = mime_content_type($file['tmp_name']);

    if (!in_array($mime, $allowed, true)) {
        echo json_encode(['ok' => false, 'msg' => 'Formato no permitido (JPG, PNG, WebP, GIF)']);
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'msg' => 'Máximo 2 MB']);
        exit;
    }

    $ext       = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    };
    $filename  = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../assets/avatars/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Remove old avatar file
    try { $stmt = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1'); $stmt->execute([$user['id']]); } catch (PDOException $e) { echo json_encode(['ok' => false, 'msg' => 'Columna foto no existe aún. Ejecuta la migración de base de datos.']); exit; }
    $oldFoto = $stmt->fetchColumn();
    if ($oldFoto) {
        $oldFile = __DIR__ . '/../' . ltrim($oldFoto, '/');
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        echo json_encode(['ok' => false, 'msg' => 'Error al guardar la imagen']);
        exit;
    }

    $path = 'assets/avatars/' . $filename;
    $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?')->execute([$path, $user['id']]);
    echo json_encode(['ok' => true, 'path' => $path]);

} elseif ($action === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        echo json_encode(['ok' => false, 'msg' => 'Todos los campos son requeridos']);
        exit;
    }
    if ($new !== $confirm) {
        echo json_encode(['ok' => false, 'msg' => 'Las contraseñas no coinciden']);
        exit;
    }
    if (strlen($new) < 8) {
        echo json_encode(['ok' => false, 'msg' => 'Mínimo 8 caracteres']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        echo json_encode(['ok' => false, 'msg' => 'Contraseña actual incorrecta']);
        exit;
    }

    $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')->execute([$newHash, $user['id']]);
    echo json_encode(['ok' => true, 'msg' => 'Contraseña cambiada correctamente']);

} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Acción no reconocida']);
}
