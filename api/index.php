<?php

/**
 * OZNA PWA REST API — Single entry point / router.
 *
 * All requests to /api/* are rewritten here by .htaccess.
 * Routes are dispatched to controller methods with optional JWT auth.
 */

// ── Bootstrap ───────────────────────────────────────────────

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/api_error.log');

mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=utf-8');

// CORS — same-origin in production, permissive for dev
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Autoloader ──────────────────────────────────────────────

spl_autoload_register(function (string $class) {
    // OznaApi\Services\JwtService  → api/services/JwtService.php
    // OznaApi\Controllers\AuthController → api/controllers/AuthController.php
    // OznaApi\Middleware\AuthMiddleware  → api/middleware/AuthMiddleware.php
    if (strpos($class, 'OznaApi\\') === 0) {
        $relative = str_replace('OznaApi\\', '', $class);
        $path = __DIR__ . '/' . strtolower(dirname(str_replace('\\', '/', $relative)))
              . '/' . basename(str_replace('\\', '/', $relative)) . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }

    // Telegram bot classes (shared services)
    // Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService
    $botRoot = dirname(__DIR__);
    if (strpos($class, 'Longman\\TelegramBot\\Commands\\SystemCommands\\') === 0) {
        $relative = str_replace('Longman\\TelegramBot\\Commands\\SystemCommands\\', '', $class);
        $path = $botRoot . '/CustomCommands/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load Composer autoloader for the bot (needed for Telegram Request class)
$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// ── Config & Services ───────────────────────────────────────

$config = require __DIR__ . '/config.php';

$jwt      = new OznaApi\Services\JwtService($config['jwt']);
$authMw   = new OznaApi\Middleware\AuthMiddleware($jwt);
$password = new OznaApi\Services\PasswordService();

// Lazy DB connection (shared with bot)
$db = null;
function getDb(): \mysqli {
    global $db, $config;
    if ($db === null) {
        $c = $config['db'];
        $db = new \mysqli($c['host'], $c['username'], $c['password'], $c['database']);
        if ($db->connect_error) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
        $db->set_charset('utf8mb4');
        $db->autocommit(true);
    }
    return $db;
}

// ── Routing ─────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Strip query string and /api prefix
$path = parse_url($uri, PHP_URL_PATH);
$path = preg_replace('#^.*/api#', '', $path);  // handle /tg/ozna/api/... or /api/...
$path = rtrim($path, '/') ?: '/';

// Parse JSON body for POST/PUT
$body = [];
if (in_array($method, ['POST', 'PUT'])) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
}

try {
    // ── Public routes ───────────────────────────────────────
    if ($method === 'POST' && $path === '/auth/login') {
        require_once __DIR__ . '/controllers/AuthController.php';
        (new OznaApi\Controllers\AuthController($jwt, $password))->login($body);
        exit;
    }

    if ($method === 'POST' && $path === '/auth/refresh') {
        require_once __DIR__ . '/controllers/AuthController.php';
        (new OznaApi\Controllers\AuthController($jwt, $password))->refresh($body);
        exit;
    }

    // ── Health check ────────────────────────────────────────
    if ($method === 'GET' && $path === '/health') {
        echo json_encode(['status' => 'ok', 'time' => date('c')]);
        exit;
    }

    // ── Protected routes (require JWT) ──────────────────────
    if (!$authMw->handle()) {
        exit;
    }

    // Store auth middleware in globals for controllers that need access level checks
    $GLOBALS['auth_middleware'] = $authMw;

    // Profile
    if ($method === 'GET' && $path === '/profile') {
        require_once __DIR__ . '/controllers/ProfileController.php';
        (new OznaApi\Controllers\ProfileController())->get();
        exit;
    }
    if ($method === 'PUT' && $path === '/profile/password') {
        require_once __DIR__ . '/controllers/ProfileController.php';
        (new OznaApi\Controllers\ProfileController())->changePassword($body, $password);
        exit;
    }
    if ($method === 'PUT' && $path === '/profile/location') {
        require_once __DIR__ . '/controllers/ProfileController.php';
        (new OznaApi\Controllers\ProfileController())->toggleLocation($body);
        exit;
    }

    // Tabel
    if ($method === 'GET' && $path === '/tabel/activity-types') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->activityTypes();
        exit;
    }
    if ($method === 'GET' && $path === '/tabel/projects') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->projects();
        exit;
    }
    if ($method === 'POST' && $path === '/tabel') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->create($body);
        exit;
    }

    // Claims (warranty)
    if ($method === 'GET' && preg_match('#^/tabel/claims$#', $path)) {
        $projectCode = $_GET['project_code'] ?? '';
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->getClaims($projectCode);
        exit;
    }
    if ($method === 'POST' && preg_match('#^/tabel/claims/(\d+)/close$#', $path, $m)) {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->closeClaim((int)$m[1], $body);
        exit;
    }
    if ($method === 'POST' && $path === '/tabel/claims') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->createClaim($body);
        exit;
    }
    if ($method === 'POST' && $path === '/tabel/absence') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->createAbsence($body);
        exit;
    }

    // Milestone surveys
    if ($method === 'GET' && $path === '/tabel/milestone-survey') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->getMilestoneSurvey($_GET);
        exit;
    }
    if ($method === 'POST' && $path === '/tabel/milestone-estimate') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->saveMilestoneEstimate($body);
        exit;
    }
    if ($method === 'GET' && $path === '/tabel/milestone-alerts') {
        require_once __DIR__ . '/controllers/TabelController.php';
        (new OznaApi\Controllers\TabelController())->getMilestoneAlerts();
        exit;
    }

    // Location (employee map)
    if ($method === 'POST' && $path === '/location') {
        require_once __DIR__ . '/controllers/LocationController.php';
        (new OznaApi\Controllers\LocationController())->save($body);
        exit;
    }
    if ($method === 'GET' && $path === '/locations') {
        require_once __DIR__ . '/controllers/LocationController.php';
        (new OznaApi\Controllers\LocationController())->getAll();
        exit;
    }

    // Dashboard exports (superadmin only)
    if ($method === 'GET' && $path === '/dashboard/exports') {
        require_once __DIR__ . '/controllers/DashboardExportController.php';
        (new OznaApi\Controllers\DashboardExportController())->listExports();
        exit;
    }
    if ($method === 'GET' && $path === '/dashboard/exports/high-level-status') {
        require_once __DIR__ . '/controllers/DashboardExportController.php';
        (new OznaApi\Controllers\DashboardExportController())->highLevelStatus();
        exit;
    }

    // Admin user management (superadmin only)
    if ($method === 'GET' && $path === '/admin/users') {
        require_once __DIR__ . '/controllers/AdminController.php';
        (new OznaApi\Controllers\AdminController($password))->listUsers();
        exit;
    }
    if ($method === 'PUT' && preg_match('#^/admin/users/(\d+)/reset-password$#', $path, $m)) {
        require_once __DIR__ . '/controllers/AdminController.php';
        (new OznaApi\Controllers\AdminController($password))->resetPassword((int) $m[1], $body);
        exit;
    }

    // ── Metrics (supervisor+ : access_level >= 2) ───────────
    require_once __DIR__ . '/services/MetricsCacheService.php';
    require_once __DIR__ . '/controllers/MetricsController.php';
    $metricsCtrl = new OznaApi\Controllers\MetricsController();

    if ($method === 'GET' && $path === '/metrics/bn-summary') {
        $metricsCtrl->bnSummary();
        exit;
    }
    if ($method === 'GET' && $path === '/metrics/projects') {
        $metricsCtrl->projects($_GET['group'] ?? '');
        exit;
    }
    if ($method === 'POST' && $path === '/metrics/favorites') {
        $metricsCtrl->toggleFavorite($body);
        exit;
    }
    // Lookup creation endpoints (fillable lists)
    if ($method === 'POST' && $path === '/metrics/lookups/customer') {
        $metricsCtrl->createCustomer($body);
        exit;
    }
    if ($method === 'POST' && $path === '/metrics/lookups/business-unit') {
        $metricsCtrl->createBusinessUnit($body);
        exit;
    }
    if ($method === 'POST' && $path === '/metrics/lookups/manager') {
        $metricsCtrl->createManager($body);
        exit;
    }
    // Milestones Confirmation
    if ($method === 'POST' && $path === '/metrics/milestone-confirm') {
        $metricsCtrl->confirmMilestone($body);
        exit;
    }
    if ($method === 'POST' && $path === '/metrics/milestone-unconfirm') {
        $metricsCtrl->unconfirmMilestone($body);
        exit;
    }
    // Milestones CRUD — MUST come before /project-card/{code}
    if (preg_match('#^/metrics/milestones/([^/]+)/(.+)$#u', $path, $m)) {
        $code = urldecode($m[1]);
        $wt = urldecode($m[2]);
        if ($method === 'GET') {
            $metricsCtrl->milestones($code, $wt);
        } elseif ($method === 'PUT') {
            $metricsCtrl->updateMilestones($code, $wt, $body);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        exit;
    }
    // /metrics/project-card/{code}/process/{wt} — MUST come before /project-card/{code}
    if (($method === 'GET' || $method === 'PUT') && preg_match('#^/metrics/project-card/(.+)/process/(.+)$#u', $path, $m)) {
        if ($method === 'PUT') {
            $metricsCtrl->updateProcessCard(urldecode($m[1]), urldecode($m[2]), $body);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        exit;
    }
    if (preg_match('#^/metrics/project-card/(.+)$#u', $path, $m)) {
        $code = urldecode($m[1]);
        if ($method === 'GET') {
            $metricsCtrl->projectCard($code);
        } elseif ($method === 'PUT') {
            $metricsCtrl->updateProjectCard($code, $body);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        exit;
    }
    if ($method === 'GET' && preg_match('#^/metrics/project/(.+)$#u', $path, $m)) {
        $metricsCtrl->project(urldecode($m[1]));
        exit;
    }

    // ── 404 ─────────────────────────────────────────────────
    http_response_code(404);
    echo json_encode(['error' => 'Not found', 'path' => $path]);

} catch (\Throwable $e) {
    error_log("API Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
