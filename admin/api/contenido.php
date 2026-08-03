<?php
ini_set('display_errors', 0);   // evita que warnings contaminen el JSON

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/contenido.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Valida el tipo real del archivo (no solo la extensión que mandó el navegador)
// y devuelve la extensión a usar, o null si no es un formato permitido.
// Los .docx/.pptx son en realidad ZIP — algunos servidores los detectan como
// application/zip genérico, así que en ese caso se revisa el contenido interno
// del ZIP para confirmar que es un documento de Office real y no un ZIP cualquiera.
function documentoExtension(string $tmpPath, string $originalName): ?string
{
    $mime = mime_content_type($tmpPath);

    $directos = [
        'application/pdf'       => 'pdf',
        'application/msword'    => 'doc',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];
    if (isset($directos[$mime])) {
        return $directos[$mime];
    }

    // Fallback para docx/pptx mal detectados como zip genérico
    if ($mime === 'application/zip' && class_exists('ZipArchive')) {
        $extPedida = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extPedida, ['docx', 'pptx'], true)) {
            return null;
        }
        $zip = new ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            return null;
        }
        $marcador = $extPedida === 'docx' ? 'word/document.xml' : 'ppt/presentation.xml';
        $esValido = $zip->locateName($marcador) !== false;
        $zip->close();
        return $esValido ? $extPedida : null;
    }

    return null;
}

function deleteOldAsset(string $rawPath, array $allowedDirs): void
{
    $rawPath = trim($rawPath);
    if ($rawPath === '') {
        return;
    }
    $base = realpath(__DIR__ . '/../../');
    $abs  = realpath($base . '/' . ltrim($rawPath, '/'));
    if (!$abs || strpos($abs, $base . DIRECTORY_SEPARATOR) !== 0) {
        return;
    }
    foreach ($allowedDirs as $dir) {
        $dir = realpath($dir);
        if ($dir && strpos($abs, $dir . DIRECTORY_SEPARATOR) === 0) {
            @unlink($abs);
            return;
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// Verificar CSRF en escrituras
if ($method === 'POST' && !Auth::csrfVerify()) {
    http_response_code(403);
    echo json_encode(['error' => 'Solicitud no autorizada']);
    exit;
}

if ($method === 'GET') {
    $clave = $_GET['clave'] ?? '';
    echo $clave
        ? json_encode(Contenido::get($clave), JSON_UNESCAPED_UNICODE)
        : json_encode(Contenido::getAll(), JSON_UNESCAPED_UNICODE);

} elseif ($method === 'POST' && !empty($_FILES['video'])) {
    $file    = $_FILES['video'];
    $allowed = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v'];
    $mime    = mime_content_type($file['tmp_name']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = match($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_PARTIAL  => 'La carga fue interrumpida, intenta de nuevo',
            UPLOAD_ERR_NO_FILE  => 'No se recibió ningún archivo',
            default             => 'Error al recibir el archivo (código ' . $file['error'] . ')',
        };
        http_response_code(400);
        echo json_encode(['error' => $msg]);
        exit;
    }
    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato no permitido. Usa MP4, WebM u OGG.']);
        exit;
    }
    if ($file['size'] > 200 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'El video excede el límite de 200 MB']);
        exit;
    }

    $ext = match($mime) {
        'video/mp4', 'video/x-m4v', 'video/quicktime' => 'mp4',
        'video/webm'                                    => 'webm',
        'video/ogg'                                     => 'ogv',
        default                                         => 'mp4',
    };

    $filename  = 'hero_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../../assets/videos/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el video en el servidor']);
        exit;
    }

    deleteOldAsset($_POST['oldPath'] ?? '', [__DIR__ . '/../../assets/videos']);

    echo json_encode([
        'ok'   => true,
        'path' => 'assets/videos/' . $filename,
        'size' => $file['size'],
        'mime' => $mime,
        'name' => $filename,
    ]);

} elseif ($method === 'POST' && !empty($_FILES['documento'])) {
    $file = $_FILES['documento'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = match($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_PARTIAL  => 'La carga fue interrumpida, intenta de nuevo',
            UPLOAD_ERR_NO_FILE  => 'No se recibió ningún archivo',
            default             => 'Error al recibir el archivo',
        };
        http_response_code(400);
        echo json_encode(['error' => $msg]);
        exit;
    }
    if ($file['size'] > 15 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'El archivo excede el límite de 15 MB']);
        exit;
    }

    $ext = documentoExtension($file['tmp_name'], $file['name']);
    if (!$ext) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato no permitido. Solo se aceptan PDF, Word (.doc/.docx) o PowerPoint (.ppt/.pptx).']);
        exit;
    }

    $filename  = 'ficha_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../../assets/docs/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el archivo']);
        exit;
    }

    deleteOldAsset($_POST['oldPath'] ?? '', [__DIR__ . '/../../assets/docs']);

    echo json_encode(['ok' => true, 'path' => 'assets/docs/' . $filename]);

} elseif ($method === 'POST' && ($_POST['action'] ?? '') === 'eliminar_documento') {
    deleteOldAsset($_POST['path'] ?? '', [__DIR__ . '/../../assets/docs']);
    echo json_encode(['ok' => true]);

} elseif ($method === 'POST' && !empty($_FILES['image'])) {
    $file    = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    $mime    = mime_content_type($file['tmp_name']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = match($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo (5 MB)',
            UPLOAD_ERR_PARTIAL  => 'La carga fue interrumpida, intenta de nuevo',
            UPLOAD_ERR_NO_FILE  => 'No se recibió ningún archivo',
            default             => 'Error al recibir el archivo',
        };
        http_response_code(400);
        echo json_encode(['error' => $msg]);
        exit;
    }
    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato no permitido (JPG, PNG, WebP, GIF)']);
        exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Máximo 5 MB']);
        exit;
    }

    $ext = match($mime) {
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/gif'     => 'gif',
        'image/svg+xml' => 'svg',
    };

    $allowedFolders = ['general', 'partners'];
    $folder = in_array($_POST['folder'] ?? '', $allowedFolders, true) ? $_POST['folder'] : 'general';
    $prefix    = $folder === 'partners' ? 'logo_' : 'slide_';
    $filename  = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../../assets/images/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la imagen']);
        exit;
    }

    deleteOldAsset($_POST['oldPath'] ?? '', [
        __DIR__ . '/../../assets/images/general',
        __DIR__ . '/../../assets/images/partners',
    ]);

    echo json_encode(['ok' => true, 'path' => 'assets/images/' . $folder . '/' . $filename]);

} elseif ($method === 'POST') {
    $body  = json_decode(file_get_contents('php://input'), true);
    $clave = $body['clave'] ?? '';
    $valor = $body['valor'] ?? null;

    if (!$clave || $valor === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan parámetros']);
        exit;
    }

    $limitesCantidad = [
        'marcas'     => 20,
        'partners'   => 12,
        'industrias' => 15,
        'servicios'  => 20,
        'proyectos'  => 40,
    ];
    if (isset($limitesCantidad[$clave]) && is_array($valor) && count($valor) > $limitesCantidad[$clave]) {
        http_response_code(400);
        echo json_encode(['error' => "Máximo {$limitesCantidad[$clave]} elementos permitidos"]);
        exit;
    }

    if ($clave === 'contacto' && is_array($valor) && !empty($valor['email']) && !filter_var($valor['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email de contacto no válido']);
        exit;
    }

    $maxFestivos = 30;
    if ($clave === 'contacto' && is_array($valor) && isset($valor['diasFestivos']) && is_array($valor['diasFestivos'])) {
        if (count($valor['diasFestivos']) > $maxFestivos) {
            http_response_code(400);
            echo json_encode(['error' => "Máximo {$maxFestivos} fechas permitidas"]);
            exit;
        }
        foreach ($valor['diasFestivos'] as $f) {
            $dia = $f['dia'] ?? null;
            $mes = $f['mes'] ?? null;
            if (!is_numeric($dia) || !is_numeric($mes) || $dia < 1 || $dia > 31 || $mes < 1 || $mes > 12) {
                http_response_code(400);
                echo json_encode(['error' => 'Día festivo con fecha inválida']);
                exit;
            }
        }
    }

    $clavesMaquinas = ['ensamble', 'maqcontrol', 'maqprob', 'maqinspe', 'maclim', 'maqmar', 'macrobot', 'maqindus'];
    $limiteTabs     = 10;
    if (in_array($clave, $clavesMaquinas, true) && is_array($valor) && isset($valor['tabs']) && is_array($valor['tabs']) && count($valor['tabs']) > $limiteTabs) {
        http_response_code(400);
        echo json_encode(['error' => "Máximo {$limiteTabs} pestañas permitidas"]);
        exit;
    }

    Contenido::set($clave, $valor);
    echo json_encode(['ok' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
