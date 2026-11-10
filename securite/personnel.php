<?php
require_once 'config.php';
require_login();

$msg = '';
$error = '';

// Traitement Ajout / Modification / Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $matricule = trim($_POST['matricule'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role_title = trim($_POST['role_title'] ?? '');
        $department = trim($_POST['department'] ?? 'Chantier');
        $normal_start = trim($_POST['normal_start_time'] ?? '08:00');
        $avatar_color = trim($_POST['avatar_color'] ?? '#0284c7');

        if (empty($matricule)) {
            $nextId = $pdo->query("SELECT MAX(id) FROM personnel")->fetchColumn() + 1;
            $matricule = 'RMC-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        if (!empty($fullname) && !empty($role_title)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO personnel (matricule, fullname, phone, role_title, department, normal_start_time, avatar_color)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$matricule, $fullname, $phone, $role_title, $department, $normal_start, $avatar_color]);
                $msg = "Employé <strong>$fullname</strong> recensé avec succès (Matricule: $matricule) !";
            } catch (Exception $e) {
                $error = "Erreur : ce matricule ou cet employé existe déjà.";
            }
        } else {
            $error = "Veuillez renseigner le nom complet et le poste de l'employé.";
        }
    } elseif ($action === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 0);
        $new_status = $status === 1 ? 0 : 1;
        $pdo->prepare("UPDATE personnel SET is_active = ? WHERE id = ?")->execute([$new_status, $id]);
        $msg = "Statut d'activité mis à jour.";
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM personnel WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM pointages WHERE personnel_id = ?")->execute([$id]);
            $msg = "Employé supprimé du registre de sécurité.";
        }
    }
}

// Liste du personnel
$personnel_list = $pdo->query("SELECT * FROM personnel ORDER BY department ASC, fullname ASC")->fetchAll();
$total = count($personnel_list);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recensement du Personnel | Sécurité RMC BTP</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <header class="sec-header">
    <div class="sec-header-top">
      <a href="index.php" class="sec-brand">
        <img src="img/logo-white.png" alt="RMC BTP SARL" class="sec-brand-logo" onerror="this.src='img/logo.png'">
        <div>
          <h1 class="sec-brand-title">RMC SÉCURITÉ</h1>
          <p class="sec-brand-subtitle">Recensement & Fiches du Personnel</p>
        </div>
      </a>
      <div class="sec-live-clock">
        <span class="live-dot"></span>
        <span id="liveTime"><?php echo date('H:i:s'); ?></span>
      </div>
    </div>

    <nav class="sec-nav-bar">
      <a href="index.php" class="sec-nav-link">
        <i class="fas fa-fingerprint"></i> Pointage 1-Clic
      </a>
      <a href="personnel.php" class="sec-nav-link active">
        <i class="fas fa-users-gear"></i> Recensement (<?php echo $total; ?>)
      </a>
      <a href="rapports.php" class="sec-nav-link">
        <i class="fas fa-file-waveform"></i> Rapports pour le Boss
      </a>
      <a href="historique.php" class="sec-nav-link">
        <i class="fas fa-clock-rotate-left"></i> Historique
      </a>
      <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
        <span class="sec-user-tag"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['sec_fullname'] ?? 'Gardien'); ?></span>
        <a href="logout.php" class="btn-logout-sec"><i class="fas fa-power-off"></i></a>
      </div>
    </nav>
  </header>

  <main class="sec-container">

    <?php if (!empty($msg)): ?>
      <div style="margin-bottom: 20px; padding: 14px; border-radius: var(--radius-md); background: rgba(16, 185, 129, 0.15); border-left: 5px solid #10b981; color: #fff; font-weight: 700;">
        <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="margin-bottom: 20px; padding: 14px; border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.15); border-left: 5px solid #ef4444; color: #fff; font-weight: 700;">
        <i class="fas fa-triangle-exclamation"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: flex-start;">
      
      <!-- Formulaire d'ajout d'ouvrier / employé -->
      <div class="sec-table-card" style="padding: 22px;">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
          <i class="fas fa-user-plus" style="color: var(--sec-accent);"></i> Nouveau Personnel
        </h3>

        <form action="personnel.php" method="POST">
          <input type="hidden" name="action" value="add">

          <div class="form-group-sec">
            <label class="form-label-sec">Nom & Prénom *</label>
            <input type="text" name="fullname" class="form-control-sec" placeholder="ex: M. TCHINDA Paul" required>
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Matricule (laisser vide pour auto)</label>
            <input type="text" name="matricule" class="form-control-sec" placeholder="ex: RMC-011">
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Téléphone</label>
            <input type="text" name="phone" class="form-control-sec" placeholder="699 00 00 00">
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Fonction / Métier BTP *</label>
            <input type="text" name="role_title" class="form-control-sec" placeholder="ex: Maçon, Électricien, Chauffeur..." required>
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Département d'Affectation</label>
            <select name="department" class="form-control-sec">
              <option value="Chantier">Chantier BTP</option>
              <option value="Atelier">Atelier Métallique / Préfabrication</option>
              <option value="Logistique">Logistique & Conduite d'engins</option>
              <option value="Bureau">Bureau Technique & Études</option>
              <option value="Sécurité">Sécurité & Gardiennage</option>
            </select>
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Heure Normale de Prise de Service</label>
            <input type="time" name="normal_start_time" class="form-control-sec" value="08:00">
          </div>

          <div class="form-group-sec">
            <label class="form-label-sec">Couleur de Badge Avatar</label>
            <select name="avatar_color" class="form-control-sec">
              <option value="#0284c7">Bleu Océan</option>
              <option value="#ea580c">Orange BTP</option>
              <option value="#10b981">Vert Émeraude</option>
              <option value="#8b5cf6">Violet Moderne</option>
              <option value="#f59e0b">Ambre Doré</option>
              <option value="#ec4899">Rose Vif</option>
            </select>
          </div>

          <button type="submit" class="btn-pointage-1click btn-arrivee" style="margin-top: 10px;">
            <i class="fas fa-check"></i> Enregistrer l'Employé
          </button>
        </form>
      </div>

      <!-- Tableau du Personnel Recensé -->
      <div class="sec-table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 style="font-size: 1.2rem; font-weight: 800;">
            <i class="fas fa-address-book" style="color: var(--sec-accent);"></i> Registre Complet du Personnel (<?php echo $total; ?>)
          </h3>
        </div>

        <table class="sec-table">
          <thead>
            <tr>
              <th>Personnel</th>
              <th>Fonction</th>
              <th>Département</th>
              <th>Téléphone</th>
              <th>Heure Normale</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($personnel_list as $emp): 
              $words = explode(' ', $emp['fullname']);
              $initials = '';
              foreach (array_slice($words, 0, 2) as $w) {
                  $initials .= mb_substr($w, 0, 1);
              }
            ?>
              <tr>
                <td>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: <?php echo htmlspecialchars($emp['avatar_color']); ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; font-size: 0.85rem;">
                      <?php echo strtoupper($initials); ?>
                    </div>
                    <div>
                      <strong><?php echo htmlspecialchars($emp['fullname']); ?></strong><br>
                      <small style="color: var(--sec-accent); font-family: var(--font-mono);"><?php echo htmlspecialchars($emp['matricule']); ?></small>
                    </div>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($emp['role_title']); ?></td>
                <td>
                  <span style="font-size: 0.78rem; background: rgba(255, 255, 255, 0.06); padding: 4px 10px; border-radius: 6px;">
                    <?php echo htmlspecialchars($emp['department']); ?>
                  </span>
                </td>
                <td><small><?php echo htmlspecialchars($emp['phone'] ?: 'N/A'); ?></small></td>
                <td><small style="font-family: var(--font-mono);"><?php echo htmlspecialchars($emp['normal_start_time']); ?></small></td>
                <td>
                  <?php if ($emp['is_active']): ?>
                    <span style="color: #10b981; font-weight: 700; font-size: 0.8rem;">● Actif</span>
                  <?php else: ?>
                    <span style="color: #ef4444; font-weight: 700; font-size: 0.8rem;">● Inactif</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display: flex; gap: 6px;">
                    <form action="personnel.php" method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="toggle_active">
                      <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                      <input type="hidden" name="is_active" value="<?php echo $emp['is_active']; ?>">
                      <button type="submit" class="btn-filter" style="padding: 4px 8px; font-size: 0.75rem;" title="Activer / Désactiver">
                        <i class="fas fa-power-off"></i>
                      </button>
                    </form>

                    <form action="personnel.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer définitivement cet employé ?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                      <button type="submit" class="btn-filter" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444;" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>

  </main>

  <script>
    function updateLiveClock() {
      const now = new Date();
      const h = String(now.getHours()).padStart(2, '0');
      const m = String(now.getMinutes()).padStart(2, '0');
      const s = String(now.getSeconds()).padStart(2, '0');
      const el = document.getElementById('liveTime');
      if (el) el.innerText = `${h}:${m}:${s}`;
    }
    setInterval(updateLiveClock, 1000);
  </script>

</body>
</html>