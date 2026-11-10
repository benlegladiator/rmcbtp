<?php
// ============================================================================
// CONFIGURATION & CONNEXION BASE DE DONNÉES - APPLICATION SÉCURITÉ & POINTAGE
// RMC BTP SARL (security.rmcbtpsarl.com)
// Dual-Engine : MySQL (phpMyAdmin) avec bascule automatique SQLite
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Douala');

$db_host = 'localhost';
$db_name = 'securite_db';
$db_user = 'root';
$db_pass = '';

$pdo = null;

try {
    // Connexion MySQL
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialisation tables MySQL si inexistantes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` varchar(20) NOT NULL DEFAULT 'gardien',
            `fullname` varchar(100) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `personnel` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `matricule` varchar(30) NOT NULL UNIQUE,
            `fullname` varchar(100) NOT NULL,
            `phone` varchar(30) DEFAULT NULL,
            `role_title` varchar(80) NOT NULL,
            `department` varchar(50) NOT NULL DEFAULT 'Chantier',
            `normal_start_time` varchar(10) DEFAULT '08:00',
            `avatar_color` varchar(20) DEFAULT '#0284c7',
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `pointages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `personnel_id` int(11) NOT NULL,
            `date_pointage` date NOT NULL,
            `heure_arrivee` time NOT NULL,
            `heure_depart` time DEFAULT NULL,
            `duree_minutes` int(11) DEFAULT 0,
            `is_late` tinyint(1) DEFAULT 0,
            `status` varchar(20) NOT NULL DEFAULT 'present',
            `notes` text DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_personnel` (`date_pointage`, `personnel_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

} catch (PDOException $eMysql) {
    // Bascule automatique SQLite (Zéro configuration requise sur n'importe quel serveur)
    try {
        $db_file = __DIR__ . '/database.sqlite';
        $pdo = new PDO('sqlite:' . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'gardien',
                fullname TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS personnel (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                matricule TEXT UNIQUE NOT NULL,
                fullname TEXT NOT NULL,
                phone TEXT,
                role_title TEXT NOT NULL,
                department TEXT NOT NULL DEFAULT 'Chantier',
                normal_start_time TEXT DEFAULT '08:00',
                avatar_color TEXT DEFAULT '#0284c7',
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS pointages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                personnel_id INTEGER NOT NULL,
                date_pointage TEXT NOT NULL,
                heure_arrivee TEXT NOT NULL,
                heure_depart TEXT,
                duree_minutes INTEGER DEFAULT 0,
                is_late INTEGER DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'present',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

    } catch (PDOException $eSqlite) {
        die("Erreur critique d'accès à la base de données : " . $eSqlite->getMessage());
    }
}

// ----------------------------------------------------------------------------
// SEED INITIAL DES UTILISATEURS & DU PERSONNEL BTP (si tables vides)
// ----------------------------------------------------------------------------
try {
    $uCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($uCount == 0) {
        $passGardien = password_hash('gardien123', PASSWORD_DEFAULT);
        $passBoss = password_hash('admin123', PASSWORD_DEFAULT);
        $stmtU = $pdo->prepare("INSERT INTO users (username, password, role, fullname) VALUES (?, ?, ?, ?)");
        $stmtU->execute(['gardien', $passGardien, 'gardien', 'Poste de Sécurité / Gardien']);
        $stmtU->execute(['boss', $passBoss, 'boss', 'M. Rodrigue DJOMO (Direction)']);
    }

    $pCount = $pdo->query("SELECT COUNT(*) FROM personnel")->fetchColumn();
    if ($pCount == 0) {
        $samplePersonnel = [
            ['RMC-001', 'M. MBALLA Jean-Pierre', '699 10 20 30', 'Chef de Chantier', 'Chantier', '07:30', '#0284c7'],
            ['RMC-002', 'M. TCHOUPO Samuel', '677 45 89 12', 'Maçon Coffreur', 'Chantier', '08:00', '#ea580c'],
            ['RMC-003', 'M. ESSOMBA François', '695 33 22 11', 'Ferrailleur Pro', 'Chantier', '08:00', '#10b981'],
            ['RMC-004', 'M. NGANDO Roger', '655 44 77 88', 'Conducteur Poids Lourd / Benne', 'Logistique', '07:45', '#8b5cf6'],
            ['RMC-005', 'M. FOPA Michel', '699 88 12 34', 'Soudeur & Menuisier Métallique', 'Atelier', '08:00', '#f59e0b'],
            ['RMC-006', 'Mme ATANGANA Mireille', '671 22 33 44', 'Comptable de Chantier', 'Bureau', '08:00', '#06b6d4'],
            ['RMC-007', 'M. BIYA Paulin', '694 55 66 77', 'Électricien BTP & Câblage', 'Chantier', '08:00', '#ec4899'],
            ['RMC-008', 'M. KAMDEM Célestin', '680 11 99 00', 'Topographe & Géomètre', 'Bureau', '08:00', '#6366f1'],
            ['RMC-009', 'M. NANA Yves', '677 33 44 55', 'Manœuvre Polyvalent', 'Chantier', '08:00', '#64748b'],
            ['RMC-010', 'M. EKOBO Daniel', '699 77 66 55', 'Agent de Sécurité Jour', 'Sécurité', '07:00', '#059669'],
        ];

        $stmtP = $pdo->prepare("
            INSERT INTO personnel (matricule, fullname, phone, role_title, department, normal_start_time, avatar_color)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($samplePersonnel as $p) {
            $stmtP->execute($p);
        }
    }
} catch (Exception $eSeed) {
    // Ignorer si déjà existant
}

// ----------------------------------------------------------------------------
// FONCTIONS UTILITAIRES & SÉCURITÉ
// ----------------------------------------------------------------------------
function require_login() {
    if (!isset($_SESSION['sec_user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_boss() {
    return isset($_SESSION['sec_role']) && $_SESSION['sec_role'] === 'boss';
}

function format_minutes_duree($minutes) {
    if ($minutes <= 0) return '0 min';
    $h = floor($minutes / 60);
    $m = $minutes % 60;
    if ($h > 0) {
        return "{$h}h " . ($m > 0 ? "{$m}m" : "");
    }
    return "{$m} min";
}

function get_boss_phone() {
    return '237697398455'; // Numéro WhatsApp du Boss Rodrigue DJOMO
}
