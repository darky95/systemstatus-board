<?php
require_once __DIR__ . '/../config.php';

class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateStatus($status) {
        $validStatuses = [STATUS_NORMAL, STATUS_EINGESCHRAENKT, STATUS_GESTOERT, STATUS_WARTUNG];
        return in_array($status, $validStatuses);
    }
    
    public static function generateDeviceKey() {
        return bin2hex(random_bytes(16));
    }
    
    public static function generateApiKey() {
        return bin2hex(random_bytes(32));
    }
    
    public static function validateApiKey($apiKey) {
        $db = new Database();
        $key = $db->fetchOne(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
            [$apiKey]
        );
        return $key !== false;
    }
}