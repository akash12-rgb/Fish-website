<?php
// ============================================================
//  config/database.php  –  PostgreSQL connection
// ============================================================
define('DB_HOST', 'ep-square-darkness-adnakq1q-pooler.c-2.us-east-1.aws.neon.tech');
define('DB_PORT', '5432');
define('DB_NAME', 'neondb');
define('DB_USER', 'neondb_owner');
define('DB_PASSWORD', 'npg_6ZvAOxSK7UGr');

// App settings
define('APP_NAME',    'Sunbis AgroFish');
define('APP_URL',     getenv('APP_URL') ?: 'https://fish-website.onrender.com');
define('UPLOAD_DIR',  __DIR__ . '/../public/uploads/products/');
define('UPLOAD_URL',  APP_URL . '/public/uploads/products/');

// Session start (called once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── PDO Connection ─────────────────────────────────────────
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
       $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

// ─── Helper: JSON response ───────────────────────────────────
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ─── Helper: current user ────────────────────────────────────
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void
{
    $u = currentUser();
    echo $u;
    if (!$u || $u['role'] !== 'admin') {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

// ─── Helper: CSRF ────────────────────────────────────────────
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(string $token): bool
{
    return hash_equals($_SESSION['csrf'] ?? '', $token);
}
