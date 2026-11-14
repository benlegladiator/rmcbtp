<?php
require_once __DIR__ . '/config/db.php';

$projectId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: project.php");
    exit;
}

$pageTitle = htmlspecialchars($project['title']) . " | Réalisation RMC BTP SARL";
$pageDescription = "Chantier BTP réalisé par RMC BTP SARL à " . htmlspecialchars($project['location'] ?? 'Cameroun') . " : " . htmlspecialchars($project['title']);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$mainImg = resolveImage($project['main_image'] ?? '', 'images/project-1.jpg');
$beforeImg = resolveImage($project['before_image'] ?? '', 'images/about.jpg');
$afterImg = resolveImage($project['after_image'] ?? $project['main_image'] ?? '', 'images/project-1.jpg');

// Galerie complémentaire
$galleryStmt = $pdo->prepare("SELECT * FROM project_gallery WHERE project_id = ?");
$galleryStmt->execute([$projectId]);
$gallery = $galleryStmt->fetchAll();

// Regroupement de toutes les images pour le carrousel défilant automatique
$allDetailImages = [$mainImg];
if (!empty($gallery)) {
	foreach ($gallery as $g) {
		if (!empty($g['image_url'])) {
			$allDetailImages[] = resolveImage($g['image_url'], 'images/project-1.jpg');
		}
	}
}
$allDetailImages = array_values(array_unique($allDetailImages));
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('<?= htmlspecialchars($mainImg) ?>'); background-position: center top !important;" data-stellar-background-ratio="0.5">
	<div class="overlay" style="background: rgba(12,46,96,0.85);"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2">
					<span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> 
					<span class="mr-2"><a href="project.php">Chantiers <i class="fa fa-chevron-right"></i></a></span> 
					<span><?= htmlspecialchars($project['title']) ?></span>
				</p>
				<h1 class="mb-3 bread text-white font-weight-bold"><?= htmlspecialchars($project['title']) ?></h1>
			</div>
		</div>
	</div>
</section>

<!-- CONTENU DU PROJET -->
<section class="ftco-section">
	<div class="container">
		<div class="row">
			<!-- Colonne Principale -->
			<div class="col-lg-8 ftco-animate">
				
				<!-- Image Principale ou Comparatif Avant/Après -->
				<?php if (!empty($project['before_image']) && !empty($project['after_image'])): ?>
					<div class="mb-4">
						<h4 class="font-weight-bold text-dark mb-3">
							<i class="fa fa-columns text-primary mr-2"></i> Comparatif Avant / Après Chantier
						</h4>
						<div class="row">
							<div class="col-md-6 mb-3">
								<div class="before-after-container">
									<span class="ba-label ba-label-before">État Initial (Avant)</span>
									<img src="<?= htmlspecialchars($beforeImg) ?>" alt="Chantier Avant Travaux">
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="before-after-container">
									<span class="ba-label ba-label-after">Ouvrage Livré (Après)</span>
									<img src="<?= htmlspecialchars($afterImg) ?>" alt="Chantier Après Travaux">
								</div>
							</div>
						</div>
					</div>
				<?php else: ?>
					<?php if (count($allDetailImages) > 1): ?>
						<!-- Carrousel Défilant Automatique du Chantier -->
						<div id="projectHeroCarousel" class="carousel slide carousel-fade mb-4 shadow-sm rounded overflow-hidden position-relative" data-ride="carousel" data-interval="3500" data-pause="false" style="background: #0c2e60; border-radius: 14px;">
							<ol class="carousel-indicators mb-2" style="z-index: 3;">
								<?php foreach ($allDetailImages as $idx => $dImg): ?>
									<li data-target="#projectHeroCarousel" data-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>" style="width: 8px; height: 8px; border-radius: 50%;"></li>
								<?php endforeach; ?>
							</ol>
							<div class="carousel-inner" style="height: 480px;">
								<?php foreach ($allDetailImages as $idx => $dImg): ?>
									<div class="carousel-item h-100 <?= $idx === 0 ? 'active' : '' ?>">
										<a href="<?= htmlspecialchars($dImg) ?>" class="image-popup d-block h-100" title="<?= htmlspecialchars($project['title']) ?> - Vue <?= $idx + 1 ?>">
											<img src="<?= htmlspecialchars($dImg) ?>" alt="<?= htmlspecialchars($project['title']) ?> - Photo <?= $idx + 1 ?>" class="d-block w-100 h-100" style="object-fit: cover; object-position: center top;">
										</a>
									</div>
								<?php endforeach; ?>
							</div>
							<a class="carousel-control-prev" href="#projectHeroCarousel" role="button" data-slide="prev" style="width: 8%; opacity: 0.8;">
								<span class="carousel-control-prev-icon" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));"></span>
								<span class="sr-only">Précédent</span>
							</a>
							<a class="carousel-control-next" href="#projectHeroCarousel" role="button" data-slide="next" style="width: 8%; opacity: 0.8;">
								<span class="carousel-control-next-icon" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));"></span>
								<span class="sr-only">Suivant</span>
							</a>
							<span class="badge badge-dark position-absolute" style="bottom: 12px; right: 15px; z-index: 4; font-size: 11px; background: rgba(12,46,96,0.85); backdrop-filter: blur(4px); color: #fff; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
								<i class="fa fa-play mr-1 text-warning"></i> <?= count($allDetailImages) ?> photos • Défilement automatique
							</span>
						</div>
					<?php else: ?>
						<div class="mb-4">
							<img src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="img-fluid rounded shadow-sm w-100" style="max-height: 480px; object-fit: cover;">
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<!-- Description du Chantier -->
				<h3 class="font-weight-bold mb-3">Présentation Technique de l'Ouvrage</h3>
				<div class="text-muted mb-4 lead" style="font-size: 1.05rem; line-height: 1.7;">
					<?= nl2br(htmlspecialchars($project['description'] ?? $project['short_desc'] ?? '')) ?>
				</div>

				<!-- Vidéo Immersive / Drone si renseignée -->
				<?php if (!empty($project['video_url'])): ?>
					<div class="mb-5 mt-4">
						<h4 class="font-weight-bold text-dark mb-3">
							<i class="fa fa-video-camera text-danger mr-2"></i> Immersion Vidéo & Vue Drone du Chantier
						</h4>
						<div class="embed-responsive embed-responsive-16by9 rounded shadow overflow-hidden">
							<?php 
							$videoUrl = $project['video_url'];
							if (strpos($videoUrl, 'youtube.com/watch') !== false) {
								parse_str(parse_url($videoUrl, PHP_URL_QUERY), $query);
								$videoId = $query['v'] ?? '';
								$videoUrl = "https://www.youtube.com/embed/" . $videoId;
							}
							?>
							<iframe class="embed-responsive-item" src="<?= htmlspecialchars($videoUrl) ?>" allowfullscreen></iframe>
						</div>
					</div>
				<?php endif; ?>

				<!-- Galerie Photos Additionnelle -->
				<?php if (!empty($gallery)): ?>
					<h4 class="font-weight-bold text-dark mb-3">
						<i class="fa fa-images text-primary mr-2"></i> Galerie Photos Détaillées du Chantier
					</h4>
					<div class="row">
						<?php foreach ($gallery as $gImg): 
							$gPath = resolveImage($gImg['image_url'], 'images/project-1.jpg');
						?>
							<div class="col-md-4 col-6 mb-3">
								<a href="<?= htmlspecialchars($gPath) ?>" class="image-popup d-block rounded overflow-hidden shadow-sm" style="height: 140px;">
									<img src="<?= htmlspecialchars($gPath) ?>" alt="<?= htmlspecialchars($gImg['caption'] ?? '') ?>" class="w-100 h-100" style="object-fit: cover;">
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- Barre Latérale : Fiche Fiche Technique -->
			<div class="col-lg-4 ftco-animate">
				<div class="card border-0 shadow-sm rounded p-4 bg-light mb-4 sticky-top" style="top: 90px; z-index: 1;">
					<h4 class="font-weight-bold mb-3 border-bottom pb-2 text-dark">
						<i class="fa fa-clipboard text-primary mr-2"></i> Fiche d'Identité
					</h4>
					<ul class="list-unstyled mb-4">
						<li class="py-2 border-bottom d-flex justify-content-between">
							<span class="text-muted"><i class="fa fa-folder-open mr-2 text-primary"></i> Catégorie :</span>
							<strong class="text-dark"><?= htmlspecialchars($project['category'] ?? 'BTP') ?></strong>
						</li>
						<li class="py-2 border-bottom d-flex justify-content-between">
							<span class="text-muted"><i class="fa fa-user mr-2 text-primary"></i> Maître d'ouvrage :</span>
							<strong class="text-dark"><?= htmlspecialchars($project['client_name'] ?? 'Particulier / Privé') ?></strong>
						</li>
						<li class="py-2 border-bottom d-flex justify-content-between">
							<span class="text-muted"><i class="fa fa-map-marker mr-2 text-primary"></i> Localisation :</span>
							<strong class="text-dark"><?= htmlspecialchars($project['location'] ?? 'Cameroun') ?></strong>
						</li>
						<?php if (!empty($project['duration'])): ?>
							<li class="py-2 border-bottom d-flex justify-content-between">
								<span class="text-muted"><i class="fa fa-calendar mr-2 text-primary"></i> Durée d'exécution :</span>
								<strong class="text-dark"><?= htmlspecialchars($project['duration']) ?></strong>
							</li>
						<?php endif; ?>
						<?php if (!empty($project['surface'])): ?>
							<li class="py-2 border-bottom d-flex justify-content-between">
								<span class="text-muted"><i class="fa fa-arrows-alt mr-2 text-primary"></i> Surface couverte :</span>
								<strong class="text-dark"><?= htmlspecialchars($project['surface']) ?></strong>
							</li>
						<?php endif; ?>
						<li class="py-2 border-bottom d-flex justify-content-between">
							<span class="text-muted"><i class="fa fa-check-circle mr-2 text-success"></i> Statut de livraison :</span>
							<span class="badge badge-success px-2 py-1 font-weight-bold"><?= htmlspecialchars($project['status'] ?? 'Terminé') ?></span>
						</li>
					</ul>

					<a href="contact.php?type=devis&projet=<?= urlencode($project['title']) ?>" class="btn btn-primary btn-block font-weight-bold py-2 mb-2">
						<i class="fa fa-calculator mr-1"></i> Chiffrer un projet similaire
					</a>
					<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsappNumber) ?>?text=Bonjour,%20je%20vous%20contacte%20concernant%20le%20projet%20: <?= urlencode($project['title']) ?>" class="btn btn-success btn-block font-weight-bold py-2" target="_blank">
						<i class="fab fa-whatsapp mr-1"></i> Discuter sur WhatsApp
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
