<?php
$adminTitle = "Médiathèque Dynamique (Photos HD & Vidéos)";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

// Traitement mise à jour vidéo d'un chantier
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_project_video'])) {
    $projId = (int)($_POST['project_id'] ?? 0);
    $videoUrl = trim($_POST['video_url'] ?? '');

    // Si téléversement d'un fichier MP4 local
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $upDir = __DIR__ . '/../uploads/media/';
        if (!is_dir($upDir)) mkdir($upDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm', 'mov'])) {
            $newName = 'vid_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $dest = $upDir . $newName;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                $videoUrl = 'uploads/media/' . $newName;
            } else {
                $error = "Échec du téléversement du fichier vidéo.";
            }
        } else {
            $error = "Format vidéo non autorisé. Utilisez MP4 ou WebM.";
        }
    }

    if (empty($error) && $projId > 0) {
        $stmt = $pdo->prepare("UPDATE projects SET video_url = ? WHERE id = ?");
        $stmt->execute([$videoUrl, $projId]);
        $success = "La vidéo du chantier a été mise à jour avec succès sur le site web.";
    }
}

// Retirer la vidéo d'un projet
if (isset($_GET['action']) && $_GET['action'] === 'remove_video' && isset($_GET['project_id'])) {
    $projId = (int)$_GET['project_id'];
    $stmt = $pdo->prepare("UPDATE projects SET video_url = NULL WHERE id = ?");
    $stmt->execute([$projId]);
    $success = "La vidéo a été retirée du chantier et de la page d'accueil.";
}

// Traitement upload photo ou ajout vidéo générale dans la médiathèque
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !isset($_POST['update_project_video'])) {
    $mediaType = $_POST['media_type'] ?? 'image';
    $title = trim($_POST['title'] ?? 'Média Chantier');
    $category = trim($_POST['category'] ?? 'chantier');

    if ($mediaType === 'image' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $upDir = __DIR__ . '/../uploads/media/';
        if (!is_dir($upDir)) mkdir($upDir, 0777, true);

        $tmpName = $_FILES['media_file']['tmp_name'];
        $fileName = $_FILES['media_file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newName = 'media_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $dest = $upDir . $newName;

            if (move_uploaded_file($tmpName, $dest)) {
                $fileUrl = 'uploads/media/' . $newName;
                $fileSize = round(filesize($dest) / 1024) . ' Ko';

                $stmt = $pdo->prepare("INSERT INTO media (title, media_type, file_url, file_size, category) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, 'image', $fileUrl, $fileSize, $category]);
                $success = "Image téléversée avec succès dans la médiathèque.";
            } else {
                $error = "Échec du déplacement du fichier téléversé.";
            }
        } else {
            $error = "Format de photo non autorisé. Utilisez JPG, PNG ou WEBP.";
        }
    } elseif ($mediaType === 'video') {
        $videoUrl = trim($_POST['video_url'] ?? '');
        
        // Option 1 : Fichier vidéo téléversé (MP4, WebM)
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $upDir = __DIR__ . '/../uploads/media/';
            if (!is_dir($upDir)) mkdir($upDir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm', 'mov'])) {
                $newName = 'vid_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $dest = $upDir . $newName;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                    $videoUrl = 'uploads/media/' . $newName;
                    $fileSize = round(filesize($dest) / (1024 * 1024), 1) . ' Mo';
                    $stmt = $pdo->prepare("INSERT INTO media (title, media_type, file_url, file_size, category) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, 'video', $videoUrl, $fileSize, $category]);
                    $success = "Fichier vidéo MP4 téléversé avec succès dans la médiathèque.";
                } else {
                    $error = "Échec du téléversement du fichier vidéo.";
                }
            } else {
                $error = "Format vidéo non autorisé. Utilisez MP4 ou WebM.";
            }
        } elseif (!empty($videoUrl)) {
            // Option 2 : Lien vidéo YouTube / Drone
            $stmt = $pdo->prepare("INSERT INTO media (title, media_type, file_url, file_size, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, 'video', $videoUrl, 'Lien Externe', $category]);
            $success = "Lien vidéo intégré dans la médiathèque.";
        } else {
            $error = "Veuillez saisir un lien vidéo ou téléverser un fichier MP4.";
        }
    }
}

// Suppression d'un média
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    if ($media) {
        if ($media['media_type'] === 'image' && file_exists(__DIR__ . '/../' . $media['file_url'])) {
            @unlink(__DIR__ . '/../' . $media['file_url']);
        }
        $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        $success = "Média supprimé de la médiathèque.";
    }
}

// Récupérer les projets avec et sans vidéos
$projectsWithVideo = $pdo->query("SELECT * FROM projects WHERE video_url IS NOT NULL AND video_url != '' ORDER BY id ASC")->fetchAll();
$allProjects = $pdo->query("SELECT id, title, video_url FROM projects ORDER BY title ASC")->fetchAll();

$mediaItems = $pdo->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- ========================================================================
     1. GUIDE EXPLICATIF : OÙ SONT LES VIDÉOS & OÙ ELLES S'AFFICHENT
     ======================================================================== -->
<div class="admin-card" style="border-left: 4px solid var(--admin-accent); background: linear-gradient(to right, #ffffff, #f8fafc);">
  <div class="admin-card-body" style="padding: 20px 24px;">
    <div style="display: flex; align-items: flex-start; gap: 16px;">
      <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); display: flex; align-items: center; justify-content: center; color: var(--admin-accent); font-size: 1.5rem; flex-shrink: 0;">
        <i class="fas fa-video"></i>
      </div>
      <div style="flex-grow: 1;">
        <h3 style="font-size: 1.15rem; color: var(--admin-primary); margin-bottom: 8px; font-weight: 700;">
          Guide Pratique : Où se trouvent vos vidéos et dans quelles sections apparaissent-elles ?
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 14px; font-size: 0.88rem; line-height: 1.6;">
          <div style="background: #ffffff; padding: 14px 18px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--admin-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-desktop" style="color: var(--admin-secondary);"></i> 📍 Dans quelles sections s'affichent vos vidéos ?
            </div>
            <ul style="margin: 0; padding-left: 18px; color: #475569;">
              <li><strong>Page d'Accueil (<a href="../index.php#videos-section" target="_blank" style="color: var(--admin-secondary); text-decoration: underline;">index.php#videos-section</a>) :</strong> Grand écran vidéo 16:9 interactif + Playlist latérale des chantiers.</li>
              <li><strong>Page Réalisations (<a href="../projects.php" target="_blank" style="color: var(--admin-secondary); text-decoration: underline;">projects.php</a>) :</strong> Badge « Vidéo » sur chaque carte de chantier.</li>
              <li><strong>Fiches Détail Chantiers (<a href="../project-details.php?id=1#video" target="_blank" style="color: var(--admin-secondary); text-decoration: underline;">project-details.php</a>) :</strong> Lecteur Full HD intégré dans la fiche technique.</li>
            </ul>
          </div>
          <div style="background: #ffffff; padding: 14px 18px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--admin-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-folder-open" style="color: var(--admin-accent);"></i> 💾 Où sont-elles téléversées / stockées ?
            </div>
            <ul style="margin: 0; padding-left: 18px; color: #475569;">
              <li><strong>Fichiers MP4 / WebM téléversés :</strong> Enregistrés directement sur le serveur dans le dossier <code>feebtp/uploads/media/</code>.</li>
              <li><strong>Liens Vidéos (YouTube, Vimeo, Drone) :</strong> Enregistrés en base de données. <em>(Recommandé : aucun ralentissement du serveur et lecture instantanée sur smartphone au Cameroun).</em></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================
     2. GESTION DES VIDÉOS EN LIGNE SUR LE SITE (VOIR, TESTER, CHANGER)
     ======================================================================== -->
<div class="admin-card">
  <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h3>
      <i class="fas fa-play-circle" style="color: var(--admin-accent); margin-right: 8px;"></i>
      Vidéos Actuellement Diffusées sur le Site Web (<?php echo count($projectsWithVideo); ?> actives)
    </h3>
    <a href="../index.php#videos-section" target="_blank" class="btn btn-primary btn-sm">
      <i class="fas fa-external-link-alt"></i> Voir la Section Vidéos en Direct
    </a>
  </div>

  <div class="admin-card-body" style="padding: 0;">
    <?php if (empty($projectsWithVideo)): ?>
      <div style="padding: 30px; text-align: center; color: var(--admin-muted);">
        <p>Aucune vidéo n'est actuellement liée à vos chantiers. Utilisez le formulaire ci-dessous pour en ajouter une.</p>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 220px;">Chantier BTP</th>
            <th>Emplacements sur le site</th>
            <th>Source & Lecteur Actuel</th>
            <th>Modifier / Remplacer la Vidéo</th>
            <th style="text-align: right; width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projectsWithVideo as $pVideo): 
            $embed = parseVideoEmbed($pVideo['video_url']);
            $isLocalFile = (strpos($pVideo['video_url'], 'uploads/') === 0);
          ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                  <img src="../<?php echo htmlspecialchars($pVideo['main_image']); ?>" style="width: 60px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);" alt="">
                  <div>
                    <strong style="color: var(--admin-primary); font-size: 0.92rem; display: block;"><?php echo htmlspecialchars($pVideo['title']); ?></strong>
                    <span style="font-size: 0.78rem; color: var(--admin-muted);"><?php echo htmlspecialchars($pVideo['location']); ?></span>
                  </div>
                </div>
              </td>
              <td>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                  <span style="background: rgba(2, 132, 199, 0.12); color: var(--admin-secondary); font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; width: fit-content;">
                    <i class="fas fa-home"></i> Page d'Accueil (Grand Écran & Playlist)
                  </span>
                  <span style="background: #f1f5f9; color: var(--admin-primary); font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; width: fit-content;">
                    <i class="fas fa-file-alt"></i> Fiche Chantier #<?php echo $pVideo['id']; ?>
                  </span>
                </div>
              </td>
              <td>
                <div style="font-size: 0.84rem;">
                  <?php if ($isLocalFile): ?>
                    <span class="badge" style="background: #0284c7; color: #fff; margin-bottom: 4px;">Fichier MP4 Local</span><br>
                    <code style="font-size: 0.75rem; color: #475569;"><?php echo htmlspecialchars($pVideo['video_url']); ?></code>
                  <?php else: ?>
                    <span class="badge" style="background: #e11d48; color: #fff; margin-bottom: 4px;"><i class="fab fa-youtube"></i> YouTube / Externe</span><br>
                    <a href="<?php echo htmlspecialchars($pVideo['video_url']); ?>" target="_blank" style="font-size: 0.78rem; color: var(--admin-secondary); text-decoration: underline; word-break: break-all;">
                      <?php echo htmlspecialchars($pVideo['video_url']); ?>
                    </a>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <!-- Formulaire Inline de Changement Rapide -->
                <form action="media.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 6px;">
                  <input type="hidden" name="update_project_video" value="1">
                  <input type="hidden" name="project_id" value="<?php echo $pVideo['id']; ?>">
                  <div style="display: flex; gap: 6px;">
                    <input type="text" name="video_url" class="form-control" style="font-size: 0.8rem; padding: 4px 8px;" value="<?php echo htmlspecialchars($pVideo['video_url']); ?>" placeholder="Nouveau lien YouTube...">
                    <button type="submit" class="btn btn-secondary btn-sm" title="Enregistrer le nouveau lien" style="padding: 4px 10px; white-space: nowrap;">
                      <i class="fas fa-check"></i>
                    </button>
                  </div>
                  <div style="font-size: 0.72rem; color: var(--admin-muted);">
                    Ou téléverser MP4 : <input type="file" name="video_file" accept="video/mp4,video/webm" style="font-size: 0.72rem;">
                  </div>
                </form>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                  <a href="../project-details.php?id=<?php echo $pVideo['id']; ?>#video" target="_blank" class="action-btn" title="Voir le lecteur sur le site" style="background: #f1f5f9; color: var(--admin-primary);">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="media.php?action=remove_video&project_id=<?php echo $pVideo['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Retirer cette vidéo du site">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- Formulaire d'affectation de vidéo à un autre chantier existant -->
    <div style="padding: 18px 24px; background: #f8fafc; border-top: 1px solid var(--admin-border);">
      <h4 style="font-size: 0.95rem; color: var(--admin-primary); margin-bottom: 12px; font-weight: 700;">
        <i class="fas fa-plus-circle" style="color: var(--admin-accent); margin-right: 6px;"></i>
        Ajouter ou Activer une Vidéo sur un Autre Chantier
      </h4>
      <form action="media.php" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1.5fr 2fr 1.5fr auto; gap: 12px; align-items: end;">
        <input type="hidden" name="update_project_video" value="1">
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 0.8rem;">Choisir le Chantier</label>
          <select name="project_id" class="form-control" style="font-size: 0.85rem;" required>
            <option value="">-- Sélectionner un chantier --</option>
            <?php foreach ($allProjects as $ap): ?>
              <option value="<?php echo $ap['id']; ?>">
                <?php echo htmlspecialchars($ap['title']); ?> <?php echo !empty($ap['video_url']) ? '(Vidéo active)' : '(Sans vidéo)'; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 0.8rem;">Lien YouTube / Vimeo / Drone</label>
          <input type="text" name="video_url" class="form-control" style="font-size: 0.85rem;" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 0.8rem;">Ou Téléverser Fichier MP4</label>
          <input type="file" name="video_file" class="form-control" style="font-size: 0.8rem;" accept="video/mp4,video/webm">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 38px; white-space: nowrap;">
          <i class="fas fa-save"></i> Activer sur le site
        </button>
      </form>
    </div>

  </div>
</div>

<!-- ========================================================================
     3. TÉLÉVERSEMENT DE NOUVEAUX MÉDIAS DANS LA MÉDIATHÈQUE GLOBALE
     ======================================================================== -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="fas fa-upload" style="color: var(--admin-accent); margin-right: 8px;"></i> Téléverser des Photos HD & Vidéos Libres</h3>
  </div>

  <div class="admin-card-body">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
      
      <!-- Upload Photos -->
      <form action="media.php" method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid var(--admin-border);">
        <input type="hidden" name="media_type" value="image">
        <h4 style="font-size: 1rem; color: var(--admin-primary); margin-bottom: 12px;">
          <i class="fas fa-camera" style="color: var(--admin-accent); margin-right: 6px;"></i> Ajouter une Photo HD dans la Médiathèque
        </h4>
        <div class="form-group">
          <label>Titre / Légende de la photo</label>
          <input type="text" name="title" class="form-control" placeholder="Ex: Coulage de dalle béton R+2" required>
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <select name="category" class="form-control">
            <option value="Chantier Gros Œuvre">Chantier Gros Œuvre</option>
            <option value="Second Œuvre & Finitions">Second Œuvre & Finitions</option>
            <option value="VRD & Voiries">VRD & Voiries</option>
            <option value="Parc Matériel & Engins">Parc Matériel & Engins</option>
            <option value="Bâtiment Livré">Bâtiment Livré</option>
          </select>
        </div>
        <div class="form-group">
          <label>Sélectionner le fichier photo (JPG, PNG)</label>
          <input type="file" name="media_file" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fas fa-cloud-upload-alt"></i> Téléverser la photo
        </button>
      </form>

      <!-- Intégration Vidéos -->
      <form action="media.php" method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid var(--admin-border);">
        <input type="hidden" name="media_type" value="video">
        <h4 style="font-size: 1rem; color: var(--admin-primary); margin-bottom: 12px;">
          <i class="fas fa-video" style="color: var(--admin-accent); margin-right: 6px;"></i> Stocker une Vidéo dans la Médiathèque Générale
        </h4>
        <div class="form-group">
          <label>Titre de la vidéo</label>
          <input type="text" name="title" class="form-control" placeholder="Ex: Reportage engins et terrassement Dschang" required>
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <select name="category" class="form-control">
            <option value="Chantier Gros Œuvre">Chantier Gros Œuvre</option>
            <option value="Vue par drone">Vue par drone</option>
            <option value="Suivi de travaux">Suivi de travaux</option>
            <option value="VRD & Voiries">VRD & Voiries</option>
            <option value="Second Œuvre & Finitions">Second Œuvre & Finitions</option>
          </select>
        </div>
        <div class="form-group">
          <label>Lien URL de la vidéo (YouTube, Vimeo...)</label>
          <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="form-group">
          <label>Ou téléverser un fichier vidéo MP4 / Drone</label>
          <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">
          <i class="fas fa-save"></i> Enregistrer dans la médiathèque
        </button>
      </form>

    </div>
  </div>
</div>

<!-- Galerie des Médias -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>Médiathèque Globale (<?php echo count($mediaItems); ?> éléments)</h3>
  </div>
  <div class="admin-card-body">
    <?php if (empty($mediaItems)): ?>
      <p style="text-align: center; color: var(--admin-muted); padding: 30px;">
        Aucun média spécifique téléversé pour l'instant. Les photos du thème sont disponibles dans <code>assets/images/</code>.
      </p>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
        <?php foreach ($mediaItems as $item): ?>
          <div style="background: #ffffff; border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
            <div style="height: 140px; background: var(--admin-primary); position: relative;">
              <?php if ($item['media_type'] === 'image'): ?>
                <img src="../<?php echo htmlspecialchars($item['file_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: 
                $vEmbed = parseVideoEmbed($item['file_url']);
                if ($vEmbed && !empty($vEmbed['thumb'])):
              ?>
                <img src="<?php echo htmlspecialchars($vEmbed['thumb']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2.2rem;">
                  <i class="fas fa-play-circle" style="color: var(--admin-accent);"></i>
                </div>
              <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #fff; font-size: 2.5rem;">
                  <i class="fas fa-play-circle" style="color: var(--admin-accent);"></i>
                </div>
              <?php endif; ?>
              <?php endif; ?>
              <span style="position: absolute; bottom: 8px; left: 8px; background: rgba(0,0,0,0.7); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;">
                <?php echo htmlspecialchars($item['category']); ?>
              </span>
            </div>
            <div style="padding: 12px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
              <div>
                <h5 style="font-size: 0.88rem; color: var(--admin-primary); margin-bottom: 4px;"><?php echo htmlspecialchars($item['title']); ?></h5>
                <span style="font-size: 0.72rem; color: var(--admin-muted);"><?php echo htmlspecialchars($item['file_size']); ?> • <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></span>
              </div>
              <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--admin-border); padding-top: 8px;">
                <a href="<?php echo htmlspecialchars($item['file_url']); ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.72rem;">
                  <i class="fas fa-external-link-alt"></i> Ouvrir
                </a>
                <button type="button" class="btn btn-outline btn-sm" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($item['file_url']); ?>'); alert('Chemin copié : <?php echo htmlspecialchars($item['file_url']); ?>');" style="padding: 2px 8px; font-size: 0.72rem;">
                  <i class="fas fa-copy"></i> Copier
                </button>
                <a href="media.php?action=delete&id=<?php echo $item['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
