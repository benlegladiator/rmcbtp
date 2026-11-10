<?php
$pageTitle = "Contact & Demande de Cotation BTP | RMC BTP SARL";
$pageDescription = "Contactez RMC BTP SARL : formulaire de devis express avec téléversement de plans (PDF/images), géolocalisation Yaoundé & Douala et assistance directe.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/visuals_helper.php';

$contactVisuals = getPageVisuals($pdo, 'contact');
$contactBanner = getVisualInfo($contactVisuals, 'contact_banner', 'images/IMG-20261029-WA0099.jpg', 'center center');
$contactOffice = getVisualInfo($contactVisuals, 'contact_office', 'images/about.jpg', 'center center');

$defaultType = $_GET['type'] ?? 'contact';
$prefilledService = $_GET['service'] ?? '';
$prefilledProject = $_GET['projet'] ?? '';
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('<?= htmlspecialchars($contactBanner['url']) ?>'); background-position: <?= htmlspecialchars($contactBanner['focal']) ?>;" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2"><span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> <span>Contact & Devis <i class="fa fa-chevron-right"></i></span></p>
				<h1 class="mb-3 bread text-white font-weight-bold">Contactez Notre Bureau Technique à Yaoundé</h1>
			</div>
		</div>
	</div>
</section>

<!-- SECTION FORMULAIRE & INFORMATIONS -->
<section class="ftco-section bg-light">
	<div class="container">

		<!-- Alertes Flash -->
		<?php if (!empty($_SESSION['flash_success'])): ?>
			<div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
				<i class="fa fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<?php unset($_SESSION['flash_success']); ?>
		<?php endif; ?>

		<?php if (!empty($_SESSION['flash_error'])): ?>
			<div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
				<i class="fa fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<?php unset($_SESSION['flash_error']); ?>
		<?php endif; ?>

		<div class="row">
			<!-- Formulaire de Devis & Contact -->
			<div class="col-lg-7 ftco-animate mb-4">
				<div class="bg-white p-4 p-md-5 rounded shadow-sm">
					<h3 class="font-weight-bold text-dark mb-2">Formulaire de Devis & Étude de Prix</h3>
					<p class="text-muted small mb-4">
						Transmettez-nous les détails de vos travaux ainsi que vos plans d'architecte ou pièces jointes (PDF, JPEG). Nos métreurs vous feront un retour sous 24h.
					</p>

					<form action="process-contact.php" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="form_type" value="<?= $defaultType === 'devis' ? 'Demande de Devis' : 'Prise de Contact' ?>">

						<div class="row">
							<div class="col-md-6 form-group">
								<label class="font-weight-bold small">Nom & Prénom *</label>
								<input type="text" name="full_name" class="form-control" placeholder="Ex: Jean Paul Ndjock" required>
							</div>
							<div class="col-md-6 form-group">
								<label class="font-weight-bold small">Numéro de Téléphone (WhatsApp) *</label>
								<input type="tel" name="phone" class="form-control" placeholder="(+237) 6..." required>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 form-group">
								<label class="font-weight-bold small">Adresse Email</label>
								<input type="email" name="email" class="form-control" placeholder="adresse@domaine.com">
							</div>
							<div class="col-md-6 form-group">
								<label class="font-weight-bold small">Corps d'État Concerné *</label>
								<select name="subject" class="form-control" required>
									<option value="Gros Œuvre & Maçonnerie" <?= $prefilledService === 'Gros Œuvre & Maçonnerie Générale' ? 'selected' : '' ?>>Gros Œuvre & Maçonnerie</option>
									<option value="Génie Civil & VRD" <?= $prefilledService === 'Génie Civil & VRD (Voiries et Réseaux)' ? 'selected' : '' ?>>Génie Civil & VRD (Voiries)</option>
									<option value="Second Œuvre & Finitions" <?= $prefilledService === 'Second Œuvre & Finitions Haut Standing' ? 'selected' : '' ?>>Second Œuvre & Finitions</option>
									<option value="Rénovation & Réhabilitation" <?= $prefilledService === 'Rénovation & Réhabilitation Immobilière' ? 'selected' : '' ?>>Rénovation & Réhabilitation</option>
									<option value="Études Techniques & Architecture" <?= $prefilledService === 'Études Techniques, Plans & Architecture' ? 'selected' : '' ?>>Études Techniques & Architecture</option>
									<option value="Conduite & Pilotage de Travaux">Conduite & Pilotage de Travaux</option>
									<option value="Autre Demande Spécifique">Autre Demande Spécifique</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="font-weight-bold small">Description Détaillée du Projet *</label>
							<textarea name="message" class="form-control" rows="5" placeholder="Indiquez la localisation exacte (ville, quartier), la surface approximative, le délai envisagé et tout détail technique utile..." required><?= !empty($prefilledProject) ? "Bonjour, je souhaite obtenir un devis pour un projet similaire à : " . htmlspecialchars($prefilledProject) : "" ?></textarea>
						</div>

						<div class="form-group border rounded p-3 bg-light">
							<label class="font-weight-bold small text-dark d-block">
								<i class="fa fa-paperclip text-primary mr-1"></i> Joindre un Document / Plan (PDF, DWG, Image)
							</label>
							<small class="text-muted d-block mb-2">Taille maximale : 15 Mo. Formats acceptés : .pdf, .jpg, .png, .docx, .dwg</small>
							<input type="file" name="attachment" class="form-control-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.dwg">
						</div>

						<button type="submit" class="btn btn-primary btn-block font-weight-bold py-3 mt-4">
							<i class="fa fa-paper-plane mr-2"></i> Envoyer ma Demande de Cotation
						</button>
					</form>
				</div>
			</div>

			<!-- Coordonnées & Accompagnement -->
			<div class="col-lg-5 ftco-animate">
				<!-- Carte Coordonnées -->
				<div class="bg-white p-4 p-md-5 rounded shadow-sm mb-4">
					<h4 class="font-weight-bold text-dark mb-4">Coordonnées Officielles</h4>
					
					<div class="d-flex mb-3">
						<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; min-width: 44px;">
							<i class="fa fa-map-marker" style="font-size: 18px;"></i>
						</div>
						<div>
							<h6 class="font-weight-bold mb-1">Siège Social</h6>
							<p class="text-muted small mb-0"><?= htmlspecialchars($address) ?></p>
						</div>
					</div>

					<div class="d-flex mb-3">
						<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; min-width: 44px;">
							<i class="fa fa-phone" style="font-size: 18px;"></i>
						</div>
						<div>
							<h6 class="font-weight-bold mb-1">Lignes Téléphoniques</h6>
							<p class="text-dark font-weight-bold mb-0"><?= htmlspecialchars($phonePrimary) ?></p>
							<p class="text-muted small mb-0"><?= htmlspecialchars($phoneSecondary) ?></p>
						</div>
					</div>

					<div class="d-flex mb-3">
						<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; min-width: 44px;">
							<i class="fab fa-whatsapp" style="font-size: 20px;"></i>
						</div>
						<div>
							<h6 class="font-weight-bold mb-1">WhatsApp Direct</h6>
							<p class="text-muted small mb-1">Échangez en direct avec un conseiller technique :</p>
							<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsappNumber) ?>?text=Bonjour%20RMC%20BTP,%20je%20souhaite%20une%20cotation." class="btn btn-success btn-sm font-weight-bold" target="_blank">
								<i class="fab fa-whatsapp mr-1"></i> Ouvrir la discussion WhatsApp
							</a>
						</div>
					</div>

					<div class="d-flex mb-3">
						<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; min-width: 44px;">
							<i class="fa fa-envelope" style="font-size: 18px;"></i>
						</div>
						<div>
							<h6 class="font-weight-bold mb-1">Emails Professionnels</h6>
							<p class="text-muted small mb-0"><?= htmlspecialchars($emailContact) ?></p>
							<p class="text-muted small mb-0"><?= htmlspecialchars(getSetting('email_direction', 'direction@rmcbtpsarl.com')) ?></p>
						</div>
					</div>

					<div class="d-flex">
						<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; min-width: 44px;">
							<i class="fa fa-clock-o" style="font-size: 18px;"></i>
						</div>
						<div>
							<h6 class="font-weight-bold mb-1">Horaires de Travail</h6>
							<p class="text-muted small mb-0"><?= htmlspecialchars($openingHours) ?></p>
						</div>
					</div>
				</div>

				<!-- Localisation Interactive Yaoundé & Siège -->
				<div class="bg-white p-3 rounded shadow-sm">
					<h5 class="font-weight-bold text-dark mb-2 px-2">
						<i class="fa fa-building text-primary mr-1"></i> Bureau & Direction Technique
					</h5>
					<div class="rounded overflow-hidden mb-3" style="height: 190px;">
						<img src="<?= htmlspecialchars($contactOffice['url']) ?>" 
						     alt="Siège Social RMC BTP SARL" 
						     class="w-100 h-100" 
						     style="object-fit: cover; object-position: <?= htmlspecialchars($contactOffice['focal']) ?>;">
					</div>
					<h6 class="font-weight-bold text-muted small px-2 mb-2"><i class="fa fa-map-marked-alt mr-1"></i> Plan d'accès :</h6>
					<div class="rounded overflow-hidden" style="height: 230px;">
						<iframe 
							src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127357.54637651817!2d11.454228966847253!3d3.8710344849310617!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x108bcf7a309a7977%3A0x7f54bad35e693c51!2sYaound%C3%A9%2C%20Cameroun!5e0!3m2!1sfr!2scm!4v1710000000000!5m2!1sfr!2scm" 
							width="100%" 
							height="100%" 
							style="border:0;" 
							allowfullscreen="" 
							loading="lazy" 
							referrerpolicy="no-referrer-when-downgrade">
						</iframe>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
