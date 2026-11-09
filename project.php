<?php
$pageTitle = "Nos Chantiers & Réalisations BTP | FEE BTP SARL";
$pageDescription = "Découvrez le portfolio des chantiers et réalisations de FEE BTP SARL : galeries photos HD, comparatifs avant/après et vidéos immersives.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$categoryFilter = $_GET['cat'] ?? 'all';

$sql = "SELECT * FROM projects";
$params = [];
if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
    $sql .= " WHERE category = ?";
    $params[] = $categoryFilter;
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Récupérer toutes les catégories distinctes
$catsStmt = $pdo->query("SELECT DISTINCT category FROM projects WHERE category IS NOT NULL AND category != ''");
$categories = $catsStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('images/IMG-20261029-WA0129.jpg'); background-position: center;" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2"><span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> <span>Portfolio <i class="fa fa-chevron-right"></i></span></p>
				<h1 class="mb-3 bread text-white font-weight-bold">Nos Chantiers & Réalisations BTP</h1>
			</div>
		</div>
	</div>
</section>

<!-- GALERIE & FILTRES CATÉGORIES -->
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-4">
			<div class="col-md-10 text-center">
				<div class="btn-group flex-wrap shadow-sm rounded overflow-hidden" role="group">
					<a href="project.php" class="btn <?= $categoryFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> px-4 py-2 font-weight-bold">
						Tous les Chantiers
					</a>
					<?php foreach ($categories as $cat): ?>
						<a href="project.php?cat=<?= urlencode($cat) ?>" class="btn <?= $categoryFilter === $cat ? 'btn-primary' : 'btn-outline-primary' ?> px-3 py-2 font-weight-bold">
							<?= htmlspecialchars($cat) ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="row mt-4">
			<?php if (empty($projects)): ?>
				<div class="col-12 text-center py-5">
					<i class="fa fa-folder-open text-muted" style="font-size: 48px;"></i>
					<p class="text-muted mt-3">Aucun projet trouvé dans cette catégorie pour le moment.</p>
					<a href="project.php" class="btn btn-primary btn-sm">Réinitialiser les filtres</a>
				</div>
			<?php else: ?>
				<?php 
				$projCount = 1;
				foreach ($projects as $proj): 
					$mainImg = resolveImage($proj['main_image'] ?? '', 'images/project-' . $projCount . '.jpg');
				?>
					<div class="col-lg-4 col-md-6 ftco-animate mb-4">
						<div class="project-entry project-card-3d rounded shadow-sm overflow-hidden bg-white h-100 d-flex flex-column justify-content-between">
							<div>
								<div class="project-img position-relative" style="height: 275px; background-image: url('<?= htmlspecialchars($mainImg) ?>'); background-size: cover; background-position: center top !important;">
									<span class="badge badge-primary position-absolute" style="top: 15px; left: 15px; padding: 6px 10px; font-size: 11px;">
										<?= htmlspecialchars($proj['category'] ?? 'BTP') ?>
									</span>
									<?php if (!empty($proj['before_image'])): ?>
										<span class="badge badge-warning position-absolute text-dark font-weight-bold" style="top: 15px; right: 15px; padding: 6px 10px; font-size: 11px;">
											<i class="fa fa-columns mr-1"></i> Avant / Après
										</span>
									<?php endif; ?>
									<a href="<?= htmlspecialchars($mainImg) ?>" class="image-popup d-flex justify-content-center align-items-center" style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(12,46,96,0.5); opacity:0; transition: all 0.3s ease;">
										<span class="fa fa-expand text-white" style="font-size: 26px;"></span>
									</a>
								</div>
								<div class="p-3">
									<h5 class="font-weight-bold mb-1">
										<a href="project-details.php?id=<?= $proj['id'] ?>" class="text-dark">
											<?= htmlspecialchars($proj['title']) ?>
										</a>
									</h5>
									<p class="small text-muted mb-2">
										<i class="fa fa-map-marker text-primary mr-1"></i> <?= htmlspecialchars($proj['location'] ?? 'Yaoundé & Cameroun') ?>
										• Statut : <span class="badge badge-success-btp"><?= htmlspecialchars($proj['status'] ?? 'Terminé') ?></span>
									</p>
									<p class="text-muted small mb-0">
										<?= htmlspecialchars(mb_strimwidth($proj['short_desc'] ?? $proj['description'] ?? '', 0, 110, '...')) ?>
									</p>
								</div>
							</div>
							<div class="p-3 pt-0">
								<hr class="my-2">
								<a href="project-details.php?id=<?= $proj['id'] ?>" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
									<i class="fa fa-info-circle mr-1"></i> Fiche détaillée & Avant/Après
								</a>
							</div>
						</div>
					</div>
				<?php 
					$projCount++;
					if ($projCount > 6) $projCount = 1;
				endforeach; 
				?>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
