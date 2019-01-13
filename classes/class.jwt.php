<?php
class JWT implements InterfaceConfig{
    use Api;
    private $protected = true;
    public static $leeway = 0;

    public static $timestamp = null;
    public static $supported_algs = array(
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
    );

    public function get_protected(){
      return $this->protected;
    }

    public static function decode($jwt, $key, array $allowed_algs = array()){

        $timestamp = is_null(static::$timestamp) ? time() : static::$timestamp;
        if (empty($key)){
          self::throwError(403, 'Token está vazio', 'ACCESS_TOKEN_ERRORS');
        }
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
          self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
          self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
        }
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
          self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
        }
        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
          self::throwError(403, 'Assinatura do token não confere', 'ACCESS_TOKEN_ERRORS');
        }
        if (empty($header->alg)) {
          self::throwError(403, 'Não especifica o algoritmo no header do token', 'ACCESS_TOKEN_ERRORS');
        }
        if (empty(static::$supported_algs[$header->alg])) {
          self::throwError(500, 'Algoritmo especificado não é suportado', 'ACCESS_TOKEN_ERRORS');
        }
        if (!in_array($header->alg, $allowed_algs)) {
          self::throwError(403, 'Algoritmo JWT é bloqueado', 'ACCESS_TOKEN_ERRORS');
        }
        if (is_array($key) || $key instanceof \ArrayAccess) {
            if (isset($header->kid)) {
                if (!isset($key[$header->kid])) {
                  self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
                }
                $key = $key[$header->kid];
            } else {
              self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
            }
        }

        if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
          self::throwError(403, 'Erro na assinatura do token', 'ACCESS_TOKEN_ERRORS');
        }

        if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
          self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
        }

        if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
          self::throwError(403, 'Token invalido', 'ACCESS_TOKEN_ERRORS');
        }

        if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
          self::throwError(401, 'Token expirou', 'ACCESS_TOKEN_ERRORS');
        }
        return $payload;
    }

    public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
    {
        $header = array('typ' => 'JWT', 'alg' => $alg);
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        if ( isset($head) && is_array($head) ) {
            $header = array_merge($head, $header);
        }
        $segments = array();
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }

    public static function sign($msg, $key, $alg = 'HS256')
    {
        if (empty(static::$supported_algs[$alg])) {
          self::throwError(500, 'Algoritmo não suportado', 'ACCESS_TOKEN_ERRORS');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($msg, $signature, $key, $algorithm);
                if (!$success) {
                  self::throwError(500, 'OpenSSL unable to sign data', 'ACCESS_TOKEN_ERRORS');
                } else {
                    return $signature;
                }
        }
    }

    private static function verify($msg, $signature, $key, $alg)
    {
        if (empty(static::$supported_algs[$alg])) {
            self::throwError(500, 'Open SSL error', 'ACCESS_TOKEN_ERRORS');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'openssl':
                $success = openssl_verify($msg, $signature, $key, $algorithm);
                if ($success === 1) {
                    return true;
                } elseif ($success === 0) {
                    return false;
                }
                self::throwError(500, 'Open SSL error', 'ACCESS_TOKEN_ERRORS');
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $msg, $key, true);
                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hash);
                }
                $len = min(static::safeStrlen($signature), static::safeStrlen($hash));
                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
                }
                $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));
                return ($status === 0);
        }
    }

    public static function jsonDecode($input)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
          self::throwError(500, 'Null result with non-null input', 'ACCESS_TOKEN_ERRORS');
        }
        return $obj;
    }

    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
          self::throwError(500, 'Null result with non-null input', 'ACCESS_TOKEN_ERRORS');
        }
        return $json;
    }

    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function handleJsonError($errno)
    {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
        );
       self::throwError(500, 'Erro', 'ACCESS_TOKEN_ERRORS');
    }

    private static function safeStrlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}
?>
