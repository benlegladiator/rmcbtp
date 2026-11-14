<?php
$adminTitle = "Gestion des Chantiers & Réalisations BTP";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

// Traitement formulaire
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_project'])) {
    $projectId = (int)($_POST['project_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    $category = trim($_POST['category'] ?? 'Résidentiel');
    $clientName = trim($_POST['client_name'] ?? '');
    $location = trim($_POST['location'] ?? 'Yaoundé, Cameroun');
    $duration = trim($_POST['duration'] ?? '');
    $surface = trim($_POST['surface'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $status = trim($_POST['status'] ?? 'Terminé');
    $shortDesc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Gestion image upload
    $mainImage = $_POST['current_main_image'] ?? 'images/project-1.jpg';
    if (isset($_FILES['main_image_file']) && $_FILES['main_image_file']['error'] === UPLOAD_ERR_OK) {
        $upDir = __DIR__ . '/../uploads/projects/';
        if (!is_dir($upDir)) mkdir($upDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['main_image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newName = 'proj_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['main_image_file']['tmp_name'], $upDir . $newName)) {
                $mainImage = 'uploads/projects/' . $newName;
            }
        }
    }

    if (!empty($title)) {
        if ($projectId > 0) {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, slug = ?, category = ?, client_name = ?, location = ?, duration = ?, surface = ?, budget = ?, status = ?, short_desc = ?, description = ?, main_image = ?, video_url = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $category, $clientName, $location, $duration, $surface, $budget, $status, $shortDesc, $description, $mainImage, $videoUrl, $isFeatured, $projectId]);
            $targetId = $projectId;
            $success = "Projet mis à jour avec succès.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, client_name, location, duration, surface, budget, status, short_desc, description, main_image, video_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $clientName, $location, $duration, $surface, $budget, $status, $shortDesc, $description, $mainImage, $videoUrl, $isFeatured]);
            $targetId = (int)$pdo->lastInsertId();
            $success = "Nouveau chantier enregistré avec succès.";
        }

        // Traitement de l'envoi de photos multiples pour la galerie (défilement automatique)
        if ($targetId > 0 && isset($_FILES['gallery_files']) && !empty($_FILES['gallery_files']['name'][0])) {
            $upDir = __DIR__ . '/../uploads/projects/';
            if (!is_dir($upDir)) @mkdir($upDir, 0777, true);
            $fileCount = count($_FILES['gallery_files']['name']);
            $insStmt = $pdo->prepare("INSERT INTO project_gallery (project_id, image_url, caption, created_at) VALUES (?, ?, ?, NOW())");
            $uploadedGalleryCount = 0;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['gallery_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gallery_files']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $galName = 'gal_' . $targetId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                        if (move_uploaded_file($_FILES['gallery_files']['tmp_name'][$i], $upDir . $galName)) {
                            $insStmt->execute([$targetId, 'uploads/projects/' . $galName, '']);
                            $uploadedGalleryCount++;
                        }
                    }
                }
            }
            if ($uploadedGalleryCount > 0) {
                $success .= " (" . $uploadedGalleryCount . " photo(s) ajoutée(s) à la galerie défilante)";
            }
        }
    } else {
        $error = "Le titre du projet est requis.";
    }
}

// Suppression d'une image spécifique de la galerie
if (isset($_GET['action']) && $_GET['action'] === 'delete_gallery_image') {
    $imgId = (int)($_GET['image_id'] ?? 0);
    $pId = (int)($_GET['project_id'] ?? 0);
    if ($imgId > 0) {
        $stmtImg = $pdo->prepare("SELECT image_url FROM project_gallery WHERE id = ?");
        $stmtImg->execute([$imgId]);
        $rowImg = $stmtImg->fetch();
        if ($rowImg && !empty($rowImg['image_url'])) {
            $fPath = __DIR__ . '/../' . ltrim(preg_replace('/\?.*$/', '', $rowImg['image_url']), '/');
            if (file_exists($fPath) && strpos($fPath, 'uploads/projects/') !== false) {
                @unlink($fPath);
            }
        }
        $pdo->prepare("DELETE FROM project_gallery WHERE id = ?")->execute([$imgId]);
        header("Location: projects.php?edit=" . $pId . "&deleted_img=1");
        exit;
    }
}

// Suppression globale d'un projet
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM project_gallery WHERE project_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
    $success = "Chantier et sa galerie supprimés avec succès.";
}

// Mode édition
$editProj = null;
$galleryImages = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProj = $stmt->fetch();
    if ($editProj) {
        $stmtG = $pdo->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY id DESC");
        $stmtG->execute([$editProj['id']]);
        $galleryImages = $stmtG->fetchAll();
    }
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Formulaire d'Ajout ou Édition -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>
      <i class="fas <?php echo $editProj ? 'fa-edit' : 'fa-plus-circle'; ?>" style="color: var(--admin-accent); margin-right: 8px;"></i>
      <?php echo $editProj ? 'Modifier le Chantier : ' . htmlspecialchars($editProj['title']) : 'Enregistrer une Nouvelle Réalisation BTP'; ?>
    </h3>
    <?php if ($editProj): ?>
      <a href="projects.php" class="btn btn-outline btn-sm">Annuler</a>
    <?php endif; ?>
  </div>

  <div class="admin-card-body">
    <form action="projects.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="save_project" value="1">
      <input type="hidden" name="project_id" value="<?php echo $editProj['id'] ?? 0; ?>">
      <input type="hidden" name="current_main_image" value="<?php echo htmlspecialchars($editProj['main_image'] ?? 'assets/images/project-1.jpg'); ?>">

      <div class="form-grid">
        <div class="form-group">
          <label>Titre de l'Ouvrage / Projet <span style="color:red;">*</span></label>
          <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editProj['title'] ?? ''); ?>" placeholder="Ex: Immeuble R+4 Les Bambous" required>
        </div>

        <div class="form-group">
          <label>Catégorie</label>
          <select name="category" class="form-control">
            <option value="Résidentiel" <?php echo (($editProj['category'] ?? '') === 'Résidentiel') ? 'selected' : ''; ?>>Résidentiel</option>
            <option value="Commercial" <?php echo (($editProj['category'] ?? '') === 'Commercial') ? 'selected' : ''; ?>>Commercial</option>
            <option value="Travaux Publics / VRD" <?php echo (($editProj['category'] ?? '') === 'Travaux Publics / VRD') ? 'selected' : ''; ?>>Travaux Publics / VRD</option>
            <option value="Industriel" <?php echo (($editProj['category'] ?? '') === 'Industriel') ? 'selected' : ''; ?>>Industriel</option>
          </select>
        </div>

        <div class="form-group">
          <label>Maître d'Ouvrage (Client)</label>
          <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($editProj['client_name'] ?? ''); ?>" placeholder="Ex: M. Kenne / Société...">
        </div>

        <div class="form-group">
          <label>Localisation</label>
          <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($editProj['location'] ?? 'Dschang, Cameroun'); ?>" placeholder="Ex: Dschang, Quartier Foto">
        </div>

        <div class="form-group">
          <label>Délai d'exécution</label>
          <input type="text" name="duration" class="form-control" value="<?php echo htmlspecialchars($editProj['duration'] ?? ''); ?>" placeholder="Ex: 6 Mois">
        </div>

        <div class="form-group">
          <label>Surface Bâtie</label>
          <input type="text" name="surface" class="form-control" value="<?php echo htmlspecialchars($editProj['surface'] ?? ''); ?>" placeholder="Ex: 850 m²">
        </div>

        <div class="form-group">
          <label>Budget Indicatif</label>
          <input type="text" name="budget" class="form-control" value="<?php echo htmlspecialchars($editProj['budget'] ?? ''); ?>" placeholder="Ex: 85 000 000 FCFA">
        </div>

        <div class="form-group">
          <label>Statut</label>
          <select name="status" class="form-control">
            <option value="Terminé" <?php echo (($editProj['status'] ?? '') === 'Terminé') ? 'selected' : ''; ?>>Terminé</option>
            <option value="En cours" <?php echo (($editProj['status'] ?? '') === 'En cours') ? 'selected' : ''; ?>>En cours</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Résumé Rapide (Carte)</label>
        <textarea name="short_desc" class="form-control" rows="2"><?php echo htmlspecialchars($editProj['short_desc'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label>Descriptif Technique Complet (Fiche Projet)</label>
        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($editProj['description'] ?? ''); ?></textarea>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label>Photo Principale (Téléverser un nouveau fichier)</label>
          <input type="file" name="main_image_file" class="form-control" accept="image/*">
          <?php if (!empty($editProj['main_image'])): ?>
            <div style="margin-top: 8px;">
              <small style="color: var(--admin-muted);">Photo actuelle :</small><br>
              <img src="../<?php echo htmlspecialchars($editProj['main_image']); ?>" style="height: 60px; border-radius: 4px; border: 1px solid var(--admin-border); margin-top: 4px;">
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Lien Vidéo YouTube / Visite Drone (Optionnel)</label>
          <input type="text" name="video_url" class="form-control" value="<?php echo htmlspecialchars($editProj['video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
        </div>
      </div>

      <!-- Galerie Photos Multiples (Carrousel Défilant Automatique) -->
      <div class="form-group" style="background: rgba(12, 46, 96, 0.04); border: 2px dashed #0c2e60; padding: 18px; border-radius: 10px; margin-bottom: 22px;">
        <label style="font-weight: 800; color: #0c2e60; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
          <i class="fas fa-images text-primary"></i> Galerie de Photos Multiples (Défilement automatique sur le site)
        </label>
        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 12px; line-height: 1.5;">
          Sélectionnez plusieurs photos en une seule fois (maintenez Ctrl ou Shift pour en choisir plusieurs). Toutes ces photos <strong>défileront automatiquement</strong> dans le carrousel animé de la carte sur l'accueil, dans le catalogue et sur la fiche complète du chantier.
        </p>
        <input type="file" name="gallery_files[]" class="form-control" multiple accept="image/*">
        
        <?php if (!empty($galleryImages)): ?>
          <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 14px;">
            <label style="font-weight: 700; font-size: 0.9rem; color: #0f172a;">
              Photos déjà enregistrées dans la galerie défilante (<?= count($galleryImages) ?> photos) :
            </label>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px;">
              <?php foreach ($galleryImages as $gImg): ?>
                <div style="position: relative; width: 110px; height: 85px; border-radius: 8px; overflow: hidden; border: 2px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
                  <img src="../<?= htmlspecialchars(resolveImage($gImg['image_url'])) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <a href="projects.php?action=delete_gallery_image&image_id=<?= $gImg['id'] ?>&project_id=<?= $editProj['id'] ?>" 
                     onclick="return confirm('Supprimer définitivement cette photo de la galerie ?');" 
                     style="position: absolute; top: 4px; right: 4px; background: rgba(220, 38, 38, 0.95); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.92rem;">
          <input type="checkbox" name="is_featured" value="1" <?php echo (!isset($editProj) || $editProj['is_featured']) ? 'checked' : ''; ?>>
          <span>Mettre en avant sur la page d'accueil</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?php echo $editProj ? 'Mettre à jour le chantier' : 'Enregistrer le chantier BTP'; ?>
      </button>
    </form>
  </div>
</div>

<!-- Liste des Projets -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>Tous les Chantiers BTP Enregistrés (<?php echo count($projects); ?>)</h3>
  </div>
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Visuel</th>
          <th>Titre du Projet</th>
          <th>Catégorie</th>
          <th>Lieu</th>
          <th>Statut</th>
          <th>Vidéo</th>
          <th>Accueil</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $p): 
          $gCount = (int)$pdo->query("SELECT COUNT(*) FROM project_gallery WHERE project_id = " . (int)$p['id'])->fetchColumn();
        ?>
          <tr>
            <td>
              <div style="position: relative; width: 50px; height: 50px;">
                <img src="../<?php echo htmlspecialchars(resolveImage($p['main_image'])); ?>" style="width: 100%; height: 100%; border-radius: 6px; object-fit: cover;">
                <?php if ($gCount > 0): ?>
                  <span style="position: absolute; bottom: -4px; right: -4px; background: #10b981; color: #fff; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 10px; border: 1px solid #fff;" title="<?= $gCount ?> photo(s) dans le carrousel défilant">
                    +<?= $gCount ?>
                  </span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($p['title']); ?></strong>
              <div style="font-size: 0.78rem; color: var(--admin-muted);"><?php echo htmlspecialchars($p['client_name'] ?? ''); ?></div>
            </td>
            <td><?php echo htmlspecialchars($p['category']); ?></td>
            <td><?php echo htmlspecialchars($p['location']); ?></td>
            <td>
              <span class="badge <?php echo ($p['status'] === 'Terminé') ? 'badge-active' : 'badge-devis'; ?>">
                <?php echo htmlspecialchars($p['status']); ?>
              </span>
            </td>
            <td>
              <?php if (!empty($p['video_url'])): ?>
                <a href="media.php" title="Gérer dans la médiathèque" class="badge" style="background: rgba(249,115,22,0.12); color: var(--admin-accent); text-decoration: none;">
                  <i class="fas fa-play-circle"></i> En Ligne
                </a>
              <?php else: ?>
                <span style="font-size: 0.78rem; color: var(--admin-muted);">Aucune</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($p['is_featured']): ?>
                <span style="color: #f59e0b;"><i class="fas fa-star"></i> Oui</span>
              <?php else: ?>
                <span style="color: var(--admin-muted);">Non</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <a href="../project-details.php?id=<?php echo $p['id']; ?>" target="_blank" class="action-btn" title="Voir fiche publique">
                <i class="fas fa-eye"></i>
              </a>
              <a href="projects.php?edit=<?php echo $p['id']; ?>" class="action-btn" title="Modifier">
                <i class="fas fa-edit"></i>
              </a>
              <a href="projects.php?action=delete&id=<?php echo $p['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
