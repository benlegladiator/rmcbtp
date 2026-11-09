<?php
$adminTitle = "Boîte de Réception & Demandes de Devis";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

// Traitement des actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $msgId = (int)($_GET['id'] ?? 0);

    if ($msgId > 0) {
        if ($action === 'mark_read') {
            $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?")->execute([$msgId]);
            $success = "Message marqué comme lu.";
        } elseif ($action === 'mark_unread') {
            $pdo->prepare("UPDATE messages SET status = 'unread' WHERE id = ?")->execute([$msgId]);
            $success = "Message marqué comme non lu.";
        } elseif ($action === 'archive') {
            $pdo->prepare("UPDATE messages SET status = 'archived' WHERE id = ?")->execute([$msgId]);
            $success = "Message archivé.";
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$msgId]);
            $success = "Message définitivement supprimé.";
        }
    }
}

// Traitement de l'ajout d'une note ou réponse admin
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_notes'])) {
    $msgId = (int)$_POST['msg_id'];
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $pdo->prepare("UPDATE messages SET admin_notes = ?, status = 'read' WHERE id = ?")->execute([$adminNotes, $msgId]);
    $success = "Notes de suivi enregistrées avec succès.";
}

// Filtre sélectionné
$filter = $_GET['filter'] ?? 'all';
$viewId = (int)($_GET['view'] ?? 0);

$query = "SELECT * FROM messages ";
$params = [];

if ($filter === 'unread') {
    $query .= "WHERE status = 'unread' ";
} elseif ($filter === 'devis') {
    $query .= "WHERE type = 'devis' ";
} elseif ($filter === 'contact') {
    $query .= "WHERE type = 'contact' ";
} elseif ($filter === 'livechat') {
    $query .= "WHERE type = 'livechat' ";
} elseif ($filter === 'archived') {
    $query .= "WHERE status = 'archived' ";
} else {
    $query .= "WHERE status != 'archived' ";
}
$query .= "ORDER BY id DESC";

$messages = $pdo->query($query)->fetchAll();

// Si un message spécifique est ouvert pour consultation
$currentMsg = null;
if ($viewId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$viewId]);
    $currentMsg = $stmt->fetch();
    if ($currentMsg && $currentMsg['status'] === 'unread') {
        $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?")->execute([$viewId]);
        $currentMsg['status'] = 'read';
    }
}
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Onglets de filtres -->
<div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
  <a href="messages.php?filter=all" class="btn <?php echo ($filter === 'all') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-inbox"></i> Tous les Actifs
  </a>
  <a href="messages.php?filter=unread" class="btn <?php echo ($filter === 'unread') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-envelope"></i> Non Lus
  </a>
  <a href="messages.php?filter=devis" class="btn <?php echo ($filter === 'devis') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-calculator"></i> Demandes de Devis
  </a>
  <a href="messages.php?filter=contact" class="btn <?php echo ($filter === 'contact') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-comment-alt"></i> Prises de Contact
  </a>
  <a href="messages.php?filter=livechat" class="btn <?php echo ($filter === 'livechat') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-comments"></i> Boîte de Dialogue
  </a>
  <a href="messages.php?filter=archived" class="btn <?php echo ($filter === 'archived') ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
    <i class="fas fa-archive"></i> Archives
  </a>
</div>

<!-- Vue Détaillée d'un Message (si sélectionné) -->
<?php if ($currentMsg): ?>
  <div class="admin-card" style="border: 2px solid var(--admin-accent);">
    <div class="admin-card-header" style="background: #fff8f5;">
      <div>
        <h3>
          <i class="fas fa-envelope-open" style="color: var(--admin-accent); margin-right: 8px;"></i>
          <?php echo htmlspecialchars($currentMsg['subject'] ?? 'Message sans sujet'); ?>
        </h3>
        <span style="font-size: 0.8rem; color: var(--admin-muted);">
          Reçu le <?php echo date('d/m/Y à H:i', strtotime($currentMsg['created_at'])); ?> • Type : <strong><?php echo strtoupper($currentMsg['type']); ?></strong>
        </span>
      </div>
      <div style="display: flex; gap: 8px;">
        <a href="messages.php?action=mark_unread&id=<?php echo $currentMsg['id']; ?>" class="btn btn-outline btn-sm" title="Marquer non lu">
          <i class="fas fa-envelope"></i> Marquer Non Lu
        </a>
        <a href="messages.php?action=archive&id=<?php echo $currentMsg['id']; ?>" class="btn btn-outline btn-sm" title="Archiver">
          <i class="fas fa-archive"></i> Archiver
        </a>
        <a href="messages.php?action=delete&id=<?php echo $currentMsg['id']; ?>" class="btn btn-secondary btn-sm confirm-delete" style="background: #ef4444;" title="Supprimer">
          <i class="fas fa-trash"></i>
        </a>
      </div>
    </div>

    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--admin-border);">
        <div>
          <span style="font-size: 0.8rem; color: var(--admin-muted); display: block;">Expéditeur :</span>
          <strong style="font-size: 1.1rem; color: var(--admin-primary);"><?php echo htmlspecialchars($currentMsg['sender_name']); ?></strong>
          <div style="margin-top: 5px;">
            <i class="fas fa-phone-alt" style="color: var(--admin-accent); font-size: 0.85rem;"></i>
            <a href="tel:<?php echo htmlspecialchars($currentMsg['sender_phone']); ?>" style="font-weight: 600;"><?php echo htmlspecialchars($currentMsg['sender_phone']); ?></a>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $currentMsg['sender_phone']); ?>?text=Bonjour%20<?php echo urlencode($currentMsg['sender_name']); ?>,%20nous%20donnons%20suite%20à%20votre%20demande%20sur%20FREE%20BTP." target="_blank" class="btn btn-whatsapp btn-sm" style="margin-left: 10px; padding: 2px 8px; font-size: 0.75rem;">
              <i class="fab fa-whatsapp"></i> Répondre WhatsApp
            </a>
          </div>
          <div style="margin-top: 5px;">
            <i class="fas fa-envelope" style="color: var(--admin-muted); font-size: 0.85rem;"></i>
            <a href="mailto:<?php echo htmlspecialchars($currentMsg['sender_email']); ?>"><?php echo htmlspecialchars($currentMsg['sender_email']); ?></a>
          </div>
        </div>

        <div>
          <span style="font-size: 0.8rem; color: var(--admin-muted); display: block;">Prestation Demandée :</span>
          <span class="badge badge-devis" style="font-size: 0.9rem; margin-top: 4px;">
            <i class="fas fa-hard-hat"></i> <?php echo htmlspecialchars($currentMsg['service_requested'] ?? 'Non spécifié'); ?>
          </span>

          <?php if (!empty($currentMsg['attachment'])): ?>
            <div style="margin-top: 15px;">
              <span style="font-size: 0.8rem; color: var(--admin-muted); display: block;">Pièce Jointe transmise par le client :</span>
              <a href="../<?php echo htmlspecialchars($currentMsg['attachment']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top: 5px;">
                <i class="fas fa-file-download"></i> Télécharger le document joint
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Corps du message -->
      <div style="margin-bottom: 30px;">
        <h4 style="font-size: 0.95rem; color: var(--admin-primary); margin-bottom: 8px;">Contenu du Message / Demande :</h4>
        <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 8px; padding: 20px; font-size: 0.95rem; line-height: 1.7; color: var(--admin-text);">
          <?php echo nl2br(htmlspecialchars($currentMsg['message'])); ?>
        </div>
      </div>

      <!-- Espace de Suivi & Notes Internes -->
      <form action="messages.php?view=<?php echo $currentMsg['id']; ?>" method="POST" style="background: #f1f5f9; padding: 20px; border-radius: 8px;">
        <input type="hidden" name="msg_id" value="<?php echo $currentMsg['id']; ?>">
        <h4 style="font-size: 0.95rem; color: var(--admin-primary); margin-bottom: 8px;">
          <i class="fas fa-sticky-note" style="color: var(--admin-accent); margin-right: 6px;"></i> Notes Internes & Suivi du Chantier
        </h4>
        <textarea name="admin_notes" rows="3" class="form-control" style="width: 100%; margin-bottom: 12px; background: #fff;" placeholder="Ex: Devis chiffré transmis par WhatsApp le 22/09. Rendez-vous de visite fixé sur le terrain à Dschang..."><?php echo htmlspecialchars($currentMsg['admin_notes'] ?? ''); ?></textarea>
        <button type="submit" name="save_notes" class="btn btn-primary btn-sm">
          <i class="fas fa-save"></i> Enregistrer les notes de suivi
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<!-- Tableau de la boîte de réception -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>Liste des Messages & Demandes (<?php echo count($messages); ?>)</h3>
  </div>
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Statut</th>
          <th>Type</th>
          <th>Expéditeur</th>
          <th>Téléphone / Email</th>
          <th>Sujet / Prestation</th>
          <th>Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 30px;">
              Aucun message trouvé pour ce filtre.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <tr style="<?php echo ($m['status'] === 'unread') ? 'background-color: #fffaf0; font-weight: 600;' : ''; ?>">
              <td>
                <?php if ($m['status'] === 'unread'): ?>
                  <span class="badge badge-unread"><i class="fas fa-envelope"></i> Non lu</span>
                <?php elseif ($m['status'] === 'archived'): ?>
                  <span class="badge badge-inactive"><i class="fas fa-archive"></i> Archivé</span>
                <?php else: ?>
                  <span class="badge badge-read"><i class="fas fa-check"></i> Traité</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($m['type'] === 'devis'): ?>
                  <span class="badge badge-devis"><i class="fas fa-calculator"></i> Devis</span>
                <?php elseif ($m['type'] === 'livechat'): ?>
                  <span class="badge badge-livechat"><i class="fas fa-comments"></i> Chat</span>
                <?php else: ?>
                  <span class="badge badge-contact"><i class="fas fa-envelope"></i> Contact</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($m['sender_name']); ?></td>
              <td>
                <div><?php echo htmlspecialchars($m['sender_phone'] ?? ''); ?></div>
                <small style="color: var(--admin-muted); font-weight: normal;"><?php echo htmlspecialchars($m['sender_email'] ?? ''); ?></small>
              </td>
              <td>
                <div><?php echo htmlspecialchars($m['subject'] ?? 'Sans sujet'); ?></div>
                <small style="color: var(--admin-accent); font-weight: normal;"><?php echo htmlspecialchars($m['service_requested'] ?? 'Général'); ?></small>
              </td>
              <td style="font-weight: normal; font-size: 0.82rem;"><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="messages.php?view=<?php echo $m['id']; ?>&filter=<?php echo $filter; ?>" class="action-btn" title="Consulter">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="messages.php?action=delete&id=<?php echo $m['id']; ?>" class="action-btn action-btn-danger confirm-delete" title="Supprimer">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
