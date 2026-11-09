<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$siteSettings = getSiteSettings($pdo);

// Calculer le nombre de messages non lus pour le badge
$unreadCount = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();

// Page courante
$adminCurrentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($adminTitle) ? htmlspecialchars($adminTitle) . ' | Dashboard RMC BTP' : 'Tableau de Bord Administrateur | RMC BTP SARL'; ?></title>
  
  <link rel="icon" type="image/png" href="../images/favicon.png?v=<?= time() ?>">
  <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico?v=<?= time() ?>">
  <link rel="apple-touch-icon" href="../images/apple-touch-icon.png?v=<?= time() ?>">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime(__DIR__ . '/../css/admin.css'); ?>">
</head>
<body>

<div class="admin-layout">
  <!-- Barre Latérale (Sidebar) -->
  <aside class="admin-sidebar">
    <a href="index.php" class="sidebar-brand">
      <img src="../images/logo-white.png" alt="RMC BTP SARL" style="height: 48px; width: auto; object-fit: contain; margin-right: 12px; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));">
      <div>
        <h3>RMC BTP SARL</h3>
        <p>Administration Yaoundé</p>
      </div>
    </a>

    <ul class="sidebar-menu">
      <li class="sidebar-item">
        <a href="index.php" class="sidebar-link <?php echo ($adminCurrentPage === 'index') ? 'active' : ''; ?>">
          <i class="fas fa-tachometer-alt"></i>
          <span>Tableau de Bord</span>
        </a>
      </li>

      <div class="sidebar-section-title">Demandes & Prospects</div>

      <li class="sidebar-item">
        <a href="messages.php" class="sidebar-link <?php echo ($adminCurrentPage === 'messages') ? 'active' : ''; ?>">
          <i class="fas fa-inbox"></i>
          <span>Boîte de Réception</span>
          <?php if ($unreadCount > 0): ?>
            <span class="badge-counter"><?php echo $unreadCount; ?></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="sidebar-item">
        <a href="testimonials.php" class="sidebar-link <?php echo ($adminCurrentPage === 'testimonials') ? 'active' : ''; ?>">
          <i class="fas fa-comment-alt"></i>
          <span>Avis & Témoignages</span>
        </a>
      </li>

      <div class="sidebar-section-title">Chantiers & Médias</div>

      <li class="sidebar-item">
        <a href="projects.php" class="sidebar-link <?php echo ($adminCurrentPage === 'projects') ? 'active' : ''; ?>">
          <i class="fas fa-city"></i>
          <span>Chantiers & Projets</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a href="services.php" class="sidebar-link <?php echo ($adminCurrentPage === 'services') ? 'active' : ''; ?>">
          <i class="fas fa-tools"></i>
          <span>Services BTP</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a href="media.php" class="sidebar-link <?php echo ($adminCurrentPage === 'media') ? 'active' : ''; ?>">
          <i class="fas fa-photo-video"></i>
          <span>Médiathèque & Vidéos</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a href="news.php" class="sidebar-link <?php echo ($adminCurrentPage === 'news') ? 'active' : ''; ?>">
          <i class="fas fa-newspaper"></i>
          <span>Actualités & Blog</span>
        </a>
      </li>

      <div class="sidebar-section-title">Configuration</div>

      <li class="sidebar-item">
        <a href="settings.php" class="sidebar-link <?php echo ($adminCurrentPage === 'settings') ? 'active' : ''; ?>">
          <i class="fas fa-sliders-h"></i>
          <span>Coordonnées & WhatsApp</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a href="profile.php" class="sidebar-link <?php echo ($adminCurrentPage === 'profile') ? 'active' : ''; ?>">
          <i class="fas fa-shield-alt"></i>
          <span>Sécurité & Profil</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-footer">
      <div class="user-badge">
        <div class="user-avatar">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="user-info">
          <h5><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'M. Rodrigue DJOMO'); ?></h5>
          <span>Direction Générale</span>
        </div>
      </div>
      <a href="logout.php" title="Déconnexion" class="logout-btn" onclick="return confirm('Confirmer la déconnexion ?');">
        <i class="fas fa-power-off"></i>
      </a>
    </div>
  </aside>

  <!-- Zone Principale (Main) -->
  <main class="admin-main">
    <header class="admin-header">
      <div class="header-title">
        <h2><?php echo isset($adminTitle) ? htmlspecialchars($adminTitle) : 'Tableau de Bord'; ?></h2>
        <span class="header-pill"><i class="fas fa-shield-alt"></i> Espace Sécurisé</span>
      </div>
      <div class="header-actions">
        <div class="header-date">
          <i class="fas fa-calendar-day" style="color: var(--admin-accent);"></i> <?php echo date('d/m/Y'); ?>
        </div>
        <a href="../index.php" target="_blank" class="header-btn header-btn-site" title="Ouvrir le site public">
          <i class="fas fa-external-link-alt"></i> Voir le site web
        </a>
        <a href="logout.php" class="action-btn action-btn-danger action-btn-icon-only" title="Déconnexion" onclick="return confirm('Confirmer la déconnexion ?');">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </header>

    <div class="admin-content">
