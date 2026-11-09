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
            $success = "Projet mis à jour avec succès.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, client_name, location, duration, surface, budget, status, short_desc, description, main_image, video_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $clientName, $location, $duration, $surface, $budget, $status, $shortDesc, $description, $mainImage, $videoUrl, $isFeatured]);
            $success = "Nouveau chantier enregistré avec succès.";
        }
    } else {
        $error = "Le titre du projet est requis.";
    }
}

// Suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
    $success = "Chantier supprimé avec succès.";
}

// Mode édition
$editProj = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProj = $stmt->fetch();
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
        <?php foreach ($projects as $p): ?>
          <tr>
            <td>
              <img src="../<?php echo htmlspecialchars($p['main_image']); ?>" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover;">
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
