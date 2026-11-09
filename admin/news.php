<?php
$adminTitle = "Gestion des Actualités & Blog BTP";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_article'])) {
    $articleId = (int)($_POST['article_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    $category = trim($_POST['category'] ?? 'Actualités Chantiers');
    $author = trim($_POST['author'] ?? 'Direction FEE BTP');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    $image = $_POST['current_image'] ?? 'images/image_1.jpg';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upDir = __DIR__ . '/../uploads/media/';
        if (!is_dir($upDir)) mkdir($upDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newName = 'news_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upDir . $newName)) {
                $image = 'uploads/media/' . $newName;
            }
        }
    }

    if (!empty($title)) {
        if ($articleId > 0) {
            $stmt = $pdo->prepare("UPDATE articles SET title = ?, slug = ?, category = ?, author = ?, summary = ?, content = ?, image = ?, is_published = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $category, $author, $summary, $content, $image, $isPublished, $articleId]);
            $success = "Article mis à jour avec succès.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, category, author, summary, content, image, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $author, $summary, $content, $image, $isPublished]);
            $success = "Nouvel article publié avec succès.";
        }
    } else {
        $error = "Le titre de l'article est obligatoire.";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    $success = "Article supprimé.";
}

$editArt = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editArt = $stmt->fetch();
}

$articles = $pdo->query("SELECT * FROM articles ORDER BY id DESC")->fetchAll();
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
      <i class="fas <?php echo $editArt ? 'fa-edit' : 'fa-plus-circle'; ?>" style="color: var(--admin-accent); margin-right: 8px;"></i>
      <?php echo $editArt ? 'Modifier l\'Article : ' . htmlspecialchars($editArt['title']) : 'Rédiger une Nouvelle Actualité'; ?>
    </h3>
    <?php if ($editArt): ?>
      <a href="news.php" class="btn btn-outline btn-sm">Annuler</a>
    <?php endif; ?>
  </div>

  <div class="admin-card-body">
    <form action="news.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="save_article" value="1">
      <input type="hidden" name="article_id" value="<?php echo $editArt['id'] ?? 0; ?>">
      <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editArt['image'] ?? 'images/image_1.jpg'); ?>">

      <div class="form-grid">
        <div class="form-group">
          <label>Titre de l'Article <span style="color:red;">*</span></label>
          <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editArt['title'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label>Catégorie</label>
          <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($editArt['category'] ?? 'Actualités Chantiers'); ?>">
        </div>

        <div class="form-group">
          <label>Auteur</label>
          <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($editArt['author'] ?? 'Direction FEE BTP'); ?>">
        </div>

        <div class="form-group">
          <label>Image d'illustration (Fichier)</label>
          <input type="file" name="image_file" class="form-control" accept="image/*">
        </div>
      </div>

      <div class="form-group">
        <label>Chapeau / Résumé (Affiché dans la liste)</label>
        <textarea name="summary" class="form-control" rows="2"><?php echo htmlspecialchars($editArt['summary'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label>Corps complet de l'article</label>
        <textarea name="content" class="form-control" rows="6"><?php echo htmlspecialchars($editArt['content'] ?? ''); ?></textarea>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="checkbox" name="is_published" value="1" <?php echo (!isset($editArt) || $editArt['is_published']) ? 'checked' : ''; ?>>
          <span>Publié et accessible au public</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Enregistrer l'article
      </button>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h3>Tous les Articles Publiés (<?php echo count($articles); ?>)</h3>
  </div>
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Visuel</th>
          <th>Titre de l'Article</th>
          <th>Catégorie</th>
          <th>Date</th>
          <th>Vues</th>
          <th>Statut</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $a): ?>
          <tr>
            <td><img src="../<?php echo htmlspecialchars($a['image']); ?>" style="width: 44px; height: 44px; border-radius: 6px; object-fit: cover;"></td>
            <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
            <td><?php echo htmlspecialchars($a['category']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($a['created_at'])); ?></td>
            <td><?php echo $a['views']; ?></td>
            <td>
              <span class="badge <?php echo $a['is_published'] ? 'badge-active' : 'badge-inactive'; ?>">
                <?php echo $a['is_published'] ? 'Publié' : 'Brouillon'; ?>
              </span>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <a href="../news-details.php?id=<?php echo $a['id']; ?>" target="_blank" class="action-btn" title="Voir"><i class="fas fa-eye"></i></a>
              <a href="news.php?edit=<?php echo $a['id']; ?>" class="action-btn" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="news.php?action=delete&id=<?php echo $a['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
