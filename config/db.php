<?php
/**
 * Configuration Base de Données - RMC BTP SARL
 * Compatible :
 * 1. Production Officielle HOSTINGER (rmcbtpsarl.com)
 * 2. Développement Local XAMPP (Windows)
 * 3. Environnement de Test InfinityFree
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir le fuseau horaire du Cameroun (UTC+1)
date_default_timezone_set('Africa/Douala');

define('SITE_NAME', 'RMC BTP SARL');
define('SITE_SLOGAN', 'Construire aujourd\'hui les solutions de demain');
define('ADMIN_EMAIL', 'info@rmcbtp.com');

// Détection de l'environnement
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$is_local = (
    (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) &&
    PHP_OS_FAMILY === 'Windows' && file_exists('C:/xampp')
);

$is_infinityfree = (
    strpos($host, 'infinityfree') !== false ||
    strpos($host, 'free.nf') !== false ||
    strpos($host, 'gt.tc') !== false ||
    strpos($host, 'epizy.com') !== false
);

// -----------------------------------------------------------------------------
// IDENTIFIANTS OFFICIELS DE CONNEXION
// -----------------------------------------------------------------------------
if (!defined('DB_HOST')) {
    if ($is_local) {
        // 1. Environnement Local XAMPP
        define('DB_HOST', '127.0.0.1');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'rmcbtp_db');
        define('SITE_URL', 'http://localhost/rmcbtpsarl');
    } elseif ($is_infinityfree) {
        // 2. Environnement de Test InfinityFree
        define('DB_HOST', 'sql113.infinityfree.com');
        define('DB_USER', 'if0_39882531');
        define('DB_PASS', 'cmTJtR2Z2yq8MO');
        define('DB_NAME', 'if0_39882531_rmcbtp');
        define('SITE_URL', 'http://' . $host);
    } else {
        // 3. ENVIRONNEMENT DE PRODUCTION HOSTINGER (rmcbtpsarl.com)
        define('DB_HOST', 'localhost');
        define('DB_USER', 'u864783662_usr_YLmKYc9Z');
        define('DB_PASS', '5Xg^@9yATni');
        define('DB_NAME', 'u864783662_db_YLmKYc9Z');
        define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $host);
    }
}

// Variables requises par les scanners et installeurs automatiques Hostinger / cPanel
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;

/**
 * Initialisation de la connexion PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );
    } catch (PDOException $e) {
        // Tentative de fallback si besoin
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );
        } catch (PDOException $e2) {
            die("Erreur critique de connexion à la base de données : " . $e2->getMessage());
        }
    }

    // Fuseau horaire MySQL Cameroun (UTC+1)
    try {
        $pdo->exec("SET time_zone = '+01:00'");
    } catch (Exception $e) {}

    // Auto-installation si la base est encore vierge
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'settings'");
        if (!$check->fetch()) {
            $dumpFile = __DIR__ . '/../rmcbtp_db_export.sql';
            if (!file_exists($dumpFile)) {
                $dumpFile = __DIR__ . '/../database.sql';
            }
            if (file_exists($dumpFile)) {
                $sql = file_get_contents($dumpFile);
                if (!empty($sql)) {
                    $pdo->exec($sql);
                }
            }
        }
    } catch (Exception $e) {}

    return $pdo;
}

$pdo = getDBConnection();

/**
 * Récupérer un paramètre dans la table settings avec valeur par défaut
 */
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Fonction de récupération de tous les paramètres du site
 */
function getSiteSettings($pdo) {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {}
    return $settings;
}

/**
 * Nettoyage des entrées utilisateurs
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Convertit une URL vidéo (YouTube, Vimeo, MP4 direct) en structure embed
 */
function parseVideoEmbed($url) {
    if (empty($url)) return null;
    $url = trim($url);

    // YouTube
    $youtubeId = null;
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([^"&?\/ ]{11})/i', $url, $matches)) {
        $youtubeId = $matches[1];
    }

    if ($youtubeId) {
        $embedUrl = "https://www.youtube-nocookie.com/embed/" . $youtubeId . "?rel=0&modestbranding=1";
        return [
            'type' => 'youtube',
            'id' => $youtubeId,
            'url' => $url,
            'embed_url' => $embedUrl,
            'thumb' => "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg",
            'embed_html' => '<iframe src="' . $embedUrl . '" title="Vidéo Chantier BTP" style="position: absolute; top:0; left:0; width:100%; height:100%; border:0; border-radius:8px;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>'
        ];
    }

    // Direct MP4
    if (preg_match('/\.(mp4|webm|ogg)$/i', $url) || strpos($url, 'uploads/') !== false) {
        return [
            'type' => 'local',
            'url' => $url,
            'embed_url' => $url,
            'thumb' => '',
            'embed_html' => '<video controls playsinline preload="metadata" style="width:100%; height:100%; object-fit:cover; border-radius:8px; background:#000;"><source src="' . htmlspecialchars($url) . '" type="video/mp4">Votre navigateur ne supporte pas la vidéo HTML5.</video>'
        ];
    }

    return [
        'type' => 'iframe',
        'url' => $url,
        'embed_url' => $url,
        'thumb' => '',
        'embed_html' => '<iframe src="' . htmlspecialchars($url) . '" style="position: absolute; top:0; left:0; width:100%; height:100%; border:0; border-radius:8px;" allowfullscreen></iframe>'
    ];
}

/**
 * Résolution des chemins d'images
 */
function resolveImage($path, $fallback = 'images/bg_1.jpg') {
    if (empty($path)) {
        return $fallback;
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    $cleaned = preg_replace('#^assets/images/#', 'images/', $path);
    if (file_exists(__DIR__ . '/../' . $cleaned)) {
        return $cleaned;
    }
    if (file_exists(__DIR__ . '/../' . $path)) {
        return $path;
    }
    $base = basename($path);
    if (file_exists(__DIR__ . '/../images/' . $base)) {
        return 'images/' . $base;
    }
    return $fallback;
}

// Module Gestionnaire de Visuels de Repère par Page & Section si présent
if (file_exists(__DIR__ . '/visuals_helper.php')) {
    require_once __DIR__ . '/visuals_helper.php';
}
