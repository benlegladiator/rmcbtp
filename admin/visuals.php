<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/visuals_helper.php';

$pdo = getDBConnection();
$siteSettings = getSiteSettings($pdo);

$success = '';
$error = '';

$filterPage = $_GET['page_slug'] ?? 'all';

// Synchronisation des clés de visuels par défaut
if (isset($_GET['sync']) && $_GET['sync'] == '1') {
    try {
        initPageVisualsTable($pdo);
        header("Location: visuals.php?page_slug=" . urlencode((string)$filterPage) . "&synced=1");
        exit;
    } catch (Throwable $e) {
        $error = "Erreur de synchronisation : " . $e->getMessage();
    }
}

if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $success = "Le visuel a été mis à jour avec succès et s'affiche maintenant sur le site en direct.";
} elseif (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $success = "Le visuel a été réinitialisé à son image d'origine avec succès.";
} elseif (isset($_GET['created']) && $_GET['created'] == '1') {
    $success = "Le nouveau visuel a été ajouté et enregistré avec succès.";
} elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $success = "Le visuel a été supprimé avec succès.";
} elseif (isset($_GET['synced']) && $_GET['synced'] == '1') {
    $success = "Toutes les sections et visuels du site ont été scannés et synchronisés avec succès.";
}

try {
    initPageVisualsTable($pdo);
} catch (Throwable $e) {
    $error = "Erreur d'initialisation de la table des visuels : " . $e->getMessage();
}

// 1. Traitement de l'ajout d'un nouveau visuel libre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_visual'])) {
    $newPageSlug = trim((string)($_POST['page_slug'] ?? 'home'));
    $newSectionKey = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(trim((string)($_POST['section_key'] ?? ''))));
    $newSectionName = trim((string)($_POST['section_name'] ?? 'Section Spécifique'));
    $newTitle = trim((string)($_POST['title'] ?? 'Nouveau Visuel'));
    $newDesc = trim((string)($_POST['description'] ?? ''));
    $newFocal = trim((string)($_POST['focal_point'] ?? 'center top'));

    if (empty($newSectionKey)) {
        $error = "Veuillez renseigner un identifiant technique unique pour ce visuel (ex: banniere_partenaires).";
    } else {
        $imageUrl = 'images/bg_1.jpg';
        if (isset($_FILES['visual_file']) && $_FILES['visual_file']['error'] === UPLOAD_ERR_OK) {
            $upDir = __DIR__ . '/../uploads/visuals/';
            if (!is_dir($upDir)) {
                @mkdir($upDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['visual_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                $filename = 'vis_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $newPageSlug) . '_' . $newSectionKey . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['visual_file']['tmp_name'], $upDir . $filename)) {
                    $imageUrl = 'uploads/visuals/' . $filename;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO page_visuals 
                (page_slug, section_key, section_name, title, description, image_url, default_url, focal_point, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 999)");
            $stmt->execute([$newPageSlug, $newSectionKey, $newSectionName, $newTitle, $newDesc, $imageUrl, $imageUrl, $newFocal]);
            header("Location: visuals.php?page_slug=" . urlencode($newPageSlug) . "&created=1");
            exit;
        } catch (Throwable $e) {
            $error = "Impossible d'ajouter le visuel (l'identifiant existe peut-être déjà) : " . $e->getMessage();
        }
    }
}

// 2. Traitement de la suppression d'un visuel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_visual'])) {
    $visualId = (int)($_POST['visual_id'] ?? 0);
    if ($visualId > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM page_visuals WHERE id = ?");
            $stmt->execute([$visualId]);
            header("Location: visuals.php?page_slug=" . urlencode((string)$filterPage) . "&deleted=1");
            exit;
        } catch (Throwable $e) {
            $error = "Erreur de suppression : " . $e->getMessage();
        }
    }
}

// 3. Traitement de la mise à jour d'un visuel existant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_visual'])) {
    $visualId = (int)($_POST['visual_id'] ?? 0);
    $focalPoint = trim((string)($_POST['focal_point'] ?? 'center top'));
    $title = trim((string)($_POST['title'] ?? ''));

    if ($visualId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM page_visuals WHERE id = ?");
            $stmt->execute([$visualId]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current) {
                $newImageUrl = $current['image_url'];

                // Si réinitialisation demandée
                if (isset($_POST['reset_default']) && $_POST['reset_default'] == '1') {
                    $newImageUrl = $current['default_url'];
                    $focalPoint = 'center top';
                    $stmtUp = $pdo->prepare("UPDATE page_visuals SET image_url = ?, focal_point = ? WHERE id = ?");
                    $stmtUp->execute([$newImageUrl, $focalPoint, $visualId]);
                    header("Location: visuals.php?page_slug=" . urlencode((string)$filterPage) . "&reset=1");
                    exit;
                } else {
                    // Si téléversement d'un nouveau fichier image
                    if (isset($_FILES['visual_file']) && $_FILES['visual_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fileError = $_FILES['visual_file']['error'];
                        if ($fileError === UPLOAD_ERR_OK) {
                            $upDir = __DIR__ . '/../uploads/visuals/';
                            if (!is_dir($upDir)) {
                                @mkdir($upDir, 0777, true);
                            }

                            $ext = strtolower(pathinfo($_FILES['visual_file']['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                                $cleanSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$current['page_slug']);
                                $cleanKey = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$current['section_key']);
                                $filename = 'vis_' . $cleanSlug . '_' . $cleanKey . '_' . time() . '.' . $ext;
                                $dest = $upDir . $filename;

                                if (move_uploaded_file($_FILES['visual_file']['tmp_name'], $dest)) {
                                    $newImageUrl = 'uploads/visuals/' . $filename;
                                } else {
                                    $error = "Échec de l'enregistrement de l'image. Vérifiez les permissions du dossier uploads/visuals/.";
                                }
                            } else {
                                $error = "Format d'image non valide. Veuillez utiliser JPG, JPEG, PNG, WEBP ou SVG.";
                            }
                        } elseif ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
                            $error = "L'image sélectionnée est trop volumineuse pour le serveur (dépasse upload_max_filesize). Veuillez réduire son poids (Max conseillé : 5 Mo).";
                        } else {
                            $error = "Erreur lors de l'envoi du fichier (Code erreur : " . $fileError . ").";
                        }
                    }

                    if (empty($error)) {
                        $finalTitle = !empty($title) ? $title : $current['title'];
                        $stmtUp = $pdo->prepare("UPDATE page_visuals SET image_url = ?, focal_point = ?, title = ? WHERE id = ?");
                        $stmtUp->execute([$newImageUrl, $focalPoint, $finalTitle, $visualId]);
                        header("Location: visuals.php?page_slug=" . urlencode((string)$filterPage) . "&saved=1");
                        exit;
                    }
                }
            } else {
                $error = "Visuel introuvable en base de données.";
            }
        } catch (Throwable $e) {
            $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$adminTitle = "Gestionnaire de Visuels de Repère par Section";
require_once __DIR__ . '/header.php';

// Filtre par page active
$filterPage = $_GET['page_slug'] ?? 'all';
$visuals = [];
try {
    if ($filterPage !== 'all') {
        $stmt = $pdo->prepare("SELECT * FROM page_visuals WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$filterPage]);
    } else {
        $stmt = $pdo->query("SELECT * FROM page_visuals ORDER BY page_slug ASC, sort_order ASC, id ASC");
    }
    if ($stmt) {
        $visuals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $error = "Impossible de charger les visuels : " . $e->getMessage();
}

// Regrouper par page et par section pour l'affichage
$grouped = [];
foreach ($visuals as $v) {
    $p = $v['page_slug'] ?? 'autre';
    $s = $v['section_name'] ?? 'Section Principale';
    if (!isset($grouped[$p])) $grouped[$p] = [];
    if (!isset($grouped[$p][$s])) $grouped[$p][$s] = [];
    $grouped[$p][$s][] = $v;
}

$pageLabels = [
    'all' => 'Tous les Visuels',
    'identity' => '🎨 Identité, Logos & Favicon',
    'home' => "🏠 Page d'Accueil (index.php)",
    'about' => "🏢 Page À Propos & Équipe (about.php)",
    'services' => "🛠️ Page Services (services.php)",
    'projects' => "🏗️ Page Projets (project.php)",
    'contact' => "📞 Page Contact (contact.php)",
    'blog' => "📰 Page Actualités (blog.php)"
];

// Compteurs par catégorie
$counts = [];
try {
    $cStmt = $pdo->query("SELECT page_slug, COUNT(*) as cnt FROM page_visuals GROUP BY page_slug");
    while ($r = $cStmt->fetch(PDO::FETCH_ASSOC)) {
        $counts[$r['page_slug']] = (int)$r['cnt'];
    }
    $totalCount = array_sum($counts);
} catch (Throwable $e) {
    $totalCount = count($visuals);
}
?>

<div class="admin-content-inner">
  <div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h1 class="page-title mb-1" style="font-size: 1.7rem; font-weight: 800; color: #0c2e60;">
        <i class="fas fa-layer-group text-primary mr-2"></i> Visuels de Repère par Page & Section
      </h1>
      <p class="text-muted mb-0 small">
        Gérez 100% des images, logos, bannières et portraits du site. Toute modification est immédiatement active sur le site en direct.
      </p>
    </div>
    <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
      <a href="visuals.php?page_slug=<?php echo urlencode($filterPage); ?>&sync=1" class="btn btn-outline-info btn-sm font-weight-bold" title="Scanne et synchronise toutes les sections">
        <i class="fas fa-sync-alt mr-1"></i> Synchroniser Tous les Visuels
      </a>
      <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" data-toggle="collapse" data-target="#collapseNewVisual" aria-expanded="false" aria-controls="collapseNewVisual">
        <i class="fas fa-plus-circle mr-1"></i> Ajouter un Nouveau Visuel
      </button>
      <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm font-weight-bold">
        <i class="fas fa-external-link-alt mr-1"></i> Voir le Site
      </a>
    </div>
  </div>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #10b981; padding: 15px; margin-bottom: 20px; background: #ecfdf5; color: #065f46; border-radius: 8px;">
      <i class="fas fa-check-circle mr-2 text-success"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Fermer" style="float: right; border: 0; background: transparent; font-size: 1.2rem;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #ef4444; padding: 15px; margin-bottom: 20px; background: #fef2f2; color: #991b1b; border-radius: 8px;">
      <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> <?php echo $error; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Fermer" style="float: right; border: 0; background: transparent; font-size: 1.2rem;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- FORMULAIRE ACCORDÉON : AJOUTER UN NOUVEAU VISUEL LIBRE -->
  <div class="collapse mb-4" id="collapseNewVisual">
    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #0056b3; background: #ffffff;">
      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h5 class="font-weight-bold text-dark mb-1">
          <i class="fas fa-plus-circle text-primary mr-2"></i> Ajouter un Nouvel Emplacement Visuel
        </h5>
        <p class="text-muted small mb-0">Définissez une nouvelle photo pour n'importe quelle section du site.</p>
      </div>
      <div class="card-body p-4">
        <form action="visuals.php?page_slug=<?php echo urlencode($filterPage); ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="create_visual" value="1">
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="small font-weight-bold text-dark">Page Cible</label>
              <select name="page_slug" class="form-control" required>
                <option value="identity">🎨 Identité, Logos & Favicon</option>
                <option value="home" selected>🏠 Page d'Accueil (index.php)</option>
                <option value="about">🏢 Page À Propos (about.php)</option>
                <option value="services">🛠️ Page Services (services.php)</option>
                <option value="projects">🏗️ Page Projets (project.php)</option>
                <option value="contact">📞 Page Contact (contact.php)</option>
                <option value="blog">📰 Page Actualités (blog.php)</option>
                <option value="custom">⚙️ Autre Section Spécifique</option>
              </select>
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="small font-weight-bold text-dark">Clé Technique Unique (sans espaces)</label>
              <input type="text" name="section_key" class="form-control" placeholder="ex: hero_banniere_diaspora" required pattern="[a-zA-Z0-9_-]+">
              <small class="text-muted">Utilisé pour appeler l'image dans le code.</small>
            </div>

            <div class="col-md-4 mb-3">
              <label class="small font-weight-bold text-dark">Nom de la Section</label>
              <input type="text" name="section_name" class="form-control" placeholder="ex: Section Partenaires & Banques" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-dark">Titre Descriptif du Visuel</label>
              <input type="text" name="title" class="form-control" placeholder="ex: Bannière Diaspora & Financement" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-dark">Description / Rôle du Visuel</label>
              <input type="text" name="description" class="form-control" placeholder="ex: Photo grand angle affichée sous les avis clients.">
            </div>
          </div>

          <div class="row align-items-center">
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-primary"><i class="fas fa-upload mr-1"></i> Fichier Image (JPG, PNG, WEBP, SVG)</label>
              <input type="file" name="visual_file" class="form-control-file border p-2 rounded bg-light" accept="image/*" required>
            </div>

            <div class="col-md-4 mb-3">
              <label class="small font-weight-bold text-dark">Point Focal (Cadrage)</label>
              <select name="focal_point" class="form-control">
                <option value="center top">Haut (center top - Recommandé)</option>
                <option value="center center" selected>Centré (center center)</option>
                <option value="center bottom">Bas (center bottom)</option>
              </select>
            </div>

            <div class="col-md-2 mb-3 text-right">
              <label class="d-none d-md-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                <i class="fas fa-check mr-1"></i> Créer
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Barre d'onglets de filtrage par page avec compteurs -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
    <div class="card-body p-2">
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($pageLabels as $slug => $label): 
          $badgeCount = ($slug === 'all') ? $totalCount : ($counts[$slug] ?? 0);
        ?>
          <a href="visuals.php?page_slug=<?php echo urlencode($slug); ?>" 
             class="btn btn-sm <?php echo ($filterPage === $slug) ? 'btn-primary font-weight-bold' : 'btn-light text-dark'; ?>"
             style="border-radius: 8px; margin: 3px;">
            <?php echo $label; ?>
            <span class="badge badge-<?php echo ($filterPage === $slug) ? 'light text-primary' : 'secondary'; ?> ml-1">
              <?php echo $badgeCount; ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Liste des sections et cartes visuelles -->
  <?php if (empty($grouped)): ?>
    <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px; background: #ffffff;">
      <i class="fas fa-images fa-3x text-muted mb-3"></i>
      <h5 class="text-muted font-weight-bold">Aucun visuel enregistré pour ce filtre.</h5>
      <p class="text-muted small">Cliquez sur « Synchroniser Tous les Visuels » pour initialiser les emplacements automatiquement.</p>
      <div>
        <a href="visuals.php?page_slug=all&sync=1" class="btn btn-primary btn-sm font-weight-bold">
          <i class="fas fa-sync-alt mr-1"></i> Synchroniser Maintenant
        </a>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($grouped as $pSlug => $sections): ?>
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
          <span class="badge badge-primary px-3 py-2 mr-2" style="font-size: 13px; border-radius: 6px;">
            <?php echo $pageLabels[$pSlug] ?? strtoupper((string)$pSlug); ?>
          </span>
          <span class="text-muted small font-weight-bold">
            <?php 
              $count = 0;
              foreach ($sections as $sList) $count += count($sList);
              echo $count . " visuel(s) configurable(s) sur cette page";
            ?>
          </span>
        </div>

        <?php foreach ($sections as $sectionName => $items): ?>
          <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
            <div class="card-header bg-light border-0 py-3 d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
              <h5 class="mb-0 font-weight-bold" style="font-size: 15px; color: #0c2e60;">
                <i class="fas fa-map-pin text-primary mr-2"></i> <?php echo htmlspecialchars((string)$sectionName); ?>
              </h5>
              <span class="badge badge-secondary badge-pill"><?php echo count($items); ?> image(s)</span>
            </div>

            <div class="card-body p-3 p-md-4">
              <div class="row">
                <?php foreach ($items as $item): ?>
                  <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 border shadow-sm" style="border-radius: 10px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                      <!-- Prévisualisation de l'image avec son cadrage réel -->
                      <div class="position-relative" style="height: 200px; background: #0c2e60; overflow: hidden;">
                        <?php 
                          $imgRaw = (string)($item['image_url'] ?? '');
                          $imgSrc = '../' . $imgRaw;
                          $fullPath = __DIR__ . '/../' . $imgRaw;
                          if (empty($imgRaw) || !file_exists($fullPath)) {
                              $imgSrc = '../images/bg_1.jpg';
                              $fullPath = __DIR__ . '/../images/bg_1.jpg';
                          }
                          $imgVersion = file_exists($fullPath) ? filemtime($fullPath) : (!empty($item['updated_at']) ? strtotime($item['updated_at']) : 1);
                          $focal = !empty($item['focal_point']) ? $item['focal_point'] : 'center top';
                        ?>
                        <img src="<?php echo htmlspecialchars((string)$imgSrc); ?>?v=<?php echo $imgVersion; ?>" 
                             alt="<?php echo htmlspecialchars((string)($item['title'] ?? 'Visuel')); ?>"
                             id="preview_img_<?php echo $item['id']; ?>"
                             loading="lazy"
                             decoding="async"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: <?php echo htmlspecialchars((string)$focal); ?>;">
                        
                        <div class="position-absolute" style="top: 10px; left: 10px;">
                          <span class="badge badge-dark px-2 py-1 shadow" style="background: rgba(4, 14, 30, 0.85); font-size: 11px;">
                            <code><?php echo htmlspecialchars((string)($item['section_key'] ?? '')); ?></code>
                          </span>
                        </div>

                        <div class="position-absolute" style="bottom: 10px; right: 10px;">
                          <span class="badge badge-primary px-2 py-1 shadow font-weight-bold" style="font-size: 10.5px; background: #0c2e60; border: 1px solid rgba(255,255,255,0.2);">
                            Cadrage : <?php echo htmlspecialchars((string)$focal); ?>
                          </span>
                        </div>
                      </div>

                      <!-- Formulaire de modification directe -->
                      <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <form action="visuals.php?page_slug=<?php echo urlencode((string)$filterPage); ?>" method="POST" enctype="multipart/form-data">
                          <input type="hidden" name="update_visual" value="1">
                          <input type="hidden" name="visual_id" value="<?php echo (int)$item['id']; ?>">

                          <div class="mb-2">
                            <label class="small font-weight-bold mb-1 text-dark">Titre / Rôle du Visuel</label>
                            <input type="text" name="title" class="form-control form-control-sm font-weight-bold" 
                                   value="<?php echo htmlspecialchars((string)($item['title'] ?? '')); ?>" required>
                          </div>

                          <?php if (!empty($item['description'])): ?>
                            <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.4;">
                              <?php echo htmlspecialchars((string)$item['description']); ?>
                            </p>
                          <?php endif; ?>

                          <!-- Téléversement de la nouvelle image -->
                          <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1 text-primary">
                              <i class="fas fa-camera mr-1"></i> Remplacer l'image
                            </label>
                            <input type="file" name="visual_file" class="form-control-file border p-1 rounded small" 
                                   accept="image/*"
                                   onchange="previewVisual(this, 'preview_img_<?php echo $item['id']; ?>')">
                            <small class="text-muted" style="font-size: 11px;">Formats : JPG, PNG, WEBP, SVG (Max 15 Mo)</small>
                          </div>

                          <!-- Sélecteur de cadrage (point focal) -->
                          <div class="form-group mb-3">
                            <label class="small font-weight-bold mb-1">
                              <i class="fas fa-crop-alt mr-1"></i> Point focal d'affichage
                            </label>
                            <select name="focal_point" class="form-control form-control-sm" 
                                     onchange="updateFocalPreview(this.value, 'preview_img_<?php echo $item['id']; ?>')">
                              <option value="center top" <?php echo ($focal === 'center top') ? 'selected' : ''; ?>>Haut (center top - Recommandé BTP / Toitures)</option>
                              <option value="center center" <?php echo ($focal === 'center center') ? 'selected' : ''; ?>>Centré (center center)</option>
                              <option value="center bottom" <?php echo ($focal === 'center bottom') ? 'selected' : ''; ?>>Bas (center bottom - Fondations & Sol)</option>
                            </select>
                          </div>

                          <!-- Actions de soumission -->
                          <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                              <i class="fas fa-save mr-1"></i> Enregistrer
                            </button>

                            <?php if (($item['image_url'] ?? '') !== ($item['default_url'] ?? '')): ?>
                              <button type="submit" name="reset_default" value="1" 
                                      class="btn btn-outline-secondary btn-sm" 
                                      onclick="return confirm('Réinitialiser ce visuel à son image d\'origine ?');"
                                      title="Remettre l'image par défaut d'origine">
                                <i class="fas fa-undo"></i> Réinit
                              </button>
                            <?php endif; ?>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
// Prévisualisation instantanée de l'image sélectionnée
function previewVisual(input, imgId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById(imgId);
      if (img) {
        img.src = e.target.result;
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Mise à jour instantanée du cadrage dans l'aperçu
function updateFocalPreview(focal, imgId) {
  const img = document.getElementById(imgId);
  if (img) {
    img.style.objectPosition = focal;
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
