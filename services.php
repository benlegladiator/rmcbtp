<?php
$pageTitle = "Nos Métiers & Services BTP | RMC BTP SARL";
$pageDescription = "Catalogue des prestations de RMC BTP SARL : Gros œuvre, Clés en main, Second œuvre, Rénovation, Études techniques et Approvisionnement direct avec SIMS Quincaillerie et SIMS Décor.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
$allServices = $stmt->fetchAll();
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('images/IMG-20261029-WA0101.jpg'); background-position: center;" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2"><span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> <span>Services <i class="fa fa-chevron-right"></i></span></p>
				<h1 class="mb-3 bread text-white font-weight-bold">Catalogue des Prestations & Corps d'État BTP</h1>
			</div>
		</div>
	</div>
</section>

<!-- LISTE DÉTAILLÉE DES SERVICES -->
<section class="ftco-section bg-light">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold">Ingénierie & Travaux</span>
				<h2 class="mb-4">Des Solutions Sur Mesure pour Chaque Étape</h2>
				<p class="text-muted">Chaque prestation bénéficie de l'expertise de notre bureau d'études et d'une rigueur d'ingénierie reconnue.</p>
			</div>
		</div>
		<div class="row">
			<?php 
			$idx = 1;
			foreach ($allServices as $srv): 
				$srvImg = resolveImage($srv['image'] ?? '', 'images/services-' . $idx . '.jpg');
			?>
				<div class="col-lg-4 col-md-6 d-flex ftco-animate mb-4">
					<div class="card border-0 shadow-sm w-100 rounded overflow-hidden d-flex flex-column justify-content-between bg-white">
						<div>
							<div style="height: 220px; background-image: url('<?= htmlspecialchars($srvImg) ?>'); background-size: cover; background-position: center; position: relative;">
								<div class="position-absolute" style="top: 15px; left: 15px; background: rgba(12,46,96,0.9); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
									<i class="fa <?= htmlspecialchars($srv['icon'] ?? 'fa-wrench') ?>"></i>
								</div>
							</div>
							<div class="card-body p-4">
								<h4 class="card-title font-weight-bold text-dark mb-3"><?= htmlspecialchars($srv['title']) ?></h4>
								<p class="card-text text-muted small mb-3">
									<?= htmlspecialchars($srv['full_desc'] ?? $srv['short_desc'] ?? '') ?>
								</p>
							</div>
						</div>
						<div class="card-footer bg-white border-0 p-4 pt-0">
							<a href="contact.php?type=devis&service=<?= urlencode($srv['title']) ?>" class="btn btn-primary btn-block font-weight-bold py-2">
								<i class="fa fa-calculator mr-1"></i> Demander une cotation
							</a>
						</div>
					</div>
				</div>
			<?php 
				$idx++;
				if ($idx > 6) $idx = 1;
			endforeach; 
			?>
		</div>
	</div>
</section>

<!-- PROCESSUS DE GESTION DE CHANTIER (MÉTHODOLOGIE BTP) -->
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold">Notre Démarche</span>
				<h2 class="mb-4">Un Processus d'Exécution Structuré en 4 Étapes</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 col-6 text-center mb-4 ftco-animate">
				<div class="p-4 border rounded shadow-sm h-100 bg-white">
					<div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">1</div>
					<h5 class="font-weight-bold mb-2">Étude & Devis</h5>
					<p class="small text-muted mb-0">Analyse de faisabilité, visite du terrain et chiffrage transparent sous 24 à 48h.</p>
				</div>
			</div>
			<div class="col-md-3 col-6 text-center mb-4 ftco-animate">
				<div class="p-4 border rounded shadow-sm h-100 bg-white">
					<div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">2</div>
					<h5 class="font-weight-bold mb-2">Plans & Métrés</h5>
					<p class="small text-muted mb-0">Plans d'architectes, notes de calcul béton armé et calepinage des approvisionnements.</p>
				</div>
			</div>
			<div class="col-md-3 col-6 text-center mb-4 ftco-animate">
				<div class="p-4 border rounded shadow-sm h-100 bg-white">
					<div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">3</div>
					<h5 class="font-weight-bold mb-2">Exécution Terrain</h5>
					<p class="small text-muted mb-0">Déploiement des engins, approvisionnement des matériaux certifiés et pilotage strict.</p>
				</div>
			</div>
			<div class="col-md-3 col-6 text-center mb-4 ftco-animate">
				<div class="p-4 border rounded shadow-sm h-100 bg-white">
					<div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">4</div>
					<h5 class="font-weight-bold mb-2">Livrance & Garantie</h5>
					<p class="small text-muted mb-0">Réception des travaux avec procès-verbal et couverture par la garantie décennale.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
