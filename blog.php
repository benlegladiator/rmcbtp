<?php
$pageTitle = "Actualités & Blog BTP | RMC BTP SARL";
$pageDescription = "Suivi des chantiers en cours, innovations dans la construction et conseils pratiques d'ingénierie par RMC BTP SARL.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/visuals_helper.php';

$blogVisuals = getPageVisuals($pdo, 'blog');
$blogBanner = getVisualInfo($blogVisuals, 'blog_banner', 'images/IMG-20261029-WA0103.jpg', 'center center');

$stmt = $pdo->query("SELECT * FROM articles WHERE is_published = 1 ORDER BY id DESC");
$articles = $stmt->fetchAll();
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('<?= htmlspecialchars($blogBanner['url']) ?>'); background-position: <?= htmlspecialchars($blogBanner['focal']) ?>;" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2"><span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> <span>Blog <i class="fa fa-chevron-right"></i></span></p>
				<h1 class="mb-3 bread text-white font-weight-bold">Actualités, Chantiers & Innovations BTP</h1>
			</div>
		</div>
	</div>
</section>

<!-- LISTE DES ARTICLES -->
<section class="ftco-section bg-light">
	<div class="container">
		<div class="row">
			<?php 
			$artIdx = 1;
			foreach ($articles as $art): 
				$artImg = resolveImage($art['image'] ?? '', 'images/image_' . $artIdx . '.jpg');
			?>
				<div class="col-md-4 d-flex ftco-animate mb-4">
					<div class="blog-entry align-self-stretch shadow-sm rounded overflow-hidden w-100 bg-white d-flex flex-column justify-content-between">
						<div>
							<a href="blog-single.php?id=<?= $art['id'] ?>" class="block-20" style="background-image: url('<?= htmlspecialchars($artImg) ?>'); height: 220px; display: block; background-size: cover; background-position: center;">
							</a>
							<div class="text p-4 d-block">
								<div class="meta mb-2">
									<div><span class="fa fa-calendar mr-1 text-primary"></span> <?= date('d M Y', strtotime($art['created_at'])) ?></div>
									<div><span class="fa fa-tag mr-1 text-primary"></span> <?= htmlspecialchars($art['category'] ?? 'Actualité') ?></div>
								</div>
								<h4 class="heading font-weight-bold"><a href="blog-single.php?id=<?= $art['id'] ?>" class="text-dark"><?= htmlspecialchars($art['title']) ?></a></h4>
								<p class="text-muted small"><?= htmlspecialchars($art['summary'] ?? '') ?></p>
							</div>
						</div>
						<div class="p-4 pt-0">
							<a href="blog-single.php?id=<?= $art['id'] ?>" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
								Lire l'article complet <i class="fa fa-angle-right ml-1"></i>
							</a>
						</div>
					</div>
				</div>
			<?php 
				$artIdx++;
				if ($artIdx > 3) $artIdx = 1;
			endforeach; 
			?>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
