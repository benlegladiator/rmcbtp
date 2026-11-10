<?php
require_once 'config.php';
require_login();

$period = $_GET['period'] ?? 'daily';
$date_selected = $_GET['date'] ?? date('Y-m-d');
$month_selected = $_GET['month'] ?? date('Y-m');

$boss_phone = get_boss_phone();

// ----------------------------------------------------------------------------
// 1. DONNÉES DU RAPPORT JOURNALIER
// ----------------------------------------------------------------------------
if ($period === 'daily') {
    $stmt = $pdo->prepare("
        SELECT e.matricule, e.fullname, e.role_title, e.department, e.normal_start_time,
               p.heure_arrivee, p.heure_depart, p.duree_minutes, p.is_late, p.status
        FROM personnel e
        LEFT JOIN pointages p ON e.id = p.personnel_id AND p.date_pointage = ?
        WHERE e.is_active = 1
        ORDER BY e.department ASC, e.fullname ASC
    ");
    $stmt->execute([$date_selected]);
    $daily_records = $stmt->fetchAll();

    $nb_total = count($daily_records);
    $nb_presents = 0;
    $nb_partis = 0;
    $nb_retards = 0;
    $nb_absents = 0;

    foreach ($daily_records as $r) {
        if ($r['status'] === 'present') $nb_presents++;
        elseif ($r['status'] === 'parti') $nb_partis++;
        else $nb_absents++;
        if ($r['is_late']) $nb_retards++;
    }
    $taux_presence = $nb_total > 0 ? round((($nb_presents + $nb_partis) / $nb_total) * 100) : 0;

    // Formatage texte WhatsApp Boss
    $date_fr = date('d/m/Y', strtotime($date_selected));
    $wa_msg = "🚨 *RAPPORT DE SÉCURITÉ & POINTAGE DU PERSONNEL*\n";
    $wa_msg .= "🏢 *RMC BTP SARL - Yaoundé*\n";
    $wa_msg .= "📅 *Date :* $date_fr\n\n";
    $wa_msg .= "📊 *SYNTHÈSE DE LA JOURNÉE :*\n";
    $wa_msg .= "• Effectif Recensé : *$nb_total* ouvriers/agents\n";
    $wa_msg .= "• Taux de Présence : *$taux_presence %*\n";
    $wa_msg .= "• Présents sur site : *$nb_presents*\n";
    $wa_msg .= "• Déjà sortis : *$nb_partis*\n";
    $wa_msg .= "• Retards constatés : *$nb_retards*\n";
    $wa_msg .= "• Absents non justifiés : *$nb_absents*\n\n";
    $wa_msg .= "📋 *RÉSUMÉ DES ÉMARGEMENTS :*\n";

    $count_shown = 0;
    foreach ($daily_records as $r) {
        if (!empty($r['heure_arrivee'])) {
            $hArr = substr($r['heure_arrivee'], 0, 5);
            $hDep = !empty($r['heure_depart']) ? substr($r['heure_depart'], 0, 5) : 'Présent';
            $flag = $r['is_late'] ? '⚠️ Retard' : '✅';
            $wa_msg .= "• {$r['fullname']} ({$r['role_title']}) : $hArr → $hDep ($flag)\n";
            $count_shown++;
        }
    }
    if ($nb_absents > 0) {
        $wa_msg .= "\n❌ *ABSENTS DU JOUR :*\n";
        foreach ($daily_records as $r) {
            if (empty($r['heure_arrivee'])) {
                $wa_msg .= "• {$r['fullname']} - {$r['role_title']} ({$r['department']})\n";
            }
        }
    }
    $wa_msg .= "\nRapport certifié et transmis au Boss par le Poste de Garde.";
    $wa_url = "https://wa.me/$boss_phone?text=" . urlencode($wa_msg);
}

// ----------------------------------------------------------------------------
// 2. DONNÉES DU RAPPORT HEBDOMADAIRE
// ----------------------------------------------------------------------------
elseif ($period === 'weekly') {
    // Calcul semaine actuelle
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($date_selected)));
    $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($date_selected)));

    $stmt = $pdo->prepare("
        SELECT e.matricule, e.fullname, e.role_title, e.department,
               COUNT(p.id) as jours_presents,
               SUM(p.is_late) as total_retards,
               COALESCE(SUM(p.duree_minutes), 0) as total_minutes
        FROM personnel e
        LEFT JOIN pointages p ON e.id = p.personnel_id AND p.date_pointage BETWEEN ? AND ?
        WHERE e.is_active = 1
        GROUP BY e.id
        ORDER BY total_minutes DESC, e.fullname ASC
    ");
    $stmt->execute([$monday, $sunday]);
    $weekly_records = $stmt->fetchAll();

    // Texte WhatsApp Boss Hebdo
    $wa_msg = "📊 *RAPPORT HEBDOMADAIRE DE POINTAGE CHANTIER*\n";
    $wa_msg .= "🏢 *RMC BTP SARL*\n";
    $wa_msg .= "🗓️ *Semaine du :* " . date('d/m', strtotime($monday)) . " au " . date('d/m/Y', strtotime($sunday)) . "\n\n";
    $wa_msg .= "👷‍♂️ *BILAN HORAIRE & ASSIDUITÉ :*\n";
    foreach ($weekly_records as $w) {
        $heures = format_minutes_duree($w['total_minutes']);
        $ret = $w['total_retards'] > 0 ? " ({$w['total_retards']} retard(s))" : "";
        $wa_msg .= "• {$w['fullname']} : {$w['jours_presents']}/6 jours • $heures$ret\n";
    }
    $wa_msg .= "\nRapport hebdomadaire validé pour le Boss.";
    $wa_url = "https://wa.me/$boss_phone?text=" . urlencode($wa_msg);
}

// ----------------------------------------------------------------------------
// 3. DONNÉES DU RAPPORT MENSUEL (RH & PAIE)
// ----------------------------------------------------------------------------
elseif ($period === 'monthly') {
    $stmt = $pdo->prepare("
        SELECT e.matricule, e.fullname, e.role_title, e.department,
               COUNT(p.id) as jours_prestés,
               SUM(p.is_late) as total_retards,
               COALESCE(SUM(p.duree_minutes), 0) as total_minutes
        FROM personnel e
        LEFT JOIN pointages p ON e.id = p.personnel_id AND strftime('%Y-%m', p.date_pointage) = ? OR (p.date_pointage LIKE ?)
        WHERE e.is_active = 1
        GROUP BY e.id
        ORDER BY jours_prestés DESC, e.fullname ASC
    ");
    $stmt->execute([$month_selected, "$month_selected%"]);
    $monthly_records = $stmt->fetchAll();

    $wa_msg = "📑 *BILAN MENSUEL DE POINTAGE & PAIE*\n";
    $wa_msg .= "🏢 *RMC BTP SARL*\n";
    $wa_msg .= "📅 *Mois de :* $month_selected\n\n";
    $wa_msg .= "📈 *RÉSUMÉ PRESTATIONS PAR TRAVAILLEUR :*\n";
    foreach ($monthly_records as $m) {
        $heures = format_minutes_duree($m['total_minutes']);
        $wa_msg .= "• {$m['fullname']} ({$m['role_title']}) : {$m['jours_prestés']} jrs prestés ($heures)\n";
    }
    $wa_msg .= "\nBilan mensuel transmis à la Direction Générale.";
    $wa_url = "https://wa.me/$boss_phone?text=" . urlencode($wa_msg);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rapports pour le Boss | Sécurité RMC BTP</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .subnav-period {
      display: flex;
      gap: 10px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .period-btn {
      padding: 10px 18px;
      border-radius: var(--radius-md);
      font-weight: 700;
      font-size: 0.92rem;
      text-decoration: none;
      color: var(--sec-text-muted);
      background: var(--sec-card);
      border: 1px solid var(--sec-border);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
    }
    .period-btn.active {
      background: var(--sec-primary);
      border-color: var(--sec-primary);
      color: #fff;
      box-shadow: 0 4px 14px var(--sec-primary-glow);
    }
  </style>
</head>
<body>

  <header class="sec-header">
    <div class="sec-header-top">
      <a href="index.php" class="sec-brand">
        <img src="img/logo-white.png" alt="RMC BTP SARL" class="sec-brand-logo" onerror="this.src='img/logo.png'">
        <div>
          <h1 class="sec-brand-title">RMC SÉCURITÉ</h1>
          <p class="sec-brand-subtitle">Générateur Automatique de Rapports Boss</p>
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
      <a href="rapports.php" class="sec-nav-link active">
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

    <!-- Sélecteur de Type de Rapport : Journalier, Hebdomadaire, Mensuel -->
    <div class="subnav-period btn-print-hide">
      <a href="rapports.php?period=daily&date=<?php echo $date_selected; ?>" class="period-btn <?php echo $period === 'daily' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-day"></i> 1. Rapport Journalier
      </a>
      <a href="rapports.php?period=weekly&date=<?php echo $date_selected; ?>" class="period-btn <?php echo $period === 'weekly' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-week"></i> 2. Rapport Hebdomadaire
      </a>
      <a href="rapports.php?period=monthly&month=<?php echo $month_selected; ?>" class="period-btn <?php echo $period === 'monthly' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-days"></i> 3. Rapport Mensuel (Paie)
      </a>
    </div>

    <!-- Barre d'Action & Envoi Boss -->
    <div class="toolbar-card btn-print-hide">
      <form action="rapports.php" method="GET" style="display: flex; gap: 12px; align-items: center;">
        <input type="hidden" name="period" value="<?php echo htmlspecialchars($period); ?>">
        
        <?php if ($period === 'monthly'): ?>
          <label style="font-weight: 700; font-size: 0.88rem;">Mois de Référence :</label>
          <input type="month" name="month" class="form-control-sec" style="width: auto;" value="<?php echo htmlspecialchars($month_selected); ?>" onchange="this.form.submit()">
        <?php else: ?>
          <label style="font-weight: 700; font-size: 0.88rem;">Date du Pointage :</label>
          <input type="date" name="date" class="form-control-sec" style="width: auto;" value="<?php echo htmlspecialchars($date_selected); ?>" onchange="this.form.submit()">
        <?php endif; ?>
      </form>

      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="<?php echo $wa_url; ?>" target="_blank" class="btn-whatsapp-boss">
          <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
          <span>Envoyer au Boss (WhatsApp)</span>
        </a>

        <button onclick="window.print()" class="btn-print-sec">
          <i class="fas fa-print"></i>
          <span>Imprimer Fiche Officielle</span>
        </button>
      </div>
    </div>

    <!-- ======================================================================
         VUE 1 : RAPPORT JOURNALIER
         ====================================================================== -->
    <?php if ($period === 'daily'): ?>
      <div style="text-align: center; margin-bottom: 24px;">
        <h2 style="font-size: 1.45rem; font-weight: 800; color: #fff;">
          FEUILLE D'ÉMARGEMENT & DE SÉCURITÉ DU PERSONNEL
        </h2>
        <p style="color: var(--sec-text-muted); font-size: 0.92rem;">
          Journée du <strong><?php echo date('d/m/Y', strtotime($date_selected)); ?></strong> • RMC BTP SARL
        </p>
      </div>

      <div class="metrics-row">
        <div class="metric-pill">
          <div class="metric-data">
            <h4>Total Effectif</h4>
            <div class="metric-val"><?php echo $nb_total; ?></div>
          </div>
          <div class="metric-icon-box" style="background: rgba(255,255,255,0.06); color:#fff;"><i class="fas fa-users"></i></div>
        </div>

        <div class="metric-pill" style="border-color: rgba(16, 185, 129, 0.4);">
          <div class="metric-data">
            <h4>Taux de Présence</h4>
            <div class="metric-val" style="color: #10b981;"><?php echo $taux_presence; ?>%</div>
          </div>
          <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.15); color:#10b981;"><i class="fas fa-chart-pie"></i></div>
        </div>

        <div class="metric-pill" style="border-color: rgba(245, 158, 11, 0.4);">
          <div class="metric-data">
            <h4>Retards</h4>
            <div class="metric-val" style="color: #f59e0b;"><?php echo $nb_retards; ?></div>
          </div>
          <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.15); color:#f59e0b;"><i class="fas fa-clock"></i></div>
        </div>

        <div class="metric-pill" style="border-color: rgba(239, 68, 68, 0.4);">
          <div class="metric-data">
            <h4>Absents</h4>
            <div class="metric-val" style="color: #ef4444;"><?php echo $nb_absents; ?></div>
          </div>
          <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.15); color:#ef4444;"><i class="fas fa-user-xmark"></i></div>
        </div>
      </div>

      <div class="sec-table-card">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Matricule & Personnel</th>
              <th>Fonction / Poste</th>
              <th>Département</th>
              <th>Heure Arrivée</th>
              <th>Heure Départ</th>
              <th>Durée sur Site</th>
              <th>Statut Émargement</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daily_records as $r): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($r['fullname']); ?></strong><br>
                  <small style="color: var(--sec-accent); font-family: var(--font-mono);"><?php echo htmlspecialchars($r['matricule']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($r['role_title']); ?></td>
                <td><?php echo htmlspecialchars($r['department']); ?></td>
                <td>
                  <?php if (!empty($r['heure_arrivee'])): ?>
                    <strong style="font-family: var(--font-mono);"><?php echo substr($r['heure_arrivee'], 0, 5); ?></strong>
                    <?php if ($r['is_late']): ?>
                      <span style="color:#f59e0b; font-size:0.75rem; font-weight:700;">(Retard)</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="color: #64748b;">Non pointé</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-family: var(--font-mono);">
                    <?php echo !empty($r['heure_depart']) ? substr($r['heure_depart'], 0, 5) : '-'; ?>
                  </span>
                </td>
                <td>
                  <?php 
                    if ($r['status'] === 'parti') {
                        echo format_minutes_duree($r['duree_minutes']);
                    } elseif ($r['status'] === 'present') {
                        echo '<span style="color:#10b981; font-weight:700;">Présent</span>';
                    } else {
                        echo '<span style="color:#ef4444;">Absent</span>';
                    }
                  ?>
                </td>
                <td>
                  <?php if ($r['status'] === 'present'): ?>
                    <span style="color: #10b981; font-weight: 700;">● Présent sur Chantier</span>
                  <?php elseif ($r['status'] === 'parti'): ?>
                    <span style="color: #38bdf8; font-weight: 700;">✓ Sorti (Clôturé)</span>
                  <?php else: ?>
                    <span style="color: #ef4444; font-weight: 700;">✗ Absent</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ======================================================================
         VUE 2 : RAPPORT HEBDOMADAIRE
         ====================================================================== -->
    <?php elseif ($period === 'weekly'): ?>
      <div style="text-align: center; margin-bottom: 24px;">
        <h2 style="font-size: 1.45rem; font-weight: 800; color: #fff;">
          SYNTHÈSE HEBDOMADAIRE DES POINTAGES & HEURES
        </h2>
        <p style="color: var(--sec-text-muted); font-size: 0.92rem;">
          Semaine du <strong><?php echo date('d/m/Y', strtotime($monday)); ?></strong> au <strong><?php echo date('d/m/Y', strtotime($sunday)); ?></strong>
        </p>
      </div>

      <div class="sec-table-card">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Personnel</th>
              <th>Poste BTP</th>
              <th>Département</th>
              <th>Jours Pointés</th>
              <th>Retards Semaine</th>
              <th>Cumul d'Heures</th>
              <th>Assiduité</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($weekly_records as $w): 
              $pct = min(100, round(($w['jours_presents'] / 6) * 100));
            ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($w['fullname']); ?></strong><br>
                  <small style="color: var(--sec-accent); font-family: var(--font-mono);"><?php echo htmlspecialchars($w['matricule']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($w['role_title']); ?></td>
                <td><?php echo htmlspecialchars($w['department']); ?></td>
                <td><strong><?php echo $w['jours_presents']; ?></strong> / 6 jours</td>
                <td>
                  <?php if ($w['total_retards'] > 0): ?>
                    <span style="color: #f59e0b; font-weight: 700;"><?php echo $w['total_retards']; ?> retard(s)</span>
                  <?php else: ?>
                    <span style="color: #10b981;">0</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color: var(--sec-accent); font-family: var(--font-mono); font-size: 1.05rem;">
                    <?php echo format_minutes_duree($w['total_minutes']); ?>
                  </strong>
                </td>
                <td>
                  <span style="font-weight: 800; color: <?php echo $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444'); ?>;">
                    <?php echo $pct; ?>%
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- ======================================================================
         VUE 3 : RAPPORT MENSUEL (PAIE)
         ====================================================================== -->
    <?php elseif ($period === 'monthly'): ?>
      <div style="text-align: center; margin-bottom: 24px;">
        <h2 style="font-size: 1.45rem; font-weight: 800; color: #fff;">
          BILAN MENSUEL DE POINTAGE POUR LA PAIE
        </h2>
        <p style="color: var(--sec-text-muted); font-size: 0.92rem;">
          Mois de <strong><?php echo htmlspecialchars($month_selected); ?></strong> • Base de calcul : 26 jours ouvrés
        </p>
      </div>

      <div class="sec-table-card">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Personnel</th>
              <th>Poste BTP</th>
              <th>Département</th>
              <th>Jours Prestés</th>
              <th>Total Retards</th>
              <th>Volume Horaire Réalisé</th>
              <th>Taux Présence Mensuel</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($monthly_records as $m): 
              $taux = min(100, round(($m['jours_prestés'] / 26) * 100));
            ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($m['fullname']); ?></strong><br>
                  <small style="color: var(--sec-accent); font-family: var(--font-mono);"><?php echo htmlspecialchars($m['matricule']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($m['role_title']); ?></td>
                <td><?php echo htmlspecialchars($m['department']); ?></td>
                <td><strong><?php echo $m['jours_prestés']; ?></strong> jours</td>
                <td>
                  <?php echo $m['total_retards'] > 0 ? "<span style='color:#f59e0b; font-weight:700;'>{$m['total_retards']}</span>" : "0"; ?>
                </td>
                <td>
                  <strong style="color: var(--sec-accent); font-family: var(--font-mono);">
                    <?php echo format_minutes_duree($m['total_minutes']); ?>
                  </strong>
                </td>
                <td>
                  <span style="font-weight: 800; color: <?php echo $taux >= 80 ? '#10b981' : ($taux >= 50 ? '#f59e0b' : '#ef4444'); ?>;">
                    <?php echo $taux; ?>%
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- Zone de Visa & Signature pour le Gardien et le Boss (Impression) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; text-align: center; page-break-inside: avoid;">
      <div style="border-top: 1px solid #666; padding-top: 10px;">
        <strong>Le Chef de Poste Sécurité</strong><br>
        <small style="color: var(--sec-text-muted);">(Visa du Gardien)</small>
      </div>
      <div style="border-top: 1px solid #666; padding-top: 10px;">
        <strong>La Direction Générale (Boss)</strong><br>
        <small style="color: var(--sec-text-muted);">(Visa de Contrôle & Paie)</small>
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