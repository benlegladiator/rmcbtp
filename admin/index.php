<?php
$adminTitle = "Tableau de Bord & Vue d'Ensemble";
require_once __DIR__ . '/header.php';

// Statistiques réelles
$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unreadMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
$totalDevis = $pdo->query("SELECT COUNT(*) FROM messages WHERE type = 'devis'")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();

// Derniers messages
$latestMessages = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetchAll();

// Derniers projets
$latestProjects = $pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 4")->fetchAll();
?>

<!-- ========================================================================
     1. BANNIÈRE DE BIENVENUE & ORIENTATION RAPIDE
     ======================================================================== -->
<div class="welcome-banner">
  <div class="welcome-text">
    <h3>
      <i class="fas fa-hand-sparkles" style="color: var(--admin-accent);"></i> 
      Bonjour <?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'M. Rodrigue DJOMO'); ?> !
    </h3>
    <p>
      Bienvenue sur votre centre de pilotage digital <strong>RMC BTP SARL</strong>. Suivez vos demandes de devis, publiez vos chantiers et gérez les contenus de votre site en toute simplicité.
    </p>
  </div>
  <div style="display: flex; gap: 10px; align-items: center; z-index: 2;">
    <?php if ($unreadMessages > 0): ?>
      <a href="messages.php?filter=unread" class="btn btn-secondary btn-sm" style="font-weight: 700;">
        <i class="fas fa-envelope"></i> <?php echo $unreadMessages; ?> Devis / Messages à Traiter
      </a>
    <?php else: ?>
      <span class="badge badge-active" style="padding: 8px 16px; font-size: 0.85rem;">
        <i class="fas fa-check-circle"></i> Tous les messages sont traités
      </span>
    <?php endif; ?>
  </div>
</div>

<!-- ========================================================================
     2. INDICATEURS CLÉS DE PERFORMANCE (KPIs)
     ======================================================================== -->
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-data">
      <h4>Messages & Devis Reçus</h4>
      <div class="metric-number"><?php echo $totalMessages; ?></div>
      <div class="metric-sub"><i class="fas fa-database" style="color: var(--admin-primary);"></i> Total cumulé du site</div>
    </div>
    <div class="metric-icon icon-blue">
      <i class="fas fa-envelope-open-text"></i>
    </div>
  </div>

  <div class="metric-card" style="<?php echo ($unreadMessages > 0) ? 'border-color: #fca5a5;' : ''; ?>">
    <div class="metric-data">
      <h4>Non Lus / En Attente</h4>
      <div class="metric-number" style="color: <?php echo ($unreadMessages > 0) ? '#dc2626' : '#16a34a'; ?>;">
        <?php echo $unreadMessages; ?>
      </div>
      <div class="metric-sub">
        <?php if ($unreadMessages > 0): ?>
          <span style="color: #dc2626; font-weight: 700;"><i class="fas fa-exclamation-triangle"></i> Réponse prioritaire requise</span>
        <?php else: ?>
          <span style="color: #16a34a;"><i class="fas fa-check"></i> Boîte à jour</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="metric-icon icon-orange">
      <i class="fas fa-bell"></i>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-data">
      <h4>Chantiers & Ouvrages</h4>
      <div class="metric-number"><?php echo $totalProjects; ?></div>
      <div class="metric-sub"><i class="fas fa-check-double" style="color: var(--admin-purple);"></i> Publiés dans votre portfolio</div>
    </div>
    <div class="metric-icon icon-purple">
      <i class="fas fa-city"></i>
    </div>
  </div>

  <div class="metric-card">
    <div class="metric-data">
      <h4>Pôles d'Expertise</h4>
      <div class="metric-number"><?php echo $totalServices; ?></div>
      <div class="metric-sub"><i class="fas fa-tools" style="color: var(--admin-success);"></i> Services actifs et visibles</div>
    </div>
    <div class="metric-icon icon-green">
      <i class="fas fa-hard-hat"></i>
    </div>
  </div>
</div>

<!-- ========================================================================
     3. ACTIONS FRÉQUENTES EN 1 CLIC (GRILLE ULTRA INTUITIVE)
     ======================================================================== -->
<div class="quick-actions-section">
  <div class="section-label-bar">
    <h3><i class="fas fa-bolt" style="color: var(--admin-accent);"></i> Actions Rapides de Gestion</h3>
    <span style="font-size: 0.82rem; color: var(--admin-muted);">Sélectionnez une opération pour y accéder directement</span>
  </div>

  <div class="quick-actions-grid">
    <a href="messages.php?filter=unread" class="quick-action-card highlight">
      <div class="quick-action-icon" style="background: #ffedd5; color: #ea580c;">
        <i class="fas fa-inbox"></i>
      </div>
      <div class="quick-action-info">
        <h4>Traiter les Devis</h4>
        <p><?php echo $unreadMessages; ?> demandes en attente de réponse</p>
      </div>
    </a>

    <a href="projects.php?action=new" class="quick-action-card">
      <div class="quick-action-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-plus-circle"></i>
      </div>
      <div class="quick-action-info">
        <h4>Ajouter un Chantier</h4>
        <p>Publier un projet avec photos et vidéo drone</p>
      </div>
    </a>

    <a href="media.php" class="quick-action-card">
      <div class="quick-action-icon" style="background: #f3e8ff; color: #7c3aed;">
        <i class="fas fa-photo-video"></i>
      </div>
      <div class="quick-action-info">
        <h4>Photos & Vidéos</h4>
        <p>Médiathèque et fichiers de chantiers</p>
      </div>
    </a>

    <a href="settings.php" class="quick-action-card">
      <div class="quick-action-icon" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-sliders-h"></i>
      </div>
      <div class="quick-action-info">
        <h4>Contacts & WhatsApp</h4>
        <p>Modifier numéros, horaires et textes</p>
      </div>
    </a>
  </div>
</div>

<!-- ========================================================================
     4. DERNIÈRES DEMANDES DE DEVIS & MESSAGES
     ======================================================================== -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>
      <i class="fas fa-envelope-open-text" style="color: var(--admin-accent);"></i> 
      Dernières Demandes de Devis & Messages Entrants
    </h3>
    <a href="messages.php" class="btn btn-outline btn-sm">
      <i class="fas fa-inbox"></i> Ouvrir Toute la Boîte de Réception
    </a>
  </div>

  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Statut</th>
          <th>Type</th>
          <th>Expéditeur</th>
          <th>Coordonnées</th>
          <th>Objet & Prestation</th>
          <th>Date de Réception</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($latestMessages)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 40px;">
              <i class="fas fa-inbox" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
              Aucun message ou devis reçu pour le moment.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($latestMessages as $m): ?>
            <tr>
              <td>
                <?php if ($m['status'] === 'unread'): ?>
                  <span class="badge badge-unread"><i class="fas fa-envelope"></i> Non lu</span>
                <?php else: ?>
                  <span class="badge badge-read"><i class="fas fa-check"></i> Traité</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($m['type'] === 'devis'): ?>
                  <span class="badge badge-devis"><i class="fas fa-calculator"></i> Devis DQE</span>
                <?php elseif ($m['type'] === 'livechat'): ?>
                  <span class="badge badge-livechat"><i class="fas fa-comments"></i> Chat en Direct</span>
                <?php else: ?>
                  <span class="badge badge-contact"><i class="fas fa-envelope"></i> Contact</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($m['sender_name']); ?></strong>
              </td>
              <td>
                <div><i class="fas fa-phone-alt" style="color: var(--admin-accent); font-size: 0.75rem; margin-right: 4px;"></i> <?php echo htmlspecialchars($m['sender_phone'] ?: 'Non renseigné'); ?></div>
                <small style="color: var(--admin-muted);"><i class="fas fa-envelope" style="font-size: 0.75rem; margin-right: 4px;"></i> <?php echo htmlspecialchars($m['sender_email'] ?: 'Non renseigné'); ?></small>
              </td>
              <td>
                <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($m['subject'] ?? 'Demande d\'information'); ?></div>
                <small style="color: var(--admin-primary); font-weight: 600;"><i class="fas fa-hard-hat" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($m['service_requested'] ?? 'Général'); ?></small>
              </td>
              <td>
                <span style="font-size: 0.85rem; color: var(--admin-muted);">
                  <i class="fas fa-clock" style="font-size: 0.75rem; margin-right: 3px;"></i> <?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?>
                </span>
              </td>
              <td style="text-align: right;">
                <a href="messages.php?view=<?php echo $m['id']; ?>" class="action-btn" title="Consulter et répondre">
                  <i class="fas fa-eye"></i> Consulter
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================================================
     5. APERÇU DES CHANTIERS BTP EN LIGNE
     ======================================================================== -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>
      <i class="fas fa-building" style="color: var(--admin-accent);"></i> 
      Chantiers & Réalisations Récemment Publiés
    </h3>
    <a href="projects.php" class="btn btn-outline btn-sm">
      <i class="fas fa-th-list"></i> Gérer Tous les Chantiers
    </a>
  </div>
  
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Aperçu & Nom de l'Ouvrage</th>
          <th>Catégorie</th>
          <th>Localisation</th>
          <th>Maître d'Ouvrage</th>
          <th>Délai</th>
          <th>Statut</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latestProjects as $p): ?>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 12px;">
                <img src="../<?php echo htmlspecialchars($p['main_image']); ?>" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid var(--admin-border);" alt="Aperçu">
                <div>
                  <strong style="color: var(--admin-text); display: block;"><?php echo htmlspecialchars($p['title']); ?></strong>
                  <?php if (!empty($p['video_url'])): ?>
                    <small style="color: var(--admin-accent); font-weight: 600;"><i class="fas fa-video"></i> Vidéo disponible</small>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <span class="badge badge-contact"><?php echo htmlspecialchars($p['category']); ?></span>
            </td>
            <td>
              <i class="fas fa-map-marker-alt" style="color: var(--admin-accent); font-size: 0.8rem; margin-right: 4px;"></i>
              <?php echo htmlspecialchars($p['location']); ?>
            </td>
            <td><?php echo htmlspecialchars($p['client_name'] ?? 'Client Privé'); ?></td>
            <td><?php echo htmlspecialchars($p['duration'] ?? 'N/A'); ?></td>
            <td>
              <span class="badge <?php echo ($p['status'] === 'Terminé') ? 'badge-active' : 'badge-devis'; ?>">
                <i class="fas <?php echo ($p['status'] === 'Terminé') ? 'fa-check' : 'fa-spinner fa-spin'; ?>"></i>
                <?php echo htmlspecialchars($p['status']); ?>
              </span>
            </td>
            <td style="text-align: right;">
              <a href="projects.php?edit=<?php echo $p['id']; ?>" class="action-btn" title="Modifier ce chantier">
                <i class="fas fa-edit"></i> Modifier
              </a>
              <a href="../project-details.php?id=<?php echo $p['id']; ?>" target="_blank" class="action-btn action-btn-icon-only" title="Voir la page publique">
                <i class="fas fa-external-link-alt"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
