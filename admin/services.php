<?php
$adminTitle = "Gestion des Services & Expertises BTP";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

// Traitement ajout / modification
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_service'])) {
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    $icon = trim($_POST['icon'] ?? 'fa-hard-hat');
    $shortDesc = trim($_POST['short_desc'] ?? '');
    $fullDesc = trim($_POST['full_desc'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($title)) {
        if ($serviceId > 0) {
            $stmt = $pdo->prepare("UPDATE services SET title = ?, slug = ?, icon = ?, short_desc = ?, full_desc = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $icon, $shortDesc, $fullDesc, $sortOrder, $isActive, $serviceId]);
            $success = "Service mis à jour avec succès.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO services (title, slug, icon, short_desc, full_desc, sort_order, is_active, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $icon, $shortDesc, $fullDesc, $sortOrder, $isActive, 'images/services-1.jpg']);
            $success = "Nouveau service ajouté avec succès.";
        }
    } else {
        $error = "Le titre du service est obligatoire.";
    }
}

// Traitement suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    $success = "Service supprimé.";
}

// Édition d'un service
$editService = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editService = $stmt->fetch();
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Formulaire d'Ajout / Modification -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>
      <i class="fas <?php echo $editService ? 'fa-edit' : 'fa-plus-circle'; ?>" style="color: var(--admin-accent); margin-right: 8px;"></i>
      <?php echo $editService ? 'Modifier le Service : ' . htmlspecialchars($editService['title']) : 'Ajouter un Nouveau Service BTP'; ?>
    </h3>
    <?php if ($editService): ?>
      <a href="services.php" class="btn btn-outline btn-sm">Annuler la modification</a>
    <?php endif; ?>
  </div>

  <div class="admin-card-body">
    <form action="services.php" method="POST">
      <input type="hidden" name="save_service" value="1">
      <input type="hidden" name="service_id" value="<?php echo $editService['id'] ?? 0; ?>">

      <div class="form-grid">
        <div class="form-group">
          <label>Titre de l'Expertise / Service <span style="color:red;">*</span></label>
          <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editService['title'] ?? ''); ?>" placeholder="Ex: Travaux Sous-Marins & Forages" required>
        </div>

        <div class="form-group">
          <label>Identifiant URL (Slug)</label>
          <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($editService['slug'] ?? ''); ?>" placeholder="Ex: travaux-sous-marins">
        </div>

        <div class="form-group">
          <label>Icône FontAwesome</label>
          <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($editService['icon'] ?? 'fa-hard-hat'); ?>" placeholder="Ex: fa-hard-hat, fa-road, fa-tools">
        </div>

        <div class="form-group">
          <label>Ordre d'affichage (1, 2, 3...)</label>
          <input type="number" name="sort_order" class="form-control" value="<?php echo htmlspecialchars($editService['sort_order'] ?? '0'); ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Résumé Court (Affiché sur la page d'accueil)</label>
        <textarea name="short_desc" class="form-control" rows="2"><?php echo htmlspecialchars($editService['short_desc'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label>Description Détaillée (Page Services)</label>
        <textarea name="full_desc" class="form-control" rows="4"><?php echo htmlspecialchars($editService['full_desc'] ?? ''); ?></textarea>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.92rem;">
          <input type="checkbox" name="is_active" value="1" <?php echo (!isset($editService) || $editService['is_active']) ? 'checked' : ''; ?>>
          <span>Actif et visible sur le site public</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?php echo $editService ? 'Mettre à jour le service' : 'Enregistrer le nouveau service'; ?>
      </button>
    </form>
  </div>
</div>

<!-- Liste des Services Existants -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>Services Actuellement en Ligne (<?php echo count($services); ?>)</h3>
  </div>
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Ordre</th>
          <th>Icône</th>
          <th>Titre du Service</th>
          <th>Identifiant (Slug)</th>
          <th>Statut</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s): ?>
          <tr>
            <td><strong>#<?php echo $s['sort_order']; ?></strong></td>
            <td>
              <div style="width: 36px; height: 36px; border-radius: 6px; background: #fff3ec; color: var(--admin-accent); display: flex; align-items: center; justify-content: center;">
                <i class="fas <?php echo htmlspecialchars($s['icon'] ?? 'fa-hard-hat'); ?>"></i>
              </div>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($s['title']); ?></strong>
              <div style="font-size: 0.78rem; color: var(--admin-muted);"><?php echo htmlspecialchars(substr($s['short_desc'], 0, 70)) . '...'; ?></div>
            </td>
            <td><code><?php echo htmlspecialchars($s['slug']); ?></code></td>
            <td>
              <span class="badge <?php echo $s['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                <?php echo $s['is_active'] ? 'Actif' : 'Inactif'; ?>
              </span>
            </td>
            <td style="text-align: right;">
              <a href="services.php?edit=<?php echo $s['id']; ?>" class="action-btn" title="Modifier">
                <i class="fas fa-edit"></i>
              </a>
              <a href="services.php?action=delete&id=<?php echo $s['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer">
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
