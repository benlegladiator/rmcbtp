<?php
$adminTitle = "Sécurité & Mot de Passe Administrateur";
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

$userId = $_SESSION['admin_user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $currentPass = trim($_POST['current_password'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirmPass = trim($_POST['confirm_password'] ?? '');

    if (!empty($fullName) && !empty($email) && !empty($username)) {
        // Si changement de mot de passe demandé
        if (!empty($newPass)) {
            if (empty($currentPass) || !password_verify($currentPass, $currentUser['password'])) {
                $error = "Le mot de passe actuel saisi est incorrect.";
            } elseif ($newPass !== $confirmPass) {
                $error = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
            } elseif (strlen($newPass) < 6) {
                $error = "Le mot de passe doit comporter au moins 6 caractères.";
            } else {
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, password = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $username, $hashed, $userId]);
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_fullname'] = $fullName;
                $success = "Vos informations et votre mot de passe ont été modifiés avec succès.";
            }
        } else {
            // Mise à jour profil sans changer le mot de passe
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $username, $userId]);
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_fullname'] = $fullName;
            $success = "Vos informations ont été mises à jour.";
        }
    } else {
        $error = "Veuillez renseigner tous les champs obligatoires.";
    }

    // Recharger
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();
}
?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="fas fa-shield-alt" style="color: var(--admin-accent); margin-right: 8px;"></i> Profil Maître de l'Administrateur</h3>
  </div>

  <div class="admin-card-body">
    <form action="profile.php" method="POST">
      <div class="form-grid" style="margin-bottom: 25px;">
        <div class="form-group">
          <label>Nom & Prénom du Responsable</label>
          <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label>Identifiant de Connexion (Username)</label>
          <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label>Adresse Email de Réception des Alertes</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label>Dernière Connexion</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($currentUser['last_login'] ?? 'Récemment'); ?>" disabled style="background: #f1f5f9;">
        </div>
      </div>

      <h4 style="font-size: 1.05rem; color: var(--admin-primary); margin-bottom: 15px; border-top: 1px solid var(--admin-border); padding-top: 20px;">
        <i class="fas fa-key" style="color: var(--admin-accent); margin-right: 6px;"></i> Modifier le Mot de Passe (Optionnel)
      </h4>
      <p style="font-size: 0.85rem; color: var(--admin-muted); margin-bottom: 20px;">
        Laissez ces champs vides si vous ne souhaitez pas changer de mot de passe.
      </p>

      <div class="form-grid" style="margin-bottom: 25px;">
        <div class="form-group">
          <label>Mot de Passe Actuel</label>
          <input type="password" name="current_password" class="form-control" placeholder="••••••••">
        </div>

        <div class="form-group">
          <label>Nouveau Mot de Passe (min. 6 caractères)</label>
          <input type="password" name="new_password" class="form-control" placeholder="••••••••">
        </div>

        <div class="form-group">
          <label>Confirmer le Nouveau Mot de Passe</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Mettre à jour mon profil
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
