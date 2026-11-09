<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$siteSettings = getSiteSettings($pdo);

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_fullname'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];

            // Mettre à jour last_login
            $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?")->execute([$user['id']]);

            header('Location: index.php');
            exit;
        } else {
            $error = "Identifiants invalides. Veuillez vérifier le nom d'utilisateur et le mot de passe.";
        }
    } else {
        $error = "Veuillez saisir votre nom d'utilisateur et votre mot de passe.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Espace Direction | RMC BTP SARL</title>
  <link rel="icon" type="image/png" href="../images/favicon.png?v=<?= time() ?>">
  <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #0284c7;
      --primary-dark: #0369a1;
      --primary-glow: rgba(2, 132, 199, 0.45);
      --cyan: #38bdf8;
      --dark-bg: #050d1a;
      --card-bg: rgba(11, 25, 48, 0.78);
      --card-border: rgba(56, 189, 248, 0.18);
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body.luxury-login {
      min-height: 100vh;
      background-color: var(--dark-bg);
      background-image: 
        radial-gradient(circle at 18% 25%, rgba(2, 132, 199, 0.22) 0%, transparent 45%),
        radial-gradient(circle at 82% 75%, rgba(14, 165, 233, 0.18) 0%, transparent 45%),
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      background-size: 100% 100%, 100% 100%, 36px 36px, 36px 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      position: relative;
      overflow-x: hidden;
    }

    /* Halo lumineux décoratif en arrière-plan */
    .bg-glow-orb {
      position: absolute;
      width: 520px;
      height: 520px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, rgba(3, 105, 161, 0) 70%);
      filter: blur(60px);
      pointer-events: none;
      animation: orbFloat 14s ease-in-out infinite alternate;
      z-index: 1;
    }

    @keyframes orbFloat {
      0% { transform: translate(-20px, -20px) scale(1); }
      100% { transform: translate(30px, 20px) scale(1.1); }
    }

    .login-container {
      width: 100%;
      max-width: 440px;
      position: relative;
      z-index: 10;
    }

    .login-glass-card {
      background: var(--card-bg);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--card-border);
      border-radius: 24px;
      padding: 40px 34px;
      box-shadow: 
        0 25px 60px -15px rgba(0, 0, 0, 0.65),
        0 0 0 1px rgba(255, 255, 255, 0.05),
        inset 0 1px 1px rgba(255, 255, 255, 0.15);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .brand-section {
      text-align: center;
      margin-bottom: 30px;
    }

    .brand-logo-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 82px;
      height: 82px;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 22px;
      padding: 12px;
      margin-bottom: 16px;
      box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(56, 189, 248, 0.18);
    }

    .brand-logo-badge img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.4));
    }

    .brand-title {
      font-size: 1.45rem;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.5px;
      margin-bottom: 6px;
      background: linear-gradient(135deg, #ffffff 40%, var(--cyan) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(56, 189, 248, 0.12);
      border: 1px solid rgba(56, 189, 248, 0.25);
      color: var(--cyan);
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.1px;
      padding: 5px 12px;
      border-radius: 20px;
    }

    .brand-badge i {
      font-size: 0.65rem;
    }

    /* Message d'alerte */
    .alert-banner {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #fca5a5;
      padding: 12px 14px;
      border-radius: 12px;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 22px;
      animation: alertShake 0.4s ease;
    }

    @keyframes alertShake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-6px); }
      40%, 80% { transform: translateX(6px); }
    }

    /* Champs de saisie */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #cbd5e1;
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 16px;
      color: #64748b;
      font-size: 0.95rem;
      transition: color 0.2s ease;
      pointer-events: none;
    }

    .form-control-luxury {
      width: 100%;
      background: rgba(15, 23, 42, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 14px;
      padding: 13px 44px 13px 44px;
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 500;
      transition: all 0.25s ease;
      outline: none;
    }

    .form-control-luxury:focus {
      border-color: var(--cyan);
      background: rgba(15, 23, 42, 0.85);
      box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.18), 0 0 20px rgba(56, 189, 248, 0.1);
    }

    .input-wrapper:focus-within .input-icon {
      color: var(--cyan);
    }

    .btn-toggle-password {
      position: absolute;
      right: 14px;
      background: transparent;
      border: none;
      color: #64748b;
      cursor: pointer;
      font-size: 0.95rem;
      padding: 6px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.2s ease;
    }

    .btn-toggle-password:hover {
      color: #cbd5e1;
    }

    /* Raccourci identifiants démo */
    .demo-pill {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 255, 255, 0.04);
      border: 1px dashed rgba(255, 255, 255, 0.14);
      border-radius: 12px;
      padding: 10px 14px;
      margin-bottom: 24px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .demo-pill:hover {
      background: rgba(56, 189, 248, 0.08);
      border-color: rgba(56, 189, 248, 0.35);
    }

    .demo-pill-text {
      font-size: 0.78rem;
      color: #94a3b8;
    }

    .demo-pill-text strong {
      color: #e2e8f0;
    }

    .demo-pill-action {
      font-size: 0.72rem;
      color: var(--cyan);
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Bouton principal */
    .btn-login-submit {
      width: 100%;
      background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 14px;
      padding: 14px 20px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 10px 25px -5px var(--primary-glow);
      transition: all 0.25s ease;
    }

    .btn-login-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 30px -4px rgba(2, 132, 199, 0.6);
      background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
    }

    .btn-login-submit:active {
      transform: translateY(0);
    }

    /* Pied de formulaire */
    .card-footer-nav {
      margin-top: 26px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }

    .back-home-link {
      color: #94a3b8;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: color 0.2s ease, transform 0.2s ease;
    }

    .back-home-link:hover {
      color: #ffffff;
      transform: translateX(-3px);
    }

    .security-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.72rem;
      color: #64748b;
    }

    .security-badge .dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 8px #10b981;
    }

    @media (max-width: 480px) {
      .login-glass-card {
        padding: 30px 22px;
        border-radius: 20px;
      }
      .brand-title {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body class="luxury-login">

  <div class="bg-glow-orb"></div>

  <div class="login-container">
    <div class="login-glass-card">
      
      <!-- En-tête Marque RMC BTP -->
      <div class="brand-section">
        <div class="brand-logo-badge">
          <img src="../images/logo.png" alt="RMC BTP SARL">
        </div>
        <h1 class="brand-title">RMC BTP SARL</h1>
        <div class="brand-badge">
          <i class="fas fa-shield-alt"></i> Portail Direction Sécurisé
        </div>
      </div>

      <!-- Alerte Erreur -->
      <?php if (!empty($error)): ?>
        <div class="alert-banner">
          <i class="fas fa-exclamation-triangle"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <!-- Formulaire de Connexion -->
      <form action="login.php" method="POST" autocomplete="on">
        
        <div class="form-group">
          <label class="form-label" for="usernameInput">
            Identifiant Administrateur
          </label>
          <div class="input-wrapper">
            <i class="fas fa-user-tie input-icon"></i>
            <input 
              type="text" 
              name="username" 
              id="usernameInput" 
              class="form-control-luxury" 
              value="admin" 
              placeholder="ex: admin" 
              required 
              autofocus
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="passwordInput">
            Mot de Passe Sécurisé
          </label>
          <div class="input-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input 
              type="password" 
              name="password" 
              id="passwordInput" 
              class="form-control-luxury" 
              placeholder="••••••••" 
              required
            >
            <button type="button" class="btn-toggle-password" id="togglePasswordBtn" aria-label="Afficher ou masquer le mot de passe">
              <i class="fas fa-eye" id="togglePasswordIcon"></i>
            </button>
          </div>
        </div>

        <!-- Raccourci Démo Interactif -->
        <div class="demo-pill" id="demoPill" title="Cliquer pour renseigner automatiquement">
          <div class="demo-pill-text">
            Compte démo : <strong>admin</strong> / <strong>admin123</strong>
          </div>
          <div class="demo-pill-action">
            <i class="fas fa-magic"></i> Remplir
          </div>
        </div>

        <button type="submit" class="btn-login-submit">
          <span>Se Connecter au Dashboard</span>
          <i class="fas fa-arrow-right"></i>
        </button>
      </form>

      <!-- Pied de carte -->
      <div class="card-footer-nav">
        <a href="../index.php" class="back-home-link">
          <i class="fas fa-arrow-left"></i>
          <span>Retour au site public RMC BTP</span>
        </a>
        <div class="security-badge">
          <span class="dot"></span>
          <span>Chiffrement SSL 256-Bit • Yaoundé, Cameroun</span>
        </div>
      </div>

    </div>
  </div>

  <script>
    // 1. Bascule Affichage Mot de Passe
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('passwordInput');
    const pwdIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && pwdInput && pwdIcon) {
      toggleBtn.addEventListener('click', function() {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        pwdIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
      });
    }

    // 2. Remplissage automatique des identifiants au clic sur le badge démo
    const demoPill = document.getElementById('demoPill');
    const userInput = document.getElementById('usernameInput');

    if (demoPill && userInput && pwdInput) {
      demoPill.addEventListener('click', function() {
        userInput.value = 'admin';
        pwdInput.value = 'admin123';
        pwdInput.focus();
      });
    }
  </script>

</body>
</html>