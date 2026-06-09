<?php
require_once __DIR__ . '/conexion.php';

class Contenido {

    public static function get(string $clave): mixed {
        global $pdo;
        $stmt = $pdo->prepare('SELECT valor FROM contenido WHERE clave = ? LIMIT 1');
        $stmt->execute([$clave]);
        $row = $stmt->fetch();
        return $row ? json_decode($row['valor'], true) : null;
    }

    public static function set(string $clave, mixed $valor): void {
        global $pdo;
        $pdo->prepare(
            'INSERT INTO contenido (clave, valor)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor), actualizado_en = NOW()'
        )->execute([$clave, json_encode($valor, JSON_UNESCAPED_UNICODE)]);
    }

    public static function getAll(): array {
        global $pdo;
        $result = [];
        foreach ($pdo->query('SELECT clave, valor FROM contenido') as $row) {
            $result[$row['clave']] = json_decode($row['valor'], true);
        }
        return $result;
    }
}
