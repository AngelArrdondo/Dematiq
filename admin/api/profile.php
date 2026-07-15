<?php
ini_set('display_errors', 0);   // evita que warnings contaminen el JSON
ob_start();                      // captura cualquier salida accidental

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';

ob_end_clean();                  // descarta cualquier output de los includes
header('Content-Type: application/json');

$user = Auth::check();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

// Verificar CSRF en todas las escrituras
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Auth::csrfVerify()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Solicitud no autorizada']);
    exit;
}

if ($action === 'get') {
    try {
        $stmt = $pdo->prepare('SELECT id, username, nombre, primer_nombre, segundo_nombre, apellido_paterno, apellido_materno, email_contacto, telefono, foto FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $profile = $stmt->fetch();
    } catch (PDOException $e) {
        // New columns not yet migrated — fall back
        try {
            $stmt = $pdo->prepare('SELECT id, username, nombre, foto FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch();
        } catch (PDOException $e2) {
            $stmt = $pdo->prepare('SELECT id, username, nombre FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch();
            $profile['foto'] = null;
        }
        $profile['primer_nombre']    = '';
        $profile['segundo_nombre']   = null;
        $profile['apellido_paterno'] = '';
        $profile['apellido_materno'] = null;
        $profile['email_contacto']   = null;
        $profile['telefono']         = null;
    }
    echo json_encode(['ok' => true, 'data' => $profile]);

} elseif ($action === 'update_info') {
    $primer_nombre    = trim($_POST['primer_nombre'] ?? '');
    $segundo_nombre   = trim($_POST['segundo_nombre'] ?? '') ?: null;
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '') ?: null;
    $email_contacto   = trim($_POST['email_contacto'] ?? '') ?: null;
    $telefono         = trim($_POST['telefono'] ?? '') ?: null;

    if (!$primer_nombre) {
        echo json_encode(['ok' => false, 'msg' => 'El primer nombre es requerido']);
        exit;
    }
    if (!$apellido_paterno) {
        echo json_encode(['ok' => false, 'msg' => 'El apellido paterno es requerido']);
        exit;
    }
    if (strlen($primer_nombre) > 60 || strlen($apellido_paterno) > 60) {
        echo json_encode(['ok' => false, 'msg' => 'Nombre demasiado largo (máx 60 caracteres)']);
        exit;
    }
    if (!$apellido_materno) {
        echo json_encode(['ok' => false, 'msg' => 'El apellido materno es requerido']);
        exit;
    }
    if (!$email_contacto) {
        echo json_encode(['ok' => false, 'msg' => 'El email de contacto es requerido']);
        exit;
    }
    if (!filter_var($email_contacto, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'msg' => 'Email de contacto inválido']);
        exit;
    }
    if (!$telefono) {
        echo json_encode(['ok' => false, 'msg' => 'El teléfono es requerido']);
        exit;
    }

    $parts  = array_filter([$primer_nombre, $segundo_nombre, $apellido_paterno, $apellido_materno]);
    $nombre = implode(' ', $parts);

    try {
        $pdo->prepare(
            'UPDATE usuarios SET nombre = ?, primer_nombre = ?, segundo_nombre = ?, apellido_paterno = ?, apellido_materno = ?, email_contacto = ?, telefono = ? WHERE id = ?'
        )->execute([$nombre, $primer_nombre, $segundo_nombre, $apellido_paterno, $apellido_materno, $email_contacto, $telefono, $user['id']]);
    } catch (PDOException $e) {
        // Columns not migrated yet — update only nombre
        $pdo->prepare('UPDATE usuarios SET nombre = ? WHERE id = ?')->execute([$nombre, $user['id']]);
    }

    echo json_encode(['ok' => true, 'msg' => 'Perfil actualizado', 'nombre' => $nombre]);

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

    try { $stmt = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1'); $stmt->execute([$user['id']]); } catch (PDOException $e) { echo json_encode(['ok' => false, 'msg' => 'Columna foto no existe aún. Ejecuta la migración de base de datos.']); exit; }
    $oldFoto = $stmt->fetchColumn();

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        echo json_encode(['ok' => false, 'msg' => 'Error al guardar la imagen']);
        exit;
    }

    $path = 'assets/avatars/' . $filename;
    $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?')->execute([$path, $user['id']]);

    // Solo borrar el avatar viejo una vez que el nuevo quedó guardado y la BD actualizada
    if ($oldFoto) {
        $oldFile = __DIR__ . '/../' . ltrim($oldFoto, '/');
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

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

    // Cerrar todas las otras sesiones activas del usuario
    Auth::invalidarOtrasSesiones($user['id']);

    echo json_encode(['ok' => true, 'msg' => 'Contraseña cambiada correctamente']);

} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Acción no reconocida']);
}
