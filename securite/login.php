<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['sec_user_id'] = $user['id'];
            $_SESSION['sec_username'] = $user['username'];
            $_SESSION['sec_fullname'] = $user['fullname'];
            $_SESSION['sec_role'] = $user['role'];

            header('Location: index.php');
            exit;
        } else {
            $error = "Identifiants de sécurité incorrects.";
        }
    } else {
        $error = "Veuillez saisir votre identifiant et mot de passe.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Poste de Garde | Sécurité RMC BTP</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body.login-page {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      background: #050a14 radial-gradient(circle at 50% 30%, rgba(2, 132, 199, 0.15) 0%, transparent 60%);
    }
    .login-box {
      width: 100%;
      max-width: 420px;
      background: var(--sec-card);
      border: 1px solid var(--sec-border-light);
      border-radius: var(--radius-xl);
      padding: 36px 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
      text-align: center;
    }
    .btn-quick-login {
      padding: 10px 14px;
      border-radius: var(--radius-md);
      font-size: 0.85rem;
      font-weight: 700;
      border: 1px solid var(--sec-border);
      background: rgba(255, 255, 255, 0.04);
      color: var(--sec-text-muted);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-quick-login:hover {
      background: rgba(56, 189, 248, 0.12);
      border-color: var(--sec-accent);
      color: #fff;
    }
  </style>
</head>
<body class="login-page">

  <div class="login-box">
    <div style="margin-bottom: 20px;">
      <img src="img/logo-white.png" alt="RMC BTP SARL" style="height: 60px; width: auto; object-fit: contain;" onerror="this.src='img/logo.png'">
      <h2 style="font-size: 1.35rem; font-weight: 800; margin-top: 10px; color: #fff;">POSTE DE SÉCURITÉ</h2>
      <p style="font-size: 0.78rem; color: var(--sec-text-muted); text-transform: uppercase; letter-spacing: 1px;">Contrôle des Accès & Pointage Ouvriers</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 10px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 18px;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="form-group-sec" style="text-align: left;">
        <label class="form-label-sec">Identifiant</label>
        <input type="text" name="username" id="usernameInput" class="form-control-sec" placeholder="ex: gardien" required autofocus>
      </div>

      <div class="form-group-sec" style="text-align: left;">
        <label class="form-label-sec">Mot de Passe</label>
        <input type="password" name="password" id="passwordInput" class="form-control-sec" placeholder="••••••••" required>
      </div>

      <!-- Raccourcis Rapides Démo -->
      <div style="margin: 18px 0; display: flex; gap: 8px; justify-content: center;">
        <button type="button" class="btn-quick-login" onclick="fillCreds('gardien', 'gardien123')">
          <i class="fas fa-user-shield"></i> Gardien
        </button>
        <button type="button" class="btn-quick-login" onclick="fillCreds('boss', 'admin123')">
          <i class="fas fa-user-tie"></i> Boss
        </button>
      </div>

      <button type="submit" class="btn-pointage-1click btn-arrivee" style="margin-top: 10px;">
        <i class="fas fa-right-to-bracket"></i>
        <span>Accéder au Poste de Pointage</span>
      </button>
    </form>

    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--sec-border); font-size: 0.76rem; color: #64748b;">
      <i class="fas fa-shield-halved"></i> Système Certifié RMC BTP SARL • Yaoundé
    </div>
  </div>

  <script>
    function fillCreds(u, p) {
      document.getElementById('usernameInput').value = u;
      document.getElementById('passwordInput').value = p;
    }
  </script>

</body>
</html>