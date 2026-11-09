<?php
$adminTitle = "Gestionnaire de Visuels de Repère par Section";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

initPageVisualsTable($pdo);

// Traitement de la mise à jour d'un visuel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_visual'])) {
    $visualId = (int)($_POST['visual_id'] ?? 0);
    $focalPoint = trim($_POST['focal_point'] ?? 'center top');
    $title = trim($_POST['title'] ?? '');

    if ($visualId > 0) {
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
                $success = "Le visuel « " . htmlspecialchars($current['title']) . " » a été réinitialisé à son image d'origine.";
            } else {
                // Si téléversement d'un nouveau fichier image
                if (isset($_FILES['visual_file']) && $_FILES['visual_file']['error'] === UPLOAD_ERR_OK) {
                    $upDir = __DIR__ . '/../uploads/visuals/';
                    if (!is_dir($upDir)) {
                        mkdir($upDir, 0777, true);
                    }

                    $ext = strtolower(pathinfo($_FILES['visual_file']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $filename = 'vis_' . $current['page_slug'] . '_' . $current['section_key'] . '_' . time() . '.' . $ext;
                        $dest = $upDir . $filename;

                        if (move_uploaded_file($_FILES['visual_file']['tmp_name'], $dest)) {
                            $newImageUrl = 'uploads/visuals/' . $filename;
                        } else {
                            $error = "Échec du déplacement de l'image téléversée.";
                        }
                    } else {
                        $error = "Format d'image non valide. Utilisez JPG, PNG ou WEBP.";
                    }
                }

                if (empty($error)) {
                    $stmtUp = $pdo->prepare("UPDATE page_visuals SET image_url = ?, focal_point = ?, title = COALESCE(NULLIF(?, ''), title) WHERE id = ?");
                    $stmtUp->execute([$newImageUrl, $focalPoint, $title, $visualId]);
                    $success = "Le visuel « " . htmlspecialchars($title ?: $current['title']) . " » a été mis à jour avec succès sur le site en direct.";
                }
            }
        } else {
            $error = "Visuel introuvable.";
        }
    }
}

// Filtre par page active
$filterPage = $_GET['page_slug'] ?? 'all';
if ($filterPage !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM page_visuals WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$filterPage]);
} else {
    $stmt = $pdo->query("SELECT * FROM page_visuals ORDER BY page_slug ASC, sort_order ASC, id ASC");
}
$visuals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regrouper par page et par section pour l'affichage
$grouped = [];
foreach ($visuals as $v) {
    $p = $v['page_slug'];
    $s = $v['section_name'];
    if (!isset($grouped[$p])) $grouped[$p] = [];
    if (!isset($grouped[$p][$s])) $grouped[$p][$s] = [];
    $grouped[$p][$s][] = $v;
}

$pageLabels = [
    'all' => 'Tous les Visuels',
    'home' => "🏠 Page d'Accueil (index.php)",
    'about' => "🏢 Page À Propos (about.php)",
    'services' => "🛠️ Page Services (services.php)",
    'projects' => "🏗️ Page Projets (project.php)",
    'contact' => "📞 Page Contact (contact.php)"
];
?>

<div class="admin-content-inner">
  <div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h1 class="page-title mb-1" style="font-size: 1.7rem; font-weight: 800; color: #0c2e60;">
        <i class="fas fa-layer-group text-warning mr-2"></i> Visuels de Repère par Page & Section
      </h1>
      <p class="text-muted mb-0 small">
        Identifiez et remplacez les photos clés de chaque section sans toucher au code HTML. Les modifications s'affichent instantanément en direct sur le site.
      </p>
    </div>
    <div class="mt-2 mt-md-0">
      <a href="../index.php" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold">
        <i class="fas fa-external-link-alt mr-1"></i> Voir le Site en Direct
      </a>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #10b981;">
      <i class="fas fa-check-circle mr-2 text-success"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #ef4444;">
      <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> <?php echo $error; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Barre d'onglets de filtrage par page -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-2">
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($pageLabels as $slug => $label): ?>
          <a href="visuals.php?page_slug=<?php echo $slug; ?>" 
             class="btn btn-sm <?php echo ($filterPage === $slug) ? 'btn-primary font-weight-bold' : 'btn-light text-dark'; ?>"
             style="border-radius: 8px; margin: 3px;">
            <?php echo $label; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Liste des sections et cartes visuelles -->
  <?php if (empty($grouped)): ?>
    <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px;">
      <i class="fas fa-images fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">Aucun visuel enregistré pour ce filtre.</h5>
    </div>
  <?php else: ?>
    <?php foreach ($grouped as $pSlug => $sections): ?>
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
          <span class="badge badge-primary px-3 py-2 mr-2" style="font-size: 13px; border-radius: 6px;">
            <?php echo $pageLabels[$pSlug] ?? strtoupper($pSlug); ?>
          </span>
          <span class="text-muted small">
            <?php 
              $count = 0;
              foreach ($sections as $sList) $count += count($sList);
              echo $count . " visuel(s) configurable(s)";
            ?>
          </span>
        </div>

        <?php foreach ($sections as $sectionName => $items): ?>
          <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
            <div class="card-header bg-light border-0 py-3 d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
              <h5 class="mb-0 font-weight-bold" style="font-size: 15px; color: #0c2e60;">
                <i class="fas fa-map-pin text-warning mr-2"></i> <?php echo htmlspecialchars($sectionName); ?>
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
                          $imgSrc = '../' . $item['image_url'];
                          if (!file_exists(__DIR__ . '/../' . $item['image_url'])) {
                              $imgSrc = '../images/bg_1.jpg';
                          }
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>?v=<?php echo time(); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                             id="preview_img_<?php echo $item['id']; ?>"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: <?php echo htmlspecialchars($item['focal_point'] ?: 'center top'); ?>;">
                        
                        <div class="position-absolute" style="top: 10px; left: 10px;">
                          <span class="badge badge-dark px-2 py-1 shadow" style="background: rgba(4, 14, 30, 0.85); font-size: 11px;">
                            <?php echo htmlspecialchars($item['section_key']); ?>
                          </span>
                        </div>

                        <div class="position-absolute" style="bottom: 10px; right: 10px;">
                          <span class="badge badge-warning px-2 py-1 shadow font-weight-bold" style="font-size: 10.5px;">
                            Cadrage : <?php echo htmlspecialchars($item['focal_point'] ?: 'center top'); ?>
                          </span>
                        </div>
                      </div>

                      <!-- Formulaire de modification directe -->
                      <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <form action="visuals.php?page_slug=<?php echo urlencode($filterPage); ?>" method="POST" enctype="multipart/form-data">
                          <input type="hidden" name="update_visual" value="1">
                          <input type="hidden" name="visual_id" value="<?php echo $item['id']; ?>">

                          <div class="mb-2">
                            <label class="small font-weight-bold mb-1 text-dark">Titre / Rôle du Visuel</label>
                            <input type="text" name="title" class="form-control form-control-sm font-weight-bold" 
                                   value="<?php echo htmlspecialchars($item['title']); ?>" required>
                          </div>

                          <?php if (!empty($item['description'])): ?>
                            <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.4;">
                              <?php echo htmlspecialchars($item['description']); ?>
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
                            <small class="text-muted" style="font-size: 11px;">Formats : JPG, PNG, WEBP (Max 15 Mo)</small>
                          </div>

                          <!-- Sélecteur de cadrage (point focal) -->
                          <div class="form-group mb-3">
                            <label class="small font-weight-bold mb-1">
                              <i class="fas fa-crop-alt mr-1"></i> Point focal d'affichage
                            </label>
                            <select name="focal_point" class="form-control form-control-sm" 
                                    onchange="updateFocalPreview(this.value, 'preview_img_<?php echo $item['id']; ?>')">
                              <option value="center top" <?php echo ($item['focal_point'] === 'center top') ? 'selected' : ''; ?>>Haut (center top - Recommandé BTP / Toitures)</option>
                              <option value="center center" <?php echo ($item['focal_point'] === 'center center') ? 'selected' : ''; ?>>Centré (center center)</option>
                              <option value="center bottom" <?php echo ($item['focal_point'] === 'center bottom') ? 'selected' : ''; ?>>Bas (center bottom - Fondations & Sol)</option>
                            </select>
                          </div>

                          <!-- Actions de soumission -->
                          <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                              <i class="fas fa-save mr-1"></i> Enregistrer
                            </button>

                            <?php if ($item['image_url'] !== $item['default_url']): ?>
                              <button type="submit" name="reset_default" value="1" 
                                      class="btn btn-outline-secondary btn-sm" 
                                      onclick="return confirm('Réinitialiser ce visuel à son image d\'origine ?');"
                                      title="Remettre l'image par défaut d'origine">
                                <i class="fas fa-undo"></i> Réinitialiser
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
