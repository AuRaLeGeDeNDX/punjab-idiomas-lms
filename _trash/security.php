<?php
/**
 * Punjab Idiomas - Security Module
 * Comprehensive security functions and protections
 */

/**
 * CSRF Token Management
 */
class CSRFToken {
    private static $token_name = '_csrf_token';
    private static $token_lifetime = 3600; // 1 hour

    public static function generate() {
        if (empty($_SESSION[self::$token_name]) || self::isExpired()) {
            $_SESSION[self::$token_name] = bin2hex(random_bytes(32));
            $_SESSION[self::$token_name . '_time'] = time();
        }
        return $_SESSION[self::$token_name];
    }

    public static function getToken() {
        return self::generate();
    }

    public static function verify($token) {
        if (empty($_SESSION[self::$token_name])) {
            return false;
        }

        if (self::isExpired()) {
            unset($_SESSION[self::$token_name]);
            unset($_SESSION[self::$token_name . '_time']);
            return false;
        }

        return hash_equals($_SESSION[self::$token_name], $token ?? '');
    }

    private static function isExpired() {
        return !isset($_SESSION[self::$token_name . '_time']) ||
               (time() - $_SESSION[self::$token_name . '_time']) > self::$token_lifetime;
    }
}

/**
 * Input Validation & Sanitization
 */
class InputValidator {
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function phone($phone) {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9+\-() ]/', '', $phone);
        return !empty($cleaned) && strlen($cleaned) >= 9;
    }

    public static function text($text, $max_length = 500) {
        $text = trim($text);
        return strlen($text) > 0 && strlen($text) <= $max_length;
    }

    public static function password($password) {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }

    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function name($name) {
        // Allow letters, spaces, hyphens, and basic accents
        return preg_match('/^[a-zA-Z\s\-áéíóúñÁÉÍÓÚÑ]{2,100}$/', $name);
    }
}

/**
 * Rate Limiting
 */
class RateLimiter {
    private static $storage_file = null;

    public static function init() {
        self::$storage_file = sys_get_temp_dir() . '/punjab_rate_limits.json';
    }

    public static function check($identifier, $limit = 10, $window = 3600) {
        if (self::$storage_file === null) {
            self::init();
        }

        $attempts = self::getAttempts($identifier);
        $current_window = floor(time() / $window);

        // Clean old entries
        $attempts = array_filter($attempts, function($attempt) use ($window) {
            return (time() - $attempt['time']) < $window;
        });

        if (count($attempts) >= $limit) {
            return false;
        }

        $attempts[] = ['time' => time()];
        self::saveAttempts($identifier, $attempts);
        return true;
    }

    private static function getAttempts($identifier) {
        if (!file_exists(self::$storage_file)) {
            return [];
        }

        $data = json_decode(file_get_contents(self::$storage_file), true) ?? [];
        return $data[$identifier] ?? [];
    }

    private static function saveAttempts($identifier, $attempts) {
        $data = [];
        if (file_exists(self::$storage_file)) {
            $data = json_decode(file_get_contents(self::$storage_file), true) ?? [];
        }
        $data[$identifier] = $attempts;
        file_put_contents(self::$storage_file, json_encode($data), LOCK_EX);
    }
}

/**
 * Security Headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; frame-src 'self' https://www.google.com;");

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Force HTTPS (if not local)
    if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:8000'])) {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Secure Cookie Settings
 * (Configured in index.php before session_start)
 */

/**
 * File Upload Validation
 */
class FileValidator {
    private static $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private static $max_size = 5242880; // 5MB

    public static function validate($file) {
        if (!isset($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No file provided'];
        }

        // Check file size
        if ($file['size'] > self::$max_size) {
            return ['valid' => false, 'error' => 'File too large (max 5MB)'];
        }

        // Check MIME type
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::$allowed_mime)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }

        // Check if file is actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid file upload'];
        }

        return ['valid' => true];
    }

    public static function sanitizeFilename($filename) {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        // Prevent directory traversal
        $filename = str_replace(['..', '/', '\\'], '', $filename);
        return $filename;
    }
}

/**
 * XSS Protection - Output Encoding
 */
function safe_output($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * SQL Injection Prevention - Parameter Binding Example
 */
class Database {
    private static $connection = null;

    public static function connect($host, $user, $password, $database) {
        try {
            self::$connection = new PDO(
                "mysql:host=$host;dbname=$database;charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            return true;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            return false;
        }
    }

    public static function query($sql, $params = []) {
        try {
            $stmt = self::$connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query error: ' . $e->getMessage());
            return false;
        }
    }

    public static function insert($table, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        return self::query($sql, $values);
    }
}

/**
 * Logging Security Events
 */
class SecurityLogger {
    private static $log_file = null;

    public static function init() {
        self::$log_file = APP_ROOT . '/logs/security.log';
        if (!is_dir(dirname(self::$log_file))) {
            mkdir(dirname(self::$log_file), 0750, true);
        }
    }

    public static function log($event, $level = 'INFO') {
        if (self::$log_file === null) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_entry = "[$timestamp] [$level] [$ip] $event\n";

        file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    public static function logFailedLogin($email) {
        self::log("Failed login attempt for: $email", 'WARNING');
    }

    public static function logSuspiciousActivity($description) {
        self::log("Suspicious activity: $description", 'CRITICAL');
    }
}

/**
 * Initialize all security measures
 */
function initializeSecurityModule() {
    // Set security headers
    setSecurityHeaders();

    // Initialize rate limiter
    RateLimiter::init();

    // Initialize security logger
    SecurityLogger::init();

    // Log session start
    if (empty($_SESSION['_logged'])) {
        SecurityLogger::log("New session started from {$_SERVER['REMOTE_ADDR']}", 'INFO');
        $_SESSION['_logged'] = true;
    }
}

// Auto-initialize security module
initializeSecurityModule();
?>
