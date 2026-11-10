<?php
require_once 'config.php';
require_login();

$today = date('Y-m-d');
$msg = '';
$msg_type = 'success';

// ----------------------------------------------------------------------------
// TRAITEMENT DU POINTAGE EN 1-CLIC
// ----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // 1. Pointage Arrivée en 1 Clic
    if ($action === 'pointer_arrivee') {
        $personnel_id = (int)($_POST['personnel_id'] ?? 0);
        $custom_time = trim($_POST['custom_time'] ?? '') ?: date('H:i:s');
        
        if ($personnel_id > 0) {
            // Vérifier s'il a déjà pointé aujourd'hui
            $checkStmt = $pdo->prepare("SELECT id FROM pointages WHERE personnel_id = ? AND date_pointage = ?");
            $checkStmt->execute([$personnel_id, $today]);
            $existing = $checkStmt->fetch();

            if (!$existing) {
                // Récupérer l'heure normale de début
                $empStmt = $pdo->prepare("SELECT fullname, normal_start_time FROM personnel WHERE id = ?");
                $empStmt->execute([$personnel_id]);
                $emp = $empStmt->fetch();

                $is_late = 0;
                if ($emp && !empty($emp['normal_start_time'])) {
                    $normalTime = strtotime($emp['normal_start_time']);
                    $actualTime = strtotime(substr($custom_time, 0, 5));
                    if ($actualTime > $normalTime + (10 * 60)) { // 10 min de tolérance
                        $is_late = 1;
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO pointages (personnel_id, date_pointage, heure_arrivee, is_late, status)
                    VALUES (?, ?, ?, ?, 'present')
                ");
                $stmt->execute([$personnel_id, $today, $custom_time, $is_late]);
                $msg = "Arrivée de " . htmlspecialchars($emp['fullname']) . " pointée à " . substr($custom_time, 0, 5) . ($is_late ? " (⚠️ En retard)" : " (À l'heure)");
            } else {
                $msg = "Cet employé a déjà été pointé à l'arrivée aujourd'hui.";
                $msg_type = 'warning';
            }
        }
    }

    // 2. Pointage Départ en 1 Clic
    elseif ($action === 'pointer_depart') {
        $pointage_id = (int)($_POST['pointage_id'] ?? 0);
        $custom_time = trim($_POST['custom_time'] ?? '') ?: date('H:i:s');

        if ($pointage_id > 0) {
            $pStmt = $pdo->prepare("
                SELECT p.*, e.fullname FROM pointages p 
                JOIN personnel e ON p.personnel_id = e.id 
                WHERE p.id = ?
            ");
            $pStmt->execute([$pointage_id]);
            $pointage = $pStmt->fetch();

            if ($pointage) {
                $tArrivee = strtotime($pointage['heure_arrivee']);
                $tDepart = strtotime($custom_time);
                $duree_minutes = max(0, round(($tDepart - $tArrivee) / 60));

                $stmt = $pdo->prepare("
                    UPDATE pointages 
                    SET heure_depart = ?, duree_minutes = ?, status = 'parti' 
                    WHERE id = ?
                ");
                $stmt->execute([$custom_time, $duree_minutes, $pointage_id]);
                $msg = "Départ de " . htmlspecialchars($pointage['fullname']) . " pointé à " . substr($custom_time, 0, 5) . " (Présence : " . format_minutes_duree($duree_minutes) . ")";
            }
        }
    }
}

// ----------------------------------------------------------------------------
// RÉCUPÉRATION DU PERSONNEL ET DE L'ÉTAT DU JOUR
// ----------------------------------------------------------------------------
$filter_dept = $_GET['dept'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT e.*, 
           p.id as pointage_id, 
           p.heure_arrivee, 
           p.heure_depart, 
           p.duree_minutes, 
           p.is_late, 
           p.status as pointage_status
    FROM personnel e
    LEFT JOIN pointages p ON e.id = p.personnel_id AND p.date_pointage = :today
    WHERE e.is_active = 1
";

$params = [':today' => $today];

if ($filter_dept !== 'all') {
    $sql .= " AND e.department = :dept";
    $params[':dept'] = $filter_dept;
}

if (!empty($search)) {
    $sql .= " AND (e.fullname LIKE :search OR e.matricule LIKE :search OR e.role_title LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY e.department ASC, e.fullname ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Filtrage en mémoire sur le statut du jour si demandé
if ($filter_status === 'present') {
    $employees = array_filter($employees, fn($e) => $e['pointage_status'] === 'present');
} elseif ($filter_status === 'parti') {
    $employees = array_filter($employees, fn($e) => $e['pointage_status'] === 'parti');
} elseif ($filter_status === 'absent') {
    $employees = array_filter($employees, fn($e) => empty($e['pointage_status']));
}

// Statistiques globales du jour
$total_effectif = $pdo->query("SELECT COUNT(*) FROM personnel WHERE is_active = 1")->fetchColumn();
$total_presents = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = '$today' AND status = 'present'")->fetchColumn();
$total_partis = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = '$today' AND status = 'parti'")->fetchColumn();
$total_pointes = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = '$today'")->fetchColumn();
$total_absents = max(0, $total_effectif - $total_pointes);
$total_retards = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = '$today' AND is_late = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Poste de Sécurité - Pointage en 1 Clic | RMC BTP SARL</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- En-tête Tactique -->
  <header class="sec-header">
    <div class="sec-header-top">
      <a href="index.php" class="sec-brand">
        <img src="img/logo-white.png" alt="RMC BTP SARL" class="sec-brand-logo" onerror="this.src='img/logo.png'">
        <div>
          <h1 class="sec-brand-title">RMC SÉCURITÉ</h1>
          <p class="sec-brand-subtitle">Pointage & Contrôle des Accès</p>
        </div>
      </a>
      
      <!-- Horloge digitale temps réel -->
      <div class="sec-live-clock">
        <span class="live-dot"></span>
        <span id="liveTime"><?php echo date('H:i:s'); ?></span>
      </div>
    </div>

    <!-- Navigation rapide -->
    <nav class="sec-nav-bar">
      <a href="index.php" class="sec-nav-link active">
        <i class="fas fa-fingerprint"></i> Pointage 1-Clic
      </a>
      <a href="personnel.php" class="sec-nav-link">
        <i class="fas fa-users-gear"></i> Recensement (<?php echo $total_effectif; ?>)
      </a>
      <a href="rapports.php" class="sec-nav-link">
        <i class="fas fa-file-waveform"></i> Rapports pour le Boss
      </a>
      <a href="historique.php" class="sec-nav-link">
        <i class="fas fa-clock-rotate-left"></i> Historique
      </a>
      <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
        <span class="sec-user-tag"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['sec_fullname'] ?? 'Gardien'); ?></span>
        <a href="logout.php" class="btn-logout-sec" title="Se déconnecter"><i class="fas fa-power-off"></i></a>
      </div>
    </nav>
  </header>

  <main class="sec-container">

    <!-- Message de confirmation -->
    <?php if (!empty($msg)): ?>
      <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: var(--radius-md); background: <?php echo $msg_type === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)'; ?>; border-left: 5px solid <?php echo $msg_type === 'success' ? '#10b981' : '#f59e0b'; ?>; color: #fff; font-weight: 700; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-bell"></i> <?php echo $msg; ?>
      </div>
    <?php endif; ?>

    <!-- Métriques Clés en Direct du Jour -->
    <section class="metrics-row">
      <div class="metric-pill">
        <div class="metric-data">
          <h4>Effectif Chantier</h4>
          <div class="metric-val"><?php echo $total_effectif; ?></div>
        </div>
        <div class="metric-icon-box" style="background: rgba(255, 255, 255, 0.05); color: #fff;">
          <i class="fas fa-id-badge"></i>
        </div>
      </div>

      <div class="metric-pill" style="border-color: rgba(16, 185, 129, 0.35);">
        <div class="metric-data">
          <h4>Présents sur Site</h4>
          <div class="metric-val" style="color: #10b981;"><?php echo $total_presents; ?></div>
        </div>
        <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
          <i class="fas fa-person-shelter"></i>
        </div>
      </div>

      <div class="metric-pill" style="border-color: rgba(2, 132, 199, 0.35);">
        <div class="metric-data">
          <h4>Déjà Sortis</h4>
          <div class="metric-val" style="color: #38bdf8;"><?php echo $total_partis; ?></div>
        </div>
        <div class="metric-icon-box" style="background: rgba(2, 132, 199, 0.15); color: #38bdf8;">
          <i class="fas fa-person-walking-arrow-right"></i>
        </div>
      </div>

      <div class="metric-pill" style="border-color: rgba(239, 68, 68, 0.35);">
        <div class="metric-data">
          <h4>Absents / Non Arrivés</h4>
          <div class="metric-val" style="color: #ef4444;"><?php echo $total_absents; ?></div>
        </div>
        <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
          <i class="fas fa-user-xmark"></i>
        </div>
      </div>

      <div class="metric-pill" style="border-color: rgba(245, 158, 11, 0.35);">
        <div class="metric-data">
          <h4>Retards Détectés</h4>
          <div class="metric-val" style="color: #f59e0b;"><?php echo $total_retards; ?></div>
        </div>
        <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
          <i class="fas fa-clock-rotate-left"></i>
        </div>
      </div>
    </section>

    <!-- Barre d'outils, Recherche et Filtres -->
    <section class="toolbar-card">
      <form action="index.php" method="GET" style="display: flex; gap: 12px; flex: 1; flex-wrap: wrap;">
        <div class="search-input-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Rechercher par nom, matricule ou métier..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <select name="dept" class="form-control-sec" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
          <option value="all">Tous les Départements</option>
          <option value="Chantier" <?php echo $filter_dept === 'Chantier' ? 'selected' : ''; ?>>Chantier BTP</option>
          <option value="Atelier" <?php echo $filter_dept === 'Atelier' ? 'selected' : ''; ?>>Atelier Métallique</option>
          <option value="Logistique" <?php echo $filter_dept === 'Logistique' ? 'selected' : ''; ?>>Logistique / Chauffeurs</option>
          <option value="Bureau" <?php echo $filter_dept === 'Bureau' ? 'selected' : ''; ?>>Bureau & Études</option>
          <option value="Sécurité" <?php echo $filter_dept === 'Sécurité' ? 'selected' : ''; ?>>Sécurité</option>
        </select>
      </form>

      <div class="filter-btn-group">
        <a href="index.php?dept=<?php echo urlencode($filter_dept); ?>&status=all" class="btn-filter <?php echo $filter_status === 'all' ? 'active' : ''; ?>">Tous</a>
        <a href="index.php?dept=<?php echo urlencode($filter_dept); ?>&status=present" class="btn-filter <?php echo $filter_status === 'present' ? 'active' : ''; ?>">
          <span style="color: #10b981;">●</span> Présents (<?php echo $total_presents; ?>)
        </a>
        <a href="index.php?dept=<?php echo urlencode($filter_dept); ?>&status=absent" class="btn-filter <?php echo $filter_status === 'absent' ? 'active' : ''; ?>">
          <span style="color: #ef4444;">●</span> Absents (<?php echo $total_absents; ?>)
        </a>
        <a href="index.php?dept=<?php echo urlencode($filter_dept); ?>&status=parti" class="btn-filter <?php echo $filter_status === 'parti' ? 'active' : ''; ?>">
          <span style="color: #38bdf8;">●</span> Sortis (<?php echo $total_partis; ?>)
        </a>
      </div>
    </section>

    <!-- Grille des Cartes de Pointage 1-Clic -->
    <section class="personnel-grid">
      <?php if (empty($employees)): ?>
        <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; color: var(--sec-text-muted); background: var(--sec-card); border-radius: var(--radius-lg);">
          <i class="fas fa-users-slash" style="font-size: 3rem; margin-bottom: 14px; opacity: 0.4;"></i>
          <h3>Aucun personnel trouvé</h3>
          <p>Essayez de modifier votre recherche ou ajoutez des employés dans le menu Recensement.</p>
        </div>
      <?php else: ?>
        <?php foreach ($employees as $emp): 
          $status = $emp['pointage_status']; // null, 'present', 'parti'
          $cardClass = 'is-absent';
          $dotClass = 'dot-gray';
          if ($status === 'present') { $cardClass = 'is-present'; $dotClass = 'dot-green'; }
          elseif ($status === 'parti') { $cardClass = 'is-parti'; $dotClass = 'dot-blue'; }

          // Initiales pour l'avatar
          $words = explode(' ', $emp['fullname']);
          $initials = '';
          foreach (array_slice($words, 0, 2) as $w) {
              $initials .= mb_substr($w, 0, 1);
          }
        ?>
          <div class="pointage-card <?php echo $cardClass; ?>">
            <div class="card-top">
              <div class="avatar-circle" style="background: <?php echo htmlspecialchars($emp['avatar_color']); ?>;">
                <?php echo strtoupper($initials); ?>
                <span class="status-indicator-dot <?php echo $dotClass; ?>"></span>
              </div>
              <div class="emp-details">
                <span class="emp-matricule"><?php echo htmlspecialchars($emp['matricule']); ?></span>
                <h3 class="emp-name"><?php echo htmlspecialchars($emp['fullname']); ?></h3>
                <div class="emp-role">
                  <i class="fas fa-hammer"></i>
                  <span><?php echo htmlspecialchars($emp['role_title']); ?> • <strong style="color:#fff;"><?php echo htmlspecialchars($emp['department']); ?></strong></span>
                </div>
              </div>
            </div>

            <!-- Horodatages et statut -->
            <div class="time-status-box">
              <div class="time-item">
                <span class="time-label">Heure Arrivée</span>
                <span class="time-val">
                  <?php if (!empty($emp['heure_arrivee'])): ?>
                    <?php echo substr($emp['heure_arrivee'], 0, 5); ?>
                    <?php if ($emp['is_late']): ?>
                      <span style="font-size: 0.68rem; color: #f59e0b;" title="Arrivé en retard">⚠️</span>
                    <?php endif; ?>
                  <?php else: ?>
                    --:--
                  <?php endif; ?>
                </span>
              </div>

              <div class="time-item">
                <span class="time-label">Heure Départ</span>
                <span class="time-val">
                  <?php echo !empty($emp['heure_depart']) ? substr($emp['heure_depart'], 0, 5) : '--:--'; ?>
                </span>
              </div>

              <div class="time-item" style="text-align: right;">
                <span class="time-label">Temps Sur Site</span>
                <span class="time-val" style="color: <?php echo $status === 'present' ? '#10b981' : ($status === 'parti' ? '#38bdf8' : 'var(--sec-text-muted)'); ?>;">
                  <?php 
                    if ($status === 'parti') {
                        echo format_minutes_duree($emp['duree_minutes']);
                    } elseif ($status === 'present') {
                        $min = round((time() - strtotime($emp['heure_arrivee'])) / 60);
                        echo format_minutes_duree($min);
                    } else {
                        echo 'Absent';
                    }
                  ?>
                </span>
              </div>
            </div>

            <!-- Action 1-Clic pour le gardien -->
            <div class="card-action">
              <?php if (empty($status)): ?>
                <!-- Bouton 1 Clic : Pointer Arrivée -->
                <form action="index.php" method="POST">
                  <input type="hidden" name="action" value="pointer_arrivee">
                  <input type="hidden" name="personnel_id" value="<?php echo $emp['id']; ?>">
                  <button type="submit" class="btn-pointage-1click btn-arrivee">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    <span>🟢 POINTER ARRIVÉE (<?php echo date('H:i'); ?>)</span>
                  </button>
                </form>

              <?php elseif ($status === 'present'): ?>
                <!-- Bouton 1 Clic : Pointer Départ -->
                <form action="index.php" method="POST">
                  <input type="hidden" name="action" value="pointer_depart">
                  <input type="hidden" name="pointage_id" value="<?php echo $emp['pointage_id']; ?>">
                  <button type="submit" class="btn-pointage-1click btn-depart">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                    <span>🔴 POINTER DÉPART (<?php echo date('H:i'); ?>)</span>
                  </button>
                </form>

              <?php else: ?>
                <!-- Déjà pointé sortie -->
                <div class="btn-pointage-1click btn-termine">
                  <i class="fas fa-check-double"></i>
                  <span>Journée Clôturée (<?php echo substr($emp['heure_arrivee'], 0, 5); ?> → <?php echo substr($emp['heure_depart'], 0, 5); ?>)</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

  </main>

  <!-- Horloge JavaScript live -->
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