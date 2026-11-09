<?php
$adminTitle = "Gestion des Avis & Témoignages Clients";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_test'])) {
    $testId = (int)($_POST['test_id'] ?? 0);
    $clientName = trim($_POST['client_name'] ?? '');
    $clientTitle = trim($_POST['client_title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($clientName) && !empty($comment)) {
        if ($testId > 0) {
            $stmt = $pdo->prepare("UPDATE testimonials SET client_name = ?, client_title = ?, company = ?, comment = ?, rating = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$clientName, $clientTitle, $company, $comment, $rating, $isActive, $testId]);
            $success = "Témoignage mis à jour avec succès.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, client_title, company, comment, rating, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$clientName, $clientTitle, $company, $comment, $rating, $isActive]);
            $success = "Nouveau témoignage ajouté.";
        }
    } else {
        $error = "Le nom du client et son commentaire sont obligatoires.";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
    $success = "Témoignage supprimé.";
}

$editTest = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editTest = $stmt->fetch();
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3>
      <i class="fas <?php echo $editTest ? 'fa-edit' : 'fa-plus-circle'; ?>" style="color: var(--admin-accent); margin-right: 8px;"></i>
      <?php echo $editTest ? 'Modifier l\'Avis' : 'Ajouter un Nouvel Avis Client'; ?>
    </h3>
    <?php if ($editTest): ?>
      <a href="testimonials.php" class="btn btn-outline btn-sm">Annuler</a>
    <?php endif; ?>
  </div>

  <div class="admin-card-body">
    <form action="testimonials.php" method="POST">
      <input type="hidden" name="save_test" value="1">
      <input type="hidden" name="test_id" value="<?php echo $editTest['id'] ?? 0; ?>">

      <div class="form-grid">
        <div class="form-group">
          <label>Nom du Client / Partenaire <span style="color:red;">*</span></label>
          <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($editTest['client_name'] ?? ''); ?>" placeholder="Ex: M. Jean-Paul Kamga" required>
        </div>

        <div class="form-group">
          <label>Titre / Fonction</label>
          <input type="text" name="client_title" class="form-control" value="<?php echo htmlspecialchars($editTest['client_title'] ?? ''); ?>" placeholder="Ex: Promoteur Immobilier">
        </div>

        <div class="form-group">
          <label>Entreprise / Chantier</label>
          <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($editTest['company'] ?? ''); ?>" placeholder="Ex: Résidence Les Palmiers Dschang">
        </div>

        <div class="form-group">
          <label>Note (1 à 5 Étoiles)</label>
          <select name="rating" class="form-control">
            <option value="5" <?php echo (($editTest['rating'] ?? 5) == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5/5)</option>
            <option value="4" <?php echo (($editTest['rating'] ?? 5) == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4/5)</option>
            <option value="3" <?php echo (($editTest['rating'] ?? 5) == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3/5)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Commentaire du client <span style="color:red;">*</span></label>
        <textarea name="comment" class="form-control" rows="3" required><?php echo htmlspecialchars($editTest['comment'] ?? ''); ?></textarea>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" name="is_active" value="1" <?php echo (!isset($editTest) || $editTest['is_active']) ? 'checked' : ''; ?>>
          <span>Afficher sur la page d'accueil</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Enregistrer le témoignage
      </button>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h3>Tous les Avis Clients (<?php echo count($testimonials); ?>)</h3>
  </div>
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Client</th>
          <th>Titre / Entreprise</th>
          <th>Note</th>
          <th>Avis</th>
          <th>Statut</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($testimonials as $t): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($t['client_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($t['client_title'] ?? ''); ?> • <?php echo htmlspecialchars($t['company'] ?? ''); ?></td>
            <td style="color: #f59e0b;"><?php echo str_repeat('★', $t['rating']); ?></td>
            <td style="font-size: 0.82rem; color: var(--admin-muted);"><?php echo htmlspecialchars(substr($t['comment'], 0, 70)) . '...'; ?></td>
            <td>
              <span class="badge <?php echo $t['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                <?php echo $t['is_active'] ? 'Affiché' : 'Masqué'; ?>
              </span>
            </td>
            <td style="text-align: right;">
              <a href="testimonials.php?edit=<?php echo $t['id']; ?>" class="action-btn" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="testimonials.php?action=delete&id=<?php echo $t['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
