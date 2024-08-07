<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

/**
 * Implements JWT encoding and decoding as per http://tools.ietf.org/html/draft-ietf-oauth-json-web-token-06
 * Encoding algorithm based on http://code.google.com/p/google-api-php-client
 * Decoding algorithm based on https://github.com/luciferous/jwt
 * @author Francis Chuang <francis.chuang@gmail.com>
 */
class JWT
{
    public static function encode($payload, $key, $algo = 'HS256')
    {
        $crypt =  & load_class('Crypt');
        $header = array('typ' => 'JWT', 'alg' => $algo);
        $segments = array(
            JWT::urlsafeB64Encode(json_encode($header)),
            JWT::urlsafeB64Encode(json_encode($payload)),
        );
        $signing_input = implode('.', $segments);
        $signature = JWT::sign($signing_input, $key, $algo);
        $segments[] = JWT::urlsafeB64Encode($signature);
        return $crypt->acm_rand_encrypt(implode('.', $segments), secret_key);
    }
    public static function decode($jwt, $key = null, $algo = 'HS256')
    {
        $crypt =  & load_class('Crypt');

        $jwt = $crypt->acm_rand_decrypt($jwt, secret_key);
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            show_error("Authentication", "JWT wrong number of segments");
        }
        list($headb64, $payloadb64, $cryptob64) = $tks;
        if (null === ($header = json_decode(JWT::urlsafeB64Decode($headb64)))) {
            show_error("Authentication", "JWT invalid segment encoding");
        }
        if (null === $payload = json_decode(JWT::urlsafeB64Decode($payloadb64))) {
            show_error("Authentication", "JWT invalid segment encoding");
        }
        $sig = JWT::urlsafeB64Decode($cryptob64);
        if (isset($key)) {
            if (empty($header->alg)) {
                show_error("Authentication", "JWT empty algorithm");
            }
            if (!JWT::verifySignature($sig, "$headb64.$payloadb64", $key, $algo)) {
                show_error("Authentication", "JWT signature verification failed");
            }
        }
        return $payload;
    }
    private static function verifySignature($signature, $input, $key, $algo)
    {
        switch ($algo) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                return JWT::sign($input, $key, $algo) === $signature;
            case 'RS256':
                return (boolean) openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA256);
            case 'RS384':
                return (boolean) openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA384);
            case 'RS512':
                return (boolean) openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA512);
            default:
                show_error("Authentication", "JWT unsupported or invalid signing algorithm.");
        }
    }
    private static function sign($input, $key, $algo)
    {
        switch ($algo) {
            case 'HS256':
                return hash_hmac('sha256', $input, $key, true);
            case 'HS384':
                return hash_hmac('sha384', $input, $key, true);
            case 'HS512':
                return hash_hmac('sha512', $input, $key, true);
            case 'RS256':
                return JWT::generateRSASignature($input, $key, OPENSSL_ALGO_SHA256);
            case 'RS384':
                return JWT::generateRSASignature($input, $key, OPENSSL_ALGO_SHA384);
            case 'RS512':
                return JWT::generateRSASignature($input, $key, OPENSSL_ALGO_SHA512);
            default:
                show_error("Authentication", "JWT unsupported or invalid signing algorithm.");
        }
    }
    private static function generateRSASignature($input, $key, $algo)
    {
        if (!openssl_sign($input, $signature, $key, $algo)) {
            show_error("Authentication", "JWT unable to sign data.");
        }
        return $signature;
    }
    private static function urlSafeB64Encode($data)
    {
        $b64 = base64_encode($data);
        $b64 = str_replace(array('+', '/', '\r', '\n', '='),
            array('-', '_'),
            $b64);
        return $b64;
    }
    private static function urlSafeB64Decode($b64)
    {
        $b64 = str_replace(array('-', '_'),
            array('+', '/'),
            $b64);
        return base64_decode($b64);
    }
}
