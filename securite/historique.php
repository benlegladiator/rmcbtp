<?php
require_once 'config.php';
require_login();

// Filtres
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');
$personnel_id = (int)($_GET['personnel_id'] ?? 0);

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=pointages_securite_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Matricule', 'Nom Complet', 'Fonction', 'Departement', 'Date', 'Arrivee', 'Depart', 'Duree (min)', 'Retard', 'Statut']);

    $stmtExp = $pdo->prepare("
        SELECT e.matricule, e.fullname, e.role_title, e.department,
               p.date_pointage, p.heure_arrivee, p.heure_depart, p.duree_minutes, p.is_late, p.status
        FROM pointages p
        JOIN personnel e ON p.personnel_id = e.id
        WHERE p.date_pointage BETWEEN ? AND ?
        ORDER BY p.date_pointage DESC, p.heure_arrivee ASC
    ");
    $stmtExp->execute([$date_debut, $date_fin]);
    while ($row = $stmtExp->fetch()) {
        fputcsv($output, [
            $row['matricule'],
            $row['fullname'],
            $row['role_title'],
            $row['department'],
            $row['date_pointage'],
            $row['heure_arrivee'],
            $row['heure_depart'] ?? '-',
            $row['duree_minutes'],
            $row['is_late'] ? 'Oui' : 'Non',
            $row['status']
        ]);
    }
    fclose($output);
    exit;
}

// Requête standard
$sql = "
    SELECT p.*, e.matricule, e.fullname, e.role_title, e.department, e.avatar_color
    FROM pointages p
    JOIN personnel e ON p.personnel_id = e.id
    WHERE p.date_pointage BETWEEN :d1 AND :d2
";
$params = [':d1' => $date_debut, ':d2' => $date_fin];

if ($personnel_id > 0) {
    $sql .= " AND p.personnel_id = :pid";
    $params[':pid'] = $personnel_id;
}

$sql .= " ORDER BY p.date_pointage DESC, p.heure_arrivee DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pointages = $stmt->fetchAll();

$personnel_all = $pdo->query("SELECT id, fullname, matricule FROM personnel ORDER BY fullname ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historique des Pointages | Sécurité RMC BTP</title>
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
          <p class="sec-brand-subtitle">Historique & Archives des Pointages</p>
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
      <a href="personnel.php" class="sec-nav-link">
        <i class="fas fa-users-gear"></i> Recensement
      </a>
      <a href="rapports.php" class="sec-nav-link">
        <i class="fas fa-file-waveform"></i> Rapports pour le Boss
      </a>
      <a href="historique.php" class="sec-nav-link active">
        <i class="fas fa-clock-rotate-left"></i> Historique
      </a>
      <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
        <span class="sec-user-tag"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['sec_fullname'] ?? 'Gardien'); ?></span>
        <a href="logout.php" class="btn-logout-sec"><i class="fas fa-power-off"></i></a>
      </div>
    </nav>
  </header>

  <main class="sec-container">

    <!-- Barre de filtrage -->
    <div class="toolbar-card">
      <form action="historique.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
        <div>
          <label style="font-size: 0.78rem; font-weight: 700; color: var(--sec-text-muted); display: block;">Date Début :</label>
          <input type="date" name="date_debut" class="form-control-sec" value="<?php echo htmlspecialchars($date_debut); ?>">
        </div>

        <div>
          <label style="font-size: 0.78rem; font-weight: 700; color: var(--sec-text-muted); display: block;">Date Fin :</label>
          <input type="date" name="date_fin" class="form-control-sec" value="<?php echo htmlspecialchars($date_fin); ?>">
        </div>

        <div>
          <label style="font-size: 0.78rem; font-weight: 700; color: var(--sec-text-muted); display: block;">Employé :</label>
          <select name="personnel_id" class="form-control-sec" style="min-width: 200px;">
            <option value="0">Tous les Employés</option>
            <?php foreach ($personnel_all as $pa): ?>
              <option value="<?php echo $pa['id']; ?>" <?php echo $personnel_id === (int)$pa['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($pa['fullname']); ?> (<?php echo htmlspecialchars($pa['matricule']); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="align-self: flex-end;">
          <button type="submit" class="btn-filter active">
            <i class="fas fa-filter"></i> Filtrer
          </button>
        </div>
      </form>

      <div style="align-self: flex-end;">
        <a href="historique.php?date_debut=<?php echo urlencode($date_debut); ?>&date_fin=<?php echo urlencode($date_fin); ?>&personnel_id=<?php echo $personnel_id; ?>&export=csv" class="btn-print-sec">
          <i class="fas fa-file-excel"></i> Exporter CSV
        </a>
      </div>
    </div>

    <!-- Tableau d'historique -->
    <div class="sec-table-card">
      <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 16px;">
        <i class="fas fa-list-check" style="color: var(--sec-accent);"></i> Pointages Enregistrés (<?php echo count($pointages); ?>)
      </h3>

      <?php if (empty($pointages)): ?>
        <p style="padding: 30px; text-align: center; color: var(--sec-text-muted);">Aucun pointage trouvé pour cette période.</p>
      <?php else: ?>
        <table class="sec-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Personnel</th>
              <th>Fonction</th>
              <th>Heure Arrivée</th>
              <th>Heure Départ</th>
              <th>Durée Effectuée</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pointages as $p): ?>
              <tr>
                <td><strong><?php echo date('d/m/Y', strtotime($p['date_pointage'])); ?></strong></td>
                <td>
                  <strong><?php echo htmlspecialchars($p['fullname']); ?></strong><br>
                  <small style="color: var(--sec-accent); font-family: var(--font-mono);"><?php echo htmlspecialchars($p['matricule']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($p['role_title']); ?></td>
                <td>
                  <strong style="font-family: var(--font-mono);"><?php echo substr($p['heure_arrivee'], 0, 5); ?></strong>
                  <?php if ($p['is_late']): ?>
                    <span style="color:#f59e0b; font-size:0.75rem;">(Retard)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-family: var(--font-mono);">
                    <?php echo !empty($p['heure_depart']) ? substr($p['heure_depart'], 0, 5) : '-'; ?>
                  </span>
                </td>
                <td>
                  <span style="font-family: var(--font-mono); color: var(--sec-accent);">
                    <?php echo format_minutes_duree($p['duree_minutes']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($p['status'] === 'present'): ?>
                    <span style="color: #10b981; font-weight: 700;">● Présent</span>
                  <?php else: ?>
                    <span style="color: #38bdf8; font-weight: 700;">✓ Sorti</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
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