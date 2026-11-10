<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/visuals_helper.php';

$companyName = getSetting('company_name', 'RMC BTP SARL');
$slogan = getSetting('slogan', 'Construire aujourd\'hui les solutions de demain');
$phonePrimary = getSetting('phone_primary', '(+237) 697 39 84 55');
$phoneSecondary = getSetting('phone_secondary', '(+237) 693 90 91 59');
$whatsappNumber = getSetting('whatsapp_number', '237697398455');
$emailContact = getSetting('email_contact', 'info@rmcbtp.com');
$address = getSetting('address', 'Yaoundé & Douala, Cameroun');
$openingHours = getSetting('opening_hours', 'Lun - Ven : 07h30 - 18h00 | Sam : 08h00 - 13h00');

$identityVisuals = getPageVisuals($pdo, 'identity');
$siteFavicon = getVisualInfo($identityVisuals, 'site_favicon', 'images/favicon-32x32.png', 'center center');

$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?= htmlspecialchars($pageTitle ?? ($companyName . ' | Bâtiment, Travaux Publics & Génie Civil au Cameroun & International')) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'RMC BTP SARL - Entreprise de référence en BTP, Génie Civil, Clés en main et Rénovation au Cameroun et à l\'international. Devis express et suivi rigoureux.') ?>">
	
	<!-- Favicon Officiel RMC BTP SARL (Multi-résolutions Navigateur & Mobile) -->
	<link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($siteFavicon['url']) ?>?v=<?= time() ?>">
	<link rel="shortcut icon" type="image/x-icon" href="<?= htmlspecialchars($siteFavicon['url']) ?>?v=<?= time() ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($siteFavicon['url']) ?>?v=<?= time() ?>">
	<link rel="icon" type="image/png" sizes="192x192" href="<?= htmlspecialchars($siteFavicon['url']) ?>?v=<?= time() ?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($siteFavicon['url']) ?>?v=<?= time() ?>">
	
	<!-- Polices Google Architecturales & Modernes (Plus Jakarta Sans) -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- Font Awesome & Icônes -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

	<!-- Feuilles de style Wilcon -->
	<link rel="stylesheet" href="css/animate.css">
	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/owl.theme.default.min.css">
	<link rel="stylesheet" href="css/magnific-popup.css">
	<link rel="stylesheet" href="css/bootstrap-datepicker.css">
	<link rel="stylesheet" href="css/jquery.timepicker.css">
	<link rel="stylesheet" href="css/flaticon.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/wow-theme.css?v=<?= time() ?>">
	<!-- Leaflet CSS Cartographie -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<!-- Modules Spectaculaires RMC BTP (3D BIM, Tracker Diaspora, Carte & Configurateur) -->
	<link rel="stylesheet" href="css/spectacular-modules.css?v=<?= time() ?>">

	<!-- Styles personnalisés RMC BTP (WhatsApp flottant, Before-After, Badges Bleus & Cyan) -->
	<style>
		.navbar-brand span.brand-title {
			font-size: 24px;
			font-weight: 900;
			letter-spacing: 0.5px;
			color: #0c2e60;
		}
		.navbar-brand small {
			display: block;
			font-size: 11px;
			font-weight: 600;
			color: #0072ff;
			text-transform: uppercase;
			letter-spacing: 1px;
		}
		.top .topper p span.text {
			font-weight: 500;
		}
		/* Bouton WhatsApp Flottant */
		.whatsapp-float-btn {
			position: fixed;
			bottom: 25px;
			left: 25px;
			background-color: #25d366;
			color: #fff !important;
			border-radius: 50px;
			padding: 12px 20px;
			font-size: 14px;
			font-weight: 700;
			box-shadow: 0 6px 20px rgba(37, 211, 102, 0.45);
			z-index: 9999;
			display: flex;
			align-items: center;
			gap: 10px;
			transition: all 0.3s ease;
			text-decoration: none;
		}
		.whatsapp-float-btn:hover {
			transform: translateY(-4px) scale(1.03);
			box-shadow: 0 10px 25px rgba(37, 211, 102, 0.6);
			color: #fff !important;
		}
		.whatsapp-float-btn i {
			font-size: 22px;
		}
		/* Avant / Après Slider */
		.before-after-container {
			position: relative;
			overflow: hidden;
			border-radius: 8px;
			box-shadow: 0 10px 30px rgba(0,0,0,0.12);
			height: 380px;
			background: #111;
		}
		.before-after-container img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.before-after-container .ba-label {
			position: absolute;
			top: 15px;
			padding: 4px 12px;
			border-radius: 4px;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 1px;
			color: #fff;
			z-index: 5;
		}
		.ba-label-before { left: 15px; background: rgba(220, 38, 38, 0.85); }
		.ba-label-after { right: 15px; background: rgba(16, 185, 129, 0.85); }
		
		/* Badges & Accents BTP */
		.badge-btp {
			background: #f96d00;
			color: #fff;
			padding: 6px 14px;
			font-size: 12px;
			font-weight: 700;
			border-radius: 4px;
			display: inline-block;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.badge-success-btp {
			background: #10b981;
			color: #fff;
			padding: 4px 10px;
			font-size: 11px;
			font-weight: 600;
			border-radius: 4px;
		}
	</style>
</head>
<body>

	<!-- Barre Supérieure Prestige BTP (Standing International) -->
	<div class="header-top-clean">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-8 col-md-7 d-flex align-items-center flex-wrap">
					<span class="mr-3 mr-xl-4 font-weight-medium text-dark">
						<i class="fa fa-map-marker mr-1" style="color: #f97316;"></i> <?= htmlspecialchars($address) ?>
					</span>
					<span class="d-none d-sm-inline mr-3 mr-xl-4">
						<i class="fa fa-envelope mr-1" style="color: #f97316;"></i> <a href="mailto:<?= htmlspecialchars($emailContact) ?>" class="text-dark font-weight-medium"><?= htmlspecialchars($emailContact) ?></a>
					</span>
					<span class="live-status-pill d-none d-xl-inline-flex">
						<span class="live-dot-green"></span>
						<span><?= htmlspecialchars($openingHours) ?></span>
					</span>
				</div>
				<div class="col-lg-4 col-md-5 d-flex align-items-center justify-content-md-end justify-content-between mt-1 mt-md-0">
					<span class="mr-3 small text-muted font-weight-bold d-none d-md-inline">
						<i class="fa fa-shield mr-1" style="color: #10b981;"></i> Agréé MINTP / BTP
					</span>
					<a href="admin/login.php" class="header-admin-pill" title="Espace Administrateur Sécurisé">
						<i class="fa fa-lock mr-1" style="color: #f97316;"></i> Espace Pro
					</a>
				</div>
			</div>
		</div>
	</div>
