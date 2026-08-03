<?php
// TOTP (RFC 6238) — compatible con Google Authenticator, Authy, etc.
// Sin dependencias externas: solo hash_hmac + base32 a mano.
class Totp {

    private const PERIODO = 30;
    private const DIGITOS = 6;

    public static function generarSecreto(int $bytes = 20): string {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function otpauthUri(string $secreto, string $cuenta, string $emisor = 'DEMATIQ'): string {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($emisor), rawurlencode($cuenta), $secreto, rawurlencode($emisor), self::DIGITOS, self::PERIODO
        );
    }

    // Ventana de ±1 paso (30s) para tolerar el desfase de reloj típico
    // entre el teléfono del usuario y el servidor.
    public static function verificar(string $secreto, string $codigo, int $ventana = 1): bool {
        $codigo = preg_replace('/\s+/', '', (string) $codigo);
        if (!preg_match('/^\d{6}$/', $codigo)) return false;

        $clave = self::base32Decode($secreto);
        if ($clave === false || $clave === '') return false;

        $paso = (int) floor(time() / self::PERIODO);
        for ($i = -$ventana; $i <= $ventana; $i++) {
            if (hash_equals(self::codigoParaPaso($clave, $paso + $i), $codigo)) {
                return true;
            }
        }
        return false;
    }

    private static function codigoParaPaso(string $claveBinaria, int $paso): string {
        $bin    = pack('N2', 0, $paso); // contador de 8 bytes, big-endian
        $hash   = hash_hmac('sha1', $bin, $claveBinaria, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $valor  = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITOS);
        return str_pad((string) $valor, self::DIGITOS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string {
        $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($data) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $salida = '';
        foreach (str_split($bits, 5) as $grupo) {
            $grupo   = str_pad($grupo, 5, '0', STR_PAD_RIGHT);
            $salida .= $alfabeto[bindec($grupo)];
        }
        return $salida;
    }

    private static function base32Decode(string $secreto) {
        $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secreto  = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $secreto));
        $bits = '';
        foreach (str_split($secreto) as $char) {
            $pos = strpos($alfabeto, $char);
            if ($pos === false) return false;
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $bytes .= chr(bindec($byte));
        }
        return $bytes;
    }
}
