<?php
/**
 * Configuration Base de Données - RMC BTP SARL
 * Compatible Local XAMPP & Hébergement en ligne InfinityFree
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir le fuseau horaire du Cameroun (UTC+1)
date_default_timezone_set('Africa/Douala');

define('SITE_NAME', 'RMC BTP SARL');
define('SITE_SLOGAN', 'Construire aujourd\'hui les solutions de demain');
define('ADMIN_EMAIL', 'info@rmcbtp.com');

// Détection automatique de l'environnement (Local XAMPP vs Hostinger / InfinityFree / Production)
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$is_local = (
    strpos($host, 'localhost') !== false ||
    strpos($host, '127.0.0.1') !== false ||
    (PHP_OS_FAMILY === 'Windows' && file_exists('C:/xampp'))
);

// Fichier de configuration spécifique à la production / Hostinger si présent
$prodConfig = __DIR__ . '/db_production.php';
if (file_exists($prodConfig)) {
    require_once $prodConfig;
}

if (!defined('DB_HOST')) {
    if (!$is_local) {
        // Environnement En Ligne (Hostinger, cPanel ou InfinityFree)
        $is_hostinger = (file_exists('/home/') || strpos($host, 'hostinger') !== false || getenv('HOSTINGER_ENV'));
        
        if ($is_hostinger || getenv('DB_HOST')) {
            // Configuration standard Hostinger / Serveur Dédié
            define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
            define('DB_USER', getenv('DB_USER') ?: 'u_rmcbtp');
            define('DB_PASS', getenv('DB_PASS') ?: '');
            define('DB_NAME', getenv('DB_NAME') ?: 'rmcbtp_db');
            define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $host);
        } else {
            // Configuration InfinityFree historique
            define('DB_HOST', 'sql113.infinityfree.com');
            define('DB_USER', 'if0_39882531');
            define('DB_PASS', 'cmTJtR2Z2yq8MO');
            define('DB_NAME', getenv('DB_NAME') ?: 'if0_39882531_rmcbtp');
            define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'rmcbtp.free.nf'));
        }
    } else {
        // Configuration locale (XAMPP) - 127.0.0.1 force TCP et évite l'erreur de socket [2002]
        define('DB_HOST', '127.0.0.1');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'rmcbtp_db');
        define('SITE_URL', 'http://localhost/rmcbtpsarl');
    }
}

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    global $is_online;

    // Tentative de connexion avec la base principale et variantes possibles sur InfinityFree
    $possibleDbNames = $is_online 
        ? [DB_NAME, 'if0_39882531_rmcbtp', 'if0_39882531_rmcbtp_db', 'if0_39882531_rmc']
        : [DB_NAME];

    $connected = false;
    foreach ($possibleDbNames as $dbname) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $dbname . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 4,
            ]);
            $connected = true;
            break;
        } catch (PDOException $e) {
            continue;
        }
    }

    if (!$connected) {
        // Fallback automatique si la détection d'hôte a été inversée
        try {
            if ($is_online) {
                $pdo = new PDO("mysql:host=127.0.0.1;dbname=rmcbtp_db;charset=utf8mb4", "root", "", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 4,
                ]);
            } else {
                $pdo = new PDO("mysql:host=sql113.infinityfree.com;dbname=if0_39882531_rmcbtp;charset=utf8mb4", "if0_39882531", "cmTJtR2Z2yq8MO", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 4,
                ]);
            }
        } catch (PDOException $e2) {
            die("Erreur de connexion à la base de données : " . $e2->getMessage());
        }
    }

    // Synchroniser l'heure MySQL avec le fuseau horaire du Cameroun (UTC+1)
    try {
        $pdo->exec("SET time_zone = '+01:00'");
    } catch (Exception $e) {}

    // Auto-installation si la base en ligne est encore vide
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'settings'");
        if (!$check->fetch()) {
            $dumpFile = __DIR__ . '/../rmcbtp_db_export.sql';
            if (file_exists($dumpFile)) {
                $sql = file_get_contents($dumpFile);
                $pdo->exec($sql);
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
 * Fonction de récupération de tous les paramètres du site sous forme de tableau associatif
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
    } catch (Exception $e) {
        // En cas d'erreur
    }
    return $settings;
}

/**
 * Fonction de nettoyage des entrées utilisateurs
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
            'embed_html' => '<iframe src="' . $embedUrl . '" title="Vidéo Chantier FEE BTP" style="position: absolute; top:0; left:0; width:100%; height:100%; border:0; border-radius:8px;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>'
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
 * Fonction de résolution d'image
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
