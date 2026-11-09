<?php
require_once __DIR__ . '/config/db.php';

$articleId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND is_published = 1 LIMIT 1");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: blog.php");
    exit;
}

// Incrémenter les vues
$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$articleId]);

$pageTitle = htmlspecialchars($article['title']) . " | FEE BTP SARL";
$pageDescription = htmlspecialchars($article['summary'] ?? '');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$artImg = resolveImage($article['image'] ?? '', 'images/image_1.jpg');

// Autres articles récents
$recentStmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? AND is_published = 1 ORDER BY id DESC LIMIT 3");
$recentStmt->execute([$articleId]);
$recentArticles = $recentStmt->fetchAll();
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('<?= htmlspecialchars($artImg) ?>');" data-stellar-background-ratio="0.5">
	<div class="overlay" style="background: rgba(12,46,96,0.85);"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2">
					<span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> 
					<span class="mr-2"><a href="blog.php">Actualités <i class="fa fa-chevron-right"></i></a></span> 
					<span>Article</span>
				</p>
				<h1 class="mb-3 bread text-white font-weight-bold"><?= htmlspecialchars($article['title']) ?></h1>
			</div>
		</div>
	</div>
</section>

<!-- CONTENU ARTICLE -->
<section class="ftco-section">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 ftco-animate">
				<div class="mb-4">
					<span class="badge badge-primary px-3 py-2 mr-2"><?= htmlspecialchars($article['category'] ?? 'Chantiers') ?></span>
					<span class="text-muted small"><i class="fa fa-calendar mr-1"></i> <?= date('d F Y', strtotime($article['created_at'])) ?></span>
					<span class="text-muted small ml-3"><i class="fa fa-user mr-1"></i> <?= htmlspecialchars($article['author'] ?? 'Direction FEE BTP') ?></span>
				</div>
				
				<div class="mb-4">
					<img src="<?= htmlspecialchars($artImg) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="img-fluid rounded shadow-sm w-100" style="max-height: 440px; object-fit: cover;">
				</div>

				<div class="text-muted mb-4 lead" style="font-size: 1.1rem; line-height: 1.8;">
					<?= nl2br(htmlspecialchars($article['content'] ?? $article['summary'] ?? '')) ?>
				</div>

				<hr class="my-5">

				<!-- Bouton retour & Partage -->
				<div class="d-flex justify-content-between align-items-center flex-wrap">
					<a href="blog.php" class="btn btn-outline-primary font-weight-bold mb-2">
						<i class="fa fa-arrow-left mr-1"></i> Retour aux actualités
					</a>
					<a href="contact.php?type=devis" class="btn btn-primary font-weight-bold mb-2">
						<i class="fa fa-calculator mr-1"></i> Discuter d'un projet similaire
					</a>
				</div>
			</div>

			<!-- Sidebar -->
			<div class="col-lg-4 ftco-animate">
				<div class="sidebar-box p-4 bg-light rounded shadow-sm mb-4">
					<h4 class="font-weight-bold text-dark mb-3">Articles Récents</h4>
					<?php foreach ($recentArticles as $r): 
						$rImg = resolveImage($r['image'] ?? '', 'images/image_1.jpg');
					?>
						<div class="block-21 mb-3 d-flex">
							<a class="blog-img mr-3 rounded" style="background-image: url('<?= htmlspecialchars($rImg) ?>'); width: 80px; height: 80px; min-width: 80px; background-size: cover; background-position: center;" href="blog-single.php?id=<?= $r['id'] ?>"></a>
							<div class="text">
								<h6 class="heading font-weight-bold mb-1">
									<a href="blog-single.php?id=<?= $r['id'] ?>" class="text-dark"><?= htmlspecialchars($r['title']) ?></a>
								</h6>
								<div class="meta small text-muted">
									<div><span class="fa fa-calendar mr-1"></span> <?= date('d M Y', strtotime($r['created_at'])) ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="sidebar-box p-4 bg-primary text-white rounded shadow-sm text-center">
					<h4 class="text-white font-weight-bold mb-3">Besoin d'un Conseil BTP ?</h4>
					<p class="small text-white-50 mb-4">Nos spécialistes sont disponibles pour échanger sur votre projet de construction ou rénovation.</p>
					<a href="contact.php" class="btn btn-white font-weight-bold btn-block">Nous Contacter</a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
