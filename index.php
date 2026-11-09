<?php
$pageTitle = "RMC BTP SARL | Bâtiment, Travaux Publics, Clés en Main & Génie Civil";
$pageDescription = "RMC BTP SARL : Entreprise de référence en BTP et Génie Civil au Cameroun et à l'international. Maisons clés en main, gros œuvre, second œuvre, études architecturales et pilotage de chantiers.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Récupérer les services
$servicesStmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6");
$services = $servicesStmt->fetchAll();

// Récupérer les projets mis en avant
$projectsStmt = $pdo->query("SELECT * FROM projects WHERE is_featured = 1 ORDER BY id DESC LIMIT 6");
$projects = $projectsStmt->fetchAll();

// Récupérer les témoignages
$testimonialsStmt = $pdo->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC LIMIT 4");
$testimonials = $testimonialsStmt->fetchAll();

// Récupérer les articles récents
$articlesStmt = $pdo->query("SELECT * FROM articles WHERE is_published = 1 ORDER BY id DESC LIMIT 3");
$articles = $articlesStmt->fetchAll();

// Chiffres clés
$expYears = getSetting('stats_experience', '12');
$totalProjects = getSetting('stats_projects', '165');
$satisfaction = getSetting('stats_satisfaction', '99');
$equipments = getSetting('stats_equipments', '48');
?>

<!-- Alertes Flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
	<div class="container mt-3">
		<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
			<i class="fa fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
	<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
	<div class="container mt-3">
		<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
			<i class="fa fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
	<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ========================================================================
     1. HERO BANNER CINÉMATIQUE HAUT DE GAMME (EFFET KEN BURNS & ROTATION)
     ======================================================================== -->
<div id="heroHomeCarousel" class="carousel slide carousel-fade position-relative overflow-hidden" data-ride="carousel" data-interval="7500">
	<!-- Canvas 3D Interactif : Maillage de Structure & Poutres BTP en Fil de Fer Réactif -->
	<canvas id="feeHero3DCanvas"></canvas>

	<!-- Indicateurs -->
	<ol class="carousel-indicators" style="bottom: 30px; z-index: 10;">
		<li data-target="#heroHomeCarousel" data-slide-to="0" class="active"></li>
		<li data-target="#heroHomeCarousel" data-slide-to="1"></li>
		<li data-target="#heroHomeCarousel" data-slide-to="2"></li>
	</ol>

	<div class="carousel-inner">
		<!-- SLIDE 1 : Grande Réalisation RMC BTP (Direction & Ingénierie) -->
		<div class="carousel-item active">
			<section class="hero-wrap js-fullheight kenburns-slide position-relative">
				<?php if (file_exists(__DIR__ . '/videos/chantier-btp-4k.mp4')): ?>
				<div class="hero-video-container">
					<video id="feeHeroCinematicVideo" class="hero-video-bg" autoplay muted loop playsinline preload="auto" poster="images/_JMP6225.jpg">
						<source src="videos/chantier-btp-4k.mp4" type="video/mp4">
					</video>
				</div>
				<?php endif; ?>
				<!-- Fond Image Haute Fidélité RMC BTP _JMP6225.jpg -->
				<div class="kenburns-bg" style="background-image: url('images/_JMP6225.jpg');"></div>
				<div class="cinematic-overlay" style="background: radial-gradient(circle at 75% 35%, rgba(12, 46, 96, 0.2) 0%, rgba(4, 14, 30, 0.65) 85%), linear-gradient(90deg, rgba(4, 14, 30, 0.78) 0%, rgba(4, 14, 30, 0.45) 55%, rgba(4, 14, 30, 0.15) 100%);"></div>
				<div class="container h-100" style="position: relative; z-index: 4;">
					<div class="row no-gutters slider-text js-fullheight align-items-center">
						<div class="col-lg-10 col-xl-9 ftco-animate">
							<div class="mt-5">
								<div class="mb-3 d-flex align-items-center flex-wrap gap-2">
									<div class="badge-prestige mr-2">
										<span class="badge-prestige-dot"></span>
										<span>Direction Générale & Ingénierie BTP • Yaoundé</span>
									</div>
									<div class="hero-video-badge-live">
										<span class="live-pulse-indicator"></span>
										<span>Immersion Chantier 4K & 3D</span>
									</div>
								</div>
								<h1 class="mb-4 text-white font-weight-bold" style="font-size: 3.3rem; line-height: 1.15; text-shadow: 0 4px 20px rgba(0,0,0,0.6);">
									L'Art de Bâtir des Ouvrages <br>
									<span class="text-gradient-gold">Durables, Puissants & Modernes</span>
								</h1>
								<p class="mb-5 text-white-50 lead" style="max-width: 780px; font-size: 1.15rem; line-height: 1.7;">
									Depuis 2017, <strong><?= htmlspecialchars($companyName) ?></strong> combine rigueur d'ingénierie, approvisionnement direct avec SIMS Quincaillerie et finitions de prestige avec SIMS Décor pour concrétiser vos plus grands projets clés en main.
								</p>
								<div class="d-flex flex-wrap gap-3">
									<a href="contact.php?type=devis" class="btn btn-gold-glow mr-3 mb-2">
										<i class="fa fa-calculator mr-2"></i> Étude de Prix Express
									</a>
									<a href="project.php" class="btn btn-glass-outline mb-2">
										<i class="fa fa-eye mr-2"></i> Explorer Nos Chantiers BTP
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>

		<!-- SLIDE 2 : Chantier Réel RMC BTP (Cadence & Gros Œuvre) -->
		<div class="carousel-item">
			<section class="hero-wrap js-fullheight kenburns-slide">
				<div class="kenburns-bg" style="background-image: url('images/IMG-20261029-WA0099.jpg'); background-position: center;"></div>
				<div class="cinematic-overlay"></div>
				<div class="container h-100">
					<div class="row no-gutters slider-text js-fullheight align-items-center">
						<div class="col-lg-10 col-xl-9 ftco-animate">
							<div class="mt-5">
								<div class="mb-3">
									<div class="badge-prestige">
										<span class="badge-prestige-dot"></span>
										<span>Cadence Terrain & Sécurité HSE Zéro Incident</span>
									</div>
								</div>
								<h1 class="mb-4 text-white font-weight-bold" style="font-size: 3.2rem; line-height: 1.15;">
									Maîtrise Opérationnelle <br>
									<span class="text-gradient-gold">& Cadence de Chantier Garantie</span>
								</h1>
								<p class="mb-5 text-white-50 lead" style="max-width: 780px; font-size: 1.15rem; line-height: 1.7;">
									Sur le terrain, notre direction technique supervisée par l'Ingénieur Toukam Patrick veille au respect strict des rétro-plannings, à la qualité irréprochable des bétons et à la sécurité de vos équipes.
								</p>
								<div class="d-flex flex-wrap gap-3">
									<a href="project.php" class="btn btn-gold-glow mr-3 mb-2">
										<i class="fa fa-images mr-2"></i> Nos Réalisations en Images
									</a>
									<a href="about.php" class="btn btn-glass-outline mb-2">
										<i class="fa fa-users mr-2"></i> Découvrir l'Équipe
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>

		<!-- SLIDE 3 : Finitions & Clés en main RMC BTP (SIMS Décor & Quincaillerie) -->
		<div class="carousel-item">
			<section class="hero-wrap js-fullheight kenburns-slide">
				<div class="kenburns-bg" style="background-image: url('images/IMG-20261029-WA0098.jpg'); background-position: center;"></div>
				<div class="cinematic-overlay"></div>
				<div class="container h-100">
					<div class="row no-gutters slider-text js-fullheight align-items-center">
						<div class="col-lg-10 col-xl-9 ftco-animate">
							<div class="mt-5">
								<div class="mb-3">
									<div class="badge-prestige">
										<span class="badge-prestige-dot"></span>
										<span>Métrés Rigoureux & Maîtrise des Budgets</span>
									</div>
								</div>
								<h1 class="mb-4 text-white font-weight-bold" style="font-size: 3.2rem; line-height: 1.15;">
									Transparence Budgétaire <br>
									<span class="text-gradient-gold">& Respect Scrupuleux des Coûts</span>
								</h1>
								<p class="mb-5 text-white-50 lead" style="max-width: 780px; font-size: 1.15rem; line-height: 1.7;">
									De l'analyse d'avant-projet jusqu'au décompte général définitif, nos cofondateurs et nos équipes vous garantissent une gestion claire, sans dépassement ni frais imprévus.
								</p>
								<div class="d-flex flex-wrap gap-3">
									<a href="services.php" class="btn btn-gold-glow mr-3 mb-2">
										<i class="fa fa-th-list mr-2"></i> Nos Domaines d'Expertise
									</a>
									<a href="contact.php?type=devis" class="btn btn-glass-outline mb-2">
										<i class="fa fa-paper-plane mr-2"></i> Demander une Cotation
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>

	<!-- Flèches de navigation Carousel -->
	<a class="carousel-control-prev" href="#heroHomeCarousel" role="button" data-slide="prev" style="width: 70px; z-index: 10;">
		<span class="fa fa-chevron-left text-white p-3 rounded-circle shadow" style="background: rgba(4, 14, 30, 0.7); border: 1px solid rgba(255,153,0,0.5); font-size: 18px;" aria-hidden="true"></span>
		<span class="sr-only">Précédent</span>
	</a>
	<a class="carousel-control-next" href="#heroHomeCarousel" role="button" data-slide="next" style="width: 70px; z-index: 10;">
		<span class="fa fa-chevron-right text-white p-3 rounded-circle shadow" style="background: rgba(4, 14, 30, 0.7); border: 1px solid rgba(255,153,0,0.5); font-size: 18px;" aria-hidden="true"></span>
		<span class="sr-only">Suivant</span>
	</a>
</div>

<!-- ========================================================================
     2. BANDEAU DE CERTIFICATIONS & GARANTIES INSTITUTIONNELLES BTP
     ======================================================================== -->
<div class="fee-reassurance-ribbon py-3" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-6 col-lg-3 py-2 px-3">
				<div class="fee-reassurance-item">
					<div class="fee-reassurance-icon" style="background: rgba(12, 46, 96, 0.1); color: #0c2e60;">
						<i class="fa fa-shield"></i>
					</div>
					<div>
						<div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.25;">Agrément BTP & Normes</div>
						<small class="text-muted" style="font-size: 11px;">Calculs Eurocodes & BAEL 91</small>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 py-2 px-3">
				<div class="fee-reassurance-item">
					<div class="fee-reassurance-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;">
						<i class="fa fa-tools"></i>
					</div>
					<div>
						<div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.25;">SIMS Quincaillerie Direct</div>
						<small class="text-muted" style="font-size: 11px;">Fers certifiés & ciments CPJ 42.5</small>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 py-2 px-3">
				<div class="fee-reassurance-item">
					<div class="fee-reassurance-icon" style="background: rgba(15, 23, 42, 0.1); color: #0f172a;">
						<i class="fa fa-paint-roller"></i>
					</div>
					<div>
						<div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.25;">SIMS Décor Prestige</div>
						<small class="text-muted" style="font-size: 11px;">Staff moderne & marbrerie de luxe</small>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3 py-2 px-3">
				<div class="fee-reassurance-item">
					<div class="fee-reassurance-icon" style="background: rgba(5, 150, 105, 0.1); color: #059669;">
						<i class="fa fa-handshake-o"></i>
					</div>
					<div>
						<div class="font-weight-bold text-dark" style="font-size: 13.5px; line-height: 1.25;">Partenaire Diaspora Garanti</div>
						<small class="text-muted" style="font-size: 11px;">Reporting régulier & transparence</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ========================================================================
     2.1 STUDIO 3D BIM & MAQUETTE DIGITALE TEMPS RÉEL (THREE.JS NEXT-GEN)
     ======================================================================== -->
<section class="fee-bim-section" id="bim-3d-experience">
	<div class="container">
		<div class="row justify-content-center text-center mb-5">
			<div class="col-lg-10 ftco-animate">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(217, 119, 6, 0.15); border: 1.5px solid rgba(217, 119, 6, 0.4); color: #fbbf24; font-size: 11.5px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa fa-cube mr-1"></i> INNOVATION TECHNOLOGIQUE & INGÉNIERIE NUMÉRIQUE
				</div>
				<h2 class="font-weight-bold mb-3 text-white" style="font-size: 2.3rem; letter-spacing: -0.02em;">
					Studio 3D BIM : Explorez l'Ouvrage de l'Intérieur
				</h2>
				<p class="lead mx-auto" style="max-width: 820px; color: #94a3b8; font-size: 1.1rem; line-height: 1.7;">
					Faites pivoter la maquette à la souris ou au doigt, décomposez les niveaux avec la <strong class="text-white">vue écorchée par étage</strong> et inspectez les fondations et structures en béton armé calculées aux normes d'ingénierie de RMC BTP.
				</p>
			</div>
		</div>

		<div class="fee-bim-container">
			<div class="fee-bim-canvas-wrap" id="feeBimCanvasWrap">
				<div class="fee-bim-grid-overlay"></div>
				<canvas id="feeBim3DCanvas"></canvas>

				<!-- HUD flottant d'ingénierie -->
				<div class="fee-bim-hud d-none d-md-block">
					<div class="fee-bim-hud-title">
						<span class="fee-bim-hud-badge"></span>
						<span>Maquette BIM R+2 Structurale</span>
					</div>
					<div class="fee-bim-hud-specs">
						&bull; Calcul : <span>Eurocodes 2 & BAEL</span><br>
						&bull; Béton armé : <span>Dosage 350 kg/m³</span><br>
						&bull; Armatures : <span>Acier FeE500 HA</span><br>
						&bull; Baies & Portes : <span id="feeBimDoorLabel">Fermées</span>
					</div>
				</div>

				<!-- Barre d'outils de contrôle 3D -->
				<div class="fee-bim-toolbar">
					<button type="button" class="fee-bim-btn active" data-bim-mode="prestige" title="Rendu architectural fini">
						<i class="fa fa-eye"></i> <span class="d-none d-sm-inline">Rendu Réaliste</span>
					</button>
					<button type="button" class="fee-bim-btn" data-bim-mode="blueprint" title="Structure filaire d'ingénierie">
						<i class="fa fa-drafting-compass"></i> <span class="d-none d-sm-inline">Filaire Blueprint</span>
					</button>
					<button type="button" class="fee-bim-btn" data-bim-mode="concrete" title="Béton brut et armatures">
						<i class="fa fa-cubes"></i> <span class="d-none d-sm-inline">Structure Béton</span>
					</button>
					<button type="button" class="fee-bim-btn" id="feeBtnBimOpenDoors" title="Ouvrir / Fermer les baies vitrées et portes">
						<i class="fa fa-door-open"></i> <span>Ouvrir les Baies</span>
					</button>
					<button type="button" class="fee-bim-btn" id="feeBtnBimExplode" title="Décomposer les étages">
						<i class="fa fa-layer-group"></i> <span>Vue Écorchée</span>
					</button>
					<button type="button" class="fee-bim-btn active" id="feeBtnBimRotate" title="Activer/Pause rotation">
						<i class="fa fa-repeat"></i>
					</button>
					<button type="button" class="fee-bim-btn" id="feeBtnBimReset" title="Recentrer la caméra">
						<i class="fa fa-crosshairs"></i>
					</button>
				</div>
			</div>
		</div>

		<div class="text-center mt-4">
			<span class="small" style="color: #64748b;">
				<i class="fa fa-mouse-pointer mr-1 text-warning"></i> Cliquez et glissez pour tourner à 360° &bull; Molette pour zoomer &bull; Cliquez sur <strong>« Vue Écorchée »</strong> pour séparer les dalles
			</span>
		</div>
	</div>
</section>

<!-- ========================================================================
     3. SYNERGIE & ÉCOSYSTÈME 360° RMC BTP (BTP + QUINCAILLERIE + DÉCORATION)
     ======================================================================== -->
<section class="rmc-ecosystem-section">
	<div class="container">
		<!-- En-tête Prestigieux RMC BTP -->
		<div class="row justify-content-center text-center mb-5">
			<div class="col-lg-10 ftco-animate">
				<span class="rmc-ecosystem-badge">
					<i class="fa fa-gem mr-1 text-warning"></i> SYNERGIE INTÉGRÉE CLÉS EN MAIN
				</span>
				<h2 class="rmc-ecosystem-title">
					Un Écosystème Global pour Bâtir de A à Z sans Intermédiaire
				</h2>
				<p class="rmc-ecosystem-subtitle">
					Sous la direction de <strong>M. Rodrigue DJOMO</strong>, <strong>RMC BTP SARL</strong> réunit l'ingénierie de construction, l'approvisionnement direct en matériaux certifiés et l'aménagement intérieur haute couture à Yaoundé et sur tout le Cameroun.
				</p>
			</div>
		</div>

		<div class="row align-items-stretch">
			<!-- Pôle 1 : RMC BTP (Gros Œuvre & Génie Civil) -->
			<div class="col-lg-4 col-md-6 d-flex ftco-animate mb-4 mb-lg-0">
				<div class="rmc-hub-card w-100">
					<div>
						<div class="rmc-hub-top">
							<div class="rmc-hub-icon">
								<i class="fa fa-building"></i>
							</div>
							<span class="rmc-hub-badge">PÔLE 01 • CONSTRUCTION</span>
						</div>

						<h3 class="rmc-hub-title">RMC BTP Génie Civil</h3>
						<p class="rmc-hub-desc">
							Études techniques, gros œuvre, dalles, infrastructures et constructions clés en main avec une rigueur d'ingénierie absolue et des calculs aux eurocodes.
						</p>

						<ul class="rmc-hub-list">
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Villas de standing, immeubles R+ et résidences privées</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Béton armé vibré dosé à 350 kg/m³ & ferraillage certifié</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Suivi de chantier rigoureux avec compte rendu hebdomadaire</span>
							</li>
						</ul>
					</div>

					<div class="rmc-hub-footer">
						<span><i class="fa fa-shield text-primary mr-1"></i> Garantie Décennale & Bureau d'Études</span>
						<i class="fa fa-arrow-right text-muted"></i>
					</div>
				</div>
			</div>

			<!-- Pôle 2 : SIMS QUINCAILLERIE (Mis en Avant) -->
			<div class="col-lg-4 col-md-6 d-flex ftco-animate mb-4 mb-lg-0">
				<div class="rmc-hub-card hub-featured w-100">
					<div>
						<div class="rmc-hub-top">
							<div class="rmc-hub-icon" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #fff;">
								<i class="fa fa-tools"></i>
							</div>
							<span class="rmc-hub-badge">PÔLE 02 • MATÉRIAUX DIRECTS</span>
						</div>

						<h3 class="rmc-hub-title">SIMS Quincaillerie</h3>
						<p class="rmc-hub-desc">
							Notre centrale d'approvisionnement intégrée garantit des matériaux de premier choix au meilleur prix d'usine, éliminant tout intermédiaire et retard d'approvisionnement.
						</p>

						<ul class="rmc-hub-list">
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle" style="color: #d97706;"></i>
								<span>Ciments haute résistance, fers à béton certifiés & agrégats lavés</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle" style="color: #d97706;"></i>
								<span>Outillage professionnel, plomberie sanitaire & électricité aux normes</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle" style="color: #d97706;"></i>
								<span>Zéro rupture de stock sur votre chantier : économies directes de 15 à 20%</span>
							</li>
						</ul>
					</div>

					<div class="rmc-hub-footer" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">
						<span><i class="fa fa-tags mr-1"></i> Prix Direct Usine & Stock Sécurisé</span>
						<i class="fa fa-star text-warning"></i>
					</div>
				</div>
			</div>

			<!-- Pôle 3 : SIMS DÉCOR (Finitions Haut Standing) -->
			<div class="col-lg-4 col-md-6 d-flex ftco-animate">
				<div class="rmc-hub-card w-100">
					<div>
						<div class="rmc-hub-top">
							<div class="rmc-hub-icon">
								<i class="fa fa-palette"></i>
							</div>
							<span class="rmc-hub-badge">PÔLE 03 • FINITIONS PRESTIGE</span>
						</div>

						<h3 class="rmc-hub-title">SIMS Décor</h3>
						<p class="rmc-hub-desc">
							L'art de sublimer votre espace : carrelages grands formats, staff moderne avec éclairage LED indirect, peintures décoratives et menuiseries sur-mesure.
						</p>

						<ul class="rmc-hub-list">
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Carrelages et marbres d'importation espagnole & italienne</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Faux-plafonds en staff suspendu, moulures & gorges lumineuses</span>
							</li>
							<li class="rmc-hub-item">
								<i class="fa fa-check-circle"></i>
								<span>Peintures haut de gamme satinées, laquées et effets texturés</span>
							</li>
						</ul>
					</div>

					<div class="rmc-hub-footer">
						<span><i class="fa fa-magic text-primary mr-1"></i> Clés en Main & Design d'Intérieur</span>
						<i class="fa fa-arrow-right text-muted"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     4. PRÉSENTATION, HISTOIRE & MOT DU DIRECTEUR GÉNÉRAL (M. Rodrigue DJOMO)
     ======================================================================== -->
<section class="ftco-section" id="about-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-10 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">NOTRE HISTOIRE & NOTRE AMBITION</span>
				<h2 class="mb-4 font-weight-bold" style="font-size: 2.3rem;">Construire Aujourd'hui les Solutions de Demain</h2>
				<p class="text-muted lead">
					Créée en <strong>2017</strong> par trois cofondateurs — <strong>Chirac, Marcair et Rodrigue</strong> —, <strong>RMC BTP SARL</strong> est née d'une ambition commune : concevoir des ouvrages pérennes au Cameroun et à l'international avec rigueur et innovation.
				</p>
			</div>
		</div>
		<div class="row align-items-center">
			<div class="col-md-6 ftco-animate">
				<div class="dg-3d-card-wrapper position-relative" data-3d-tilt style="perspective: 1200px;">
					<!-- Carte PDG avec Effet de Profondeur 3D et Cadre Architecte -->
					<div class="img img-video d-flex align-items-end justify-content-center position-relative rounded shadow-lg overflow-hidden dg-3d-card" style="background-image: url('images/fondateurs/rodrigue-djomo.jpg'); min-height: 520px; background-size: cover; background-position: center top; border: 4px solid #fff; border-radius: 18px !important; box-shadow: 0 30px 60px rgba(12, 46, 96, 0.28) !important;">
						<div class="overlay" style="position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(4,14,30,0.95) 0%, rgba(4,14,30,0.3) 50%, rgba(0,0,0,0) 80%);"></div>
						
						<!-- Badge Flottant en relief 3D (Z-Index surélevé) -->
						<div class="dg-floating-badge" style="position: absolute; top: 22px; right: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: 1px solid rgba(249, 115, 22, 0.4); transform: translateZ(40px); z-index: 10;">
							<div class="d-flex align-items-center">
								<span class="live-dot-green mr-2"></span>
								<small class="font-weight-bold text-dark" style="letter-spacing: 0.5px;">Direction Générale Sur Site</small>
							</div>
						</div>

						<div class="p-4 text-center text-white position-relative w-100" style="z-index: 2; transform: translateZ(35px);">
							<h4 class="text-white mb-1 font-weight-bold" style="font-size: 1.5rem; letter-spacing: -0.3px;">M. Rodrigue DJOMO</h4>
							<p class="badge badge-warning text-dark px-3 py-1 font-weight-bold mb-1" style="font-size: 13px; letter-spacing: 0.5px;">Fondateur & Directeur Général</p>
							<p class="small text-white-50 mb-0"><?= htmlspecialchars($companyName) ?> • Yaoundé, Cameroun</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 pl-md-5 ftco-animate mt-4 mt-md-0">
				<span class="badge badge-primary px-3 py-1 mb-2 font-weight-bold">Leadership & Écosystème Intégré</span>
				<h3 class="font-weight-bold mb-3" style="font-size: 1.8rem;">Plus de 700 Réalisations & Une Présence dans 64 Pays</h3>
				<p class="text-muted" style="line-height: 1.8;">
					Sous la direction de <strong>M. Rodrigue DJOMO</strong>, notre groupe articule l'ingénierie de construction, l'approvisionnement direct en matériaux certifiés via <strong>SIMS Quincaillerie</strong> et les finitions d'intérieur de prestige avec <strong>SIMS Décor</strong>.
				</p>
				<p class="text-muted" style="line-height: 1.8;">
					Fort de <strong>52 chantiers à l'international</strong> et d'un réseau actif dans plus de <strong>64 pays partenaires</strong>, nous offrons un accompagnement clés en main sécurisé aux particuliers, promoteurs et membres de la diaspora camerounaise.
				</p>
				<ul class="list-unstyled mb-4">
					<li class="mb-3 d-flex align-items-start">
						<i class="fa fa-check-circle text-primary mt-1 mr-3" style="font-size: 18px;"></i>
						<span><strong>Initiative Fondatrice 2017 :</strong> Chirac, Marcair et Rodrigue unis pour l'excellence BTP.</span>
					</li>
					<li class="mb-3 d-flex align-items-start">
						<i class="fa fa-check-circle text-primary mt-1 mr-3" style="font-size: 18px;"></i>
						<span><strong>Solutions Clés en Main :</strong> Gros œuvre, second œuvre, staff, marbrerie et VRD.</span>
					</li>
					<li class="mb-3 d-flex align-items-start">
						<i class="fa fa-check-circle text-primary mt-1 mr-3" style="font-size: 18px;"></i>
						<span><strong>Transparence & Suivi Diaspora :</strong> Rapports d'avancement et rétroplannings respectés.</span>
					</li>
				</ul>
				<div class="d-flex flex-wrap gap-2">
					<a href="about.php#fondateurs-section" class="btn btn-primary py-3 px-4 font-weight-bold mr-2 mb-2">
						Découvrir Nos Cofondateurs <i class="fa fa-arrow-right ml-1"></i>
					</a>
					<a href="contact.php" class="btn btn-outline-dark py-3 px-4 font-weight-bold mb-2">
						Nous Rencontrer à Yaoundé
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     4.1 DÉMONSTRATEUR LIVE CHANTIER TRACKER (ESPACE DIASPORA NEXT-GEN)
     ======================================================================== -->
<section class="fee-tracker-section" id="diaspora-tracker">
	<div class="container">
		<div class="row justify-content-center text-center mb-5">
			<div class="col-lg-10 ftco-animate">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(12, 46, 96, 0.08); border: 1.5px solid rgba(12, 46, 96, 0.25); color: #0c2e60; font-size: 11.5px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa fa-globe mr-1 text-primary"></i> TRANSPARENCE TOTALE & SUIVI DIASPORA
				</div>
				<h2 class="font-weight-bold mb-3" style="font-size: 2.3rem; color: #0c2e60; letter-spacing: -0.02em;">
					Suivez Votre Chantier au Pays Comme si Vous Y Étiez
				</h2>
				<p class="text-muted lead mx-auto" style="max-width: 820px; font-size: 1.1rem; line-height: 1.7;">
					Construire au Cameroun depuis l'étranger n'est plus une source d'inquiétude. Découvrez notre système de <strong>reporting hebdomadaire certifié</strong> avec jalons contractuels, éprouvettes béton et photos horodatées.
				</p>
				<div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
					<button type="button" class="btn btn-primary btn-sm px-3 py-2 font-weight-bold" data-track-chantier="1">
						<i class="fa fa-folder-open mr-1"></i> Dossier #1 : Villa Duplex Bastos / Odza (En cours)
					</button>
					<button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 font-weight-bold ml-2" data-track-chantier="2">
						<i class="fa fa-check-circle mr-1"></i> Dossier #2 : Immeuble R+4 Douala (Livré 100%)
					</button>
				</div>
			</div>
		</div>

		<div class="fee-tracker-card ftco-animate">
			<!-- Header Dossier -->
			<div class="fee-tracker-header" style="background: linear-gradient(135deg, #0c2e60 0%, #071933 100%);">
				<div>
					<div class="d-flex align-items-center flex-wrap gap-3 mb-2">
						<span class="fee-tracker-live-pill">
							<span class="fee-header-live-dot mr-1"></span> Suivi Chantier en Direct
						</span>
						<span class="badge badge-warning text-dark font-weight-bold px-3 py-1" id="feeTrackRef">
							Dossier : RMC-YDE-2026-08
						</span>
					</div>
					<h3 class="text-white font-weight-bold mb-1" id="feeTrackTitle">Villa Duplex Contemporaine R+1</h3>
					<small class="text-white-50">
						<i class="fa fa-map-marker text-warning mr-1"></i> <span id="feeTrackLocation">Yaoundé, Quartier Bastos</span>
						&bull; Maître d'Ouvrage : <strong class="text-white" id="feeTrackClient">M. BASSOCK Jean-Paul (Bruxelles, Belgique)</strong>
					</small>
				</div>
				<div class="d-flex flex-wrap gap-2">
					<button type="button" class="btn btn-warning text-dark font-weight-bold px-3 py-2" id="feeBtnDownloadTrackPdf">
						<i class="fa fa-file-pdf mr-1"></i> Télécharger le Rapport Officiel (PDF)
					</button>
					<a href="https://wa.me/237697398455?text=Bonjour%20RMC%20BTP,%20je%20souhaite%20des%20renseignements%20sur%20le%20suivi%20de%20chantier%20pour%20la%20diaspora." class="btn btn-outline-light font-weight-bold px-3 py-2" target="_blank" rel="noopener">
						<i class="fab fa-whatsapp text-success mr-1"></i> Contacter la Direction Technique
					</a>
				</div>
			</div>

			<!-- Jauge de Progression Générale -->
			<div class="fee-tracker-progress-wrap">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="font-weight-bold text-dark" style="font-size: 15px;">
						Avancement Global des Travaux :
					</span>
					<span class="font-weight-bold text-primary" style="font-size: 20px;" id="feeTrackPercent">78%</span>
				</div>
				<div class="fee-tracker-meter">
					<div class="fee-tracker-meter-fill" id="feeTrackProgressBar" style="width: 78%; background: linear-gradient(90deg, #0c2e60, #d97706);"></div>
				</div>
				<div class="d-flex justify-content-between text-muted small mt-2">
					<span>Démarrage : 10 Mai 2026</span>
					<span>Direction Technique : <strong class="text-dark" id="feeTrackConducteur">Ing. Patrick TOUKAM</strong></span>
					<span>Livraison contractuelle : <strong class="text-dark" id="feeTrackDelivery">20 Décembre 2026</strong></span>
				</div>

				<!-- Timeline des 5 Jalons -->
				<div class="fee-tracker-timeline" id="feeTrackStepsWrap">
					<!-- Rempli dynamiquement par JS -->
				</div>
			</div>

			<!-- Métriques Matériaux & Photos Terrain Horodatées -->
			<div class="fee-tracker-body">
				<div class="row mb-4">
					<div class="col-md-6 mb-3 mb-md-0">
						<div class="fee-tracker-metric-box h-100 text-left p-3">
							<div class="d-flex align-items-center mb-2">
								<i class="fa fa-flask text-warning mr-2" style="font-size: 20px;"></i>
								<h6 class="font-weight-bold mb-0 text-dark">Résistance & Éprouvettes Béton</h6>
							</div>
							<p class="small text-muted mb-0" id="feeTrackConcrete">
								Dosage 350 kg/m³ vibré &bull; Ciment CPJ 42.5 &bull; Éprouvettes validées à 28j (29.2 MPa)
							</p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="fee-tracker-metric-box h-100 text-left p-3">
							<div class="d-flex align-items-center mb-2">
								<i class="fa fa-shield text-danger mr-2" style="font-size: 20px;"></i>
								<h6 class="font-weight-bold mb-0 text-dark">Contrôle Armatures & Sécurité HSE</h6>
							</div>
							<p class="small text-muted mb-0" id="feeTrackSteel">
								Acier Haute Adhérence FeE500 (SIMS Quincaillerie) &bull; Zéro incident de chantier
							</p>
						</div>
					</div>
				</div>

				<h5 class="font-weight-bold text-dark mb-3">
					<i class="fa fa-camera mr-2" style="color: #0c2e60;"></i> Dernières Inspections Photographiques de la Direction Technique
				</h5>
				<div class="row" id="feeTrackPhotosWrap">
					<!-- Photos chargées dynamiquement par JS -->
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     5. LE MODULE INTERACTIF "AVANT / APRÈS" EN DIRECT (EFFET WAOUH EN GRANDE LARGEUR 90%+)
     ======================================================================== -->
<section class="ftco-section bg-dark text-white py-5" style="background: #040e1e !important;">
	<div class="container-fluid px-2 px-md-4" style="width: 92%; max-width: 1750px;">
		<div class="row justify-content-center mb-5 text-center">
			<div class="col-lg-10 ftco-animate">
				<span class="subheading font-weight-bold" style="letter-spacing: 2px; color: #00d2ff;">DÉMONSTRATION INTERACTIVE</span>
				<h2 class="mb-3 text-white font-weight-bold" style="font-size: 2.2rem;">Faites Glisser : Du Terrain Brut à l'Ouvrage d'Exception</h2>
				<p class="text-white-50 lead">Déplacez le curseur central avec votre souris ou votre doigt pour comparer l'état initial du chantier avec l'ouvrage livré par nos équipes.</p>
			</div>
		</div>

		<div class="row justify-content-center">
			<div class="col-12 ftco-animate">
				<!-- Widget Comparatif Avant/Après Interactif avec Effet 3D Profondeur (Envergure 90%+) -->
				<div class="interactive-ba-3d-frame p-2 p-md-3 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(0, 180, 255, 0.35); box-shadow: 0 25px 60px rgba(0,0,0,0.5), inset 0 0 20px rgba(0, 180, 255, 0.08); border-radius: 20px; width: 100%;">
					<div class="interactive-ba-wrapper" id="homeInteractiveBA" style="border-radius: 14px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.4); width: 100%; max-width: 100%;">
						<!-- Image Après (Fond : Bâtiment RMC BTP Achevé) -->
						<div class="ba-image-layer ba-after-layer" style="background-image: url('images/_JMP6225.jpg'); background-size: cover; background-position: center;"></div>
						<span class="ba-tag ba-tag-after"><i class="fa fa-check-circle mr-1"></i> Ouvrage Livré Clé en Main</span>

						<!-- Image Avant (Recouvrement : Chantier / Gros Œuvre) -->
						<div class="ba-image-layer ba-before-layer" id="baBeforeLayer" style="background-image: url('images/_JMP6244.jpg'); background-size: cover; background-position: center;"></div>
						<span class="ba-tag ba-tag-before"><i class="fa fa-history mr-1"></i> Chantier en Cours / Gros Œuvre</span>

						<!-- Poignée de Glissement -->
						<div class="ba-handle" id="baHandle">
							<i class="fa fa-arrows-h"></i>
						</div>

						<!-- Range Slider Invisible mais Contrôlable -->
						<input type="range" min="0" max="100" value="50" class="ba-range-slider" id="baRangeSlider" aria-label="Comparer avant et après">
					</div>
				</div>

				<div class="text-center mt-3">
					<span class="text-muted small"><i class="fa fa-hand-pointer-o text-warning mr-1"></i> <em>Faites glisser horizontalement pour dévoiler la transformation</em></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     6. CHIFFRES CLÉS & ENVERGURE NATIONALE & INTERNATIONALE
     ======================================================================== -->
<section class="ftco-section ftco-counter img" id="section-counter" style="background-image: url('images/bg_2.jpg');" data-stellar-background-ratio="0.5">
	<div class="overlay" style="background: rgba(4, 14, 30, 0.88);"></div>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12">
				<div class="row">
					<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="<?= htmlspecialchars($totalProjects) ?>">0</strong>
								<span class="text-white font-weight-bold">Travaux Réalisés</span>
								<small class="text-white-50 d-block mt-1">Yaoundé, Cameroun & Diaspora</small>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="52">0</strong>
								<span class="text-white font-weight-bold">Travaux à l'International</span>
								<small class="text-white-50 d-block mt-1">Afrique & Partenariats Mondiaux</small>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="64">0</strong>
								<span class="text-white font-weight-bold">Pays Partenaires</span>
								<small class="text-white-50 d-block mt-1">Réseau d'approvisionnement</small>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
						<div class="block-18 text-center">
							<div class="text">
								<strong class="number" data-number="<?= htmlspecialchars($expYears) ?>">0</strong>
								<span class="text-white font-weight-bold">Années d'Excellence</span>
								<small class="text-white-50 d-block mt-1">Depuis 2017</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     7. LES 3 BRANCHES MAJEURES DU GROUPE RMC BTP (3D HOLOGRAPHIQUE)
     ======================================================================== -->
<section class="ftco-section pb-4" style="background: linear-gradient(180deg, #f8fafc 0%, #edf2f7 100%);">
	<div class="container">
		<div class="row justify-content-center mb-5 text-center">
			<div class="col-lg-9 ftco-animate">
				<span class="badge badge-warning text-dark px-3 py-1 font-weight-bold mb-2" style="letter-spacing: 2px;">L'ÉCOSYSTÈME RMC BTP</span>
				<h2 class="font-weight-bold" style="font-size: 2.3rem; color: #0c2e60;">Une Synergie Complète en 3 Pôles d'Excellence</h2>
				<p class="text-muted lead">
					De l'étude de structure à l'approvisionnement en matériaux certifiés jusqu'aux finitions d'intérieur les plus raffinées.
				</p>
			</div>
		</div>

		<div class="row">
			<!-- PÔLE 1 : RMC BTP -->
			<div class="col-lg-4 col-md-6 mb-4 ftco-animate">
				<div class="card h-100 border-0 shadow-lg position-relative overflow-hidden" data-3d-tilt style="border-radius: 18px; transition: transform 0.3s ease;">
					<div style="height: 220px; background-image: url('images/_JMP6225.jpg'); background-size: cover; background-position: center; position: relative;">
						<div class="overlay" style="position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(12,46,96,0.92) 0%, rgba(0,0,0,0.2) 100%);"></div>
						<span class="badge badge-primary position-absolute" style="top: 15px; left: 15px; font-size: 13px; padding: 6px 14px; border-radius: 30px;">
							<i class="fa fa-building mr-1"></i> Bâtiment & Travaux
						</span>
					</div>
					<div class="card-body p-4 d-flex flex-column justify-content-between">
						<div>
							<h3 class="font-weight-bold mb-2" style="color: #0c2e60; font-size: 1.5rem;">RMC BTP</h3>
							<p class="text-warning font-weight-bold small mb-3">Construction Clés en Main & Génie Civil</p>
							<p class="text-muted small">
								Conception architecturale, calculs de structures, gros œuvre, dalles, et gestion intégrale de chantiers résidentiels, commerciaux et industriels au Cameroun.
							</p>
						</div>
						<a href="services.php" class="btn btn-primary btn-block font-weight-bold py-2 mt-3" style="border-radius: 8px;">
							Découvrir les Travaux <i class="fa fa-arrow-right ml-1"></i>
						</a>
					</div>
				</div>
			</div>

			<!-- PÔLE 2 : SIMS QUINCAILLERIE -->
			<div class="col-lg-4 col-md-6 mb-4 ftco-animate">
				<div class="card h-100 border-0 shadow-lg position-relative overflow-hidden" data-3d-tilt style="border-radius: 18px; transition: transform 0.3s ease;">
					<div style="height: 220px; background-image: url('images/_JMP6308.jpg'); background-size: cover; background-position: center; position: relative;">
						<div class="overlay" style="position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(0,56,141,0.92) 0%, rgba(0,0,0,0.2) 100%);"></div>
						<span class="badge badge-info position-absolute" style="top: 15px; left: 15px; font-size: 13px; padding: 6px 14px; border-radius: 30px; background: #00d2ff; color: #041226;">
							<i class="fa fa-wrench mr-1"></i> Matériaux & Outillage
						</span>
					</div>
					<div class="card-body p-4 d-flex flex-column justify-content-between">
						<div>
							<h3 class="font-weight-bold mb-2" style="color: #00388d; font-size: 1.5rem;">SIMS QUINCAILLERIE</h3>
							<p class="text-primary font-weight-bold small mb-3">Matériaux Pro & Équipements BTP</p>
							<p class="text-muted small">
								Magasin d'équipement haut de gamme : ciments certifiés, fers à béton haute adhérence, outillages électroportatifs, plomberie et électricité de chantier aux meilleurs tarifs.
							</p>
						</div>
						<a href="contact.php?service=SIMS+Quincaillerie" class="btn btn-outline-primary btn-block font-weight-bold py-2 mt-3" style="border-radius: 8px; border-color: #00388d; color: #00388d;">
							Consulter le Catalogue <i class="fa fa-arrow-right ml-1"></i>
						</a>
					</div>
				</div>
			</div>

			<!-- PÔLE 3 : SIMS DECOR -->
			<div class="col-lg-4 col-md-6 mb-4 ftco-animate">
				<div class="card h-100 border-0 shadow-lg position-relative overflow-hidden" data-3d-tilt style="border-radius: 18px; transition: transform 0.3s ease;">
					<div style="height: 220px; background-image: url('images/_JMP6286.jpg'); background-size: cover; background-position: center; position: relative;">
						<div class="overlay" style="position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(15,23,42,0.92) 0%, rgba(0,0,0,0.2) 100%);"></div>
						<span class="badge badge-light position-absolute" style="top: 15px; left: 15px; font-size: 13px; padding: 6px 14px; border-radius: 30px; color: #0c2e60;">
							<i class="fa fa-paint-brush mr-1"></i> Finitions & Décoration
						</span>
					</div>
					<div class="card-body p-4 d-flex flex-column justify-content-between">
						<div>
							<h3 class="font-weight-bold mb-2" style="color: #0f172a; font-size: 1.5rem;">SIMS DECOR</h3>
							<p class="text-success font-weight-bold small mb-3">Architecture d'Intérieur & Finitions de Luxe</p>
							<p class="text-muted small">
								Sublimez votre villa ou duplex : plafonds décoratifs en staff avec gorges lumineuses LED, revêtements marbre et carrelage grand format, peinture haut de gamme et sanitaires design.
							</p>
						</div>
						<a href="contact.php?service=SIMS+Decor" class="btn btn-dark btn-block font-weight-bold py-2 mt-3" style="border-radius: 8px;">
							Découvrir les Finitions <i class="fa fa-arrow-right ml-1"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     8. NOS SERVICES DÉTAILLÉS (PAR CORPS D'ÉTAT)
     ======================================================================== -->
<section class="ftco-section bg-light" id="services-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">SPÉCIALISATIONS TECHNIQUES</span>
				<h2 class="mb-4 font-weight-bold">Tous Nos Corps d'État & Compétences</h2>
				<p class="text-muted">De la conception des plans jusqu'à la remise solennelle des clés, notre expertise couvre l'ensemble du cycle de construction.</p>
			</div>
		</div>
		<div class="row">
			<?php 
			$serviceIndex = 1;
			foreach ($services as $srv): 
				$srvImg = resolveImage($srv['image'] ?? '', 'images/services-' . $serviceIndex . '.jpg');
			?>
				<div class="col-md-4 d-flex ftco-animate mb-4">
					<div class="card-wow-service w-100 d-flex flex-column justify-content-between">
						<div>
							<div style="height: 210px; background-image: url('<?= htmlspecialchars($srvImg) ?>'); background-size: cover; background-position: center; position: relative;">
								<div class="service-icon-box position-absolute" style="top: 15px; left: 15px; width: 48px; height: 48px; border-radius: 50%; background: #0c2e60; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px;">
									<i class="fa <?= htmlspecialchars($srv['icon'] ?? 'fa-building') ?>"></i>
								</div>
							</div>
							<div class="p-4">
								<h5 class="font-weight-bold mb-3 text-dark"><?= htmlspecialchars($srv['title']) ?></h5>
								<p class="text-muted small mb-0">
									<?= htmlspecialchars($srv['short_desc'] ?? '') ?>
								</p>
							</div>
						</div>
						<div class="p-4 pt-0">
							<a href="contact.php?type=devis&service=<?= urlencode($srv['title']) ?>" class="btn btn-outline-primary btn-sm btn-block font-weight-bold py-2">
								<i class="fa fa-calculator mr-1"></i> Cotation pour ce corps d'état
							</a>
						</div>
					</div>
				</div>
			<?php 
				$serviceIndex++;
				if ($serviceIndex > 6) $serviceIndex = 1;
			endforeach; 
			?>
		</div>
		<div class="text-center mt-3">
			<a href="services.php" class="btn btn-primary py-3 px-5 font-weight-bold">
				Voir Tous Nos Domaines d'Intervention <i class="fa fa-arrow-right ml-1"></i>
			</a>
		</div>
	</div>
</section>

<!-- ========================================================================
     8. CONFIGURATEUR DE PROJET ÉTAPE PAR ÉTAPE (WIZARD ESTIMATIF & DEVIS PDF)
     ======================================================================== -->
<section class="fee-wizard-section py-5" id="simulateur-section" style="background: #ffffff; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
	<div class="container">
		<div class="row justify-content-center text-center mb-5">
			<div class="col-lg-10 ftco-animate">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(12, 46, 96, 0.08); border: 1.5px solid rgba(12, 46, 96, 0.25); color: #0c2e60; font-size: 11.5px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa fa-sliders mr-1 text-warning"></i> CONFIGURATEUR D'INGÉNIERIE & ÉTUDE DE PRIX EXPRESS
				</div>
				<h2 class="font-weight-bold mb-3" style="font-size: 2.3rem; color: #0c2e60; letter-spacing: -0.02em;">
					Configurez Votre Projet & Obtenez Votre Devis Immédiat
				</h2>
				<p class="text-muted lead mx-auto" style="max-width: 820px; font-size: 1.1rem; line-height: 1.7;">
					Un algorithme précis calibré sur les mercuriales officielles et les cours des matériaux au Cameroun. Téléchargez votre fiche estimative en PDF ou partagez-la directement sur WhatsApp avec nos métreurs RMC BTP.
				</p>
			</div>
		</div>

		<div class="fee-wizard-wrap ftco-animate">
			<!-- Onglets de navigation du Wizard -->
			<div class="fee-wizard-nav">
				<div class="fee-wizard-tab active" data-step-target="1">
					<span class="fee-wizard-tab-number">1</span>
					<span>1. Type d'Ouvrage</span>
				</div>
				<div class="fee-wizard-tab" data-step-target="2">
					<span class="fee-wizard-tab-number">2</span>
					<span>2. Standing & Finitions</span>
				</div>
				<div class="fee-wizard-tab" data-step-target="3">
					<span class="fee-wizard-tab-number">3</span>
					<span>3. Relief & Terrain</span>
				</div>
				<div class="fee-wizard-tab" data-step-target="4">
					<span class="fee-wizard-tab-number">4</span>
					<span>4. Surface & Options</span>
				</div>
			</div>

			<div class="fee-wizard-content">
				<!-- ÉTAPE 1 : TYPE D'OUVRAGE -->
				<div class="fee-wizard-step active" id="feewizStep1">
					<h4 class="font-weight-bold text-dark mb-2">Quel type d'ouvrage souhaitez-vous réaliser ?</h4>
					<p class="text-muted small mb-4">Sélectionnez la catégorie principale pour charger les ratios de ferraillage et de gros œuvre adaptés.</p>
					
					<div class="fee-wizard-grid">
						<div class="fee-wizard-card selected" data-wiz-type="villa">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-home"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Villa / Résidence</h6>
							<small class="text-muted">Plain-pied ou duplex contemporain avec baies vitrées</small>
						</div>
						<div class="fee-wizard-card" data-wiz-type="building">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-building"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Immeuble R+2 à R+4</h6>
							<small class="text-muted">Appartements locatifs, commerces et bureaux de standing</small>
						</div>
						<div class="fee-wizard-card" data-wiz-type="retaining">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-layer-group"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Soutènement & VRD</h6>
							<small class="text-muted">Murs banchés anti-glissement, voirie et pavage lourd</small>
						</div>
						<div class="fee-wizard-card" data-wiz-type="industrial">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-industry"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Hangar / Entrepôt</h6>
							<small class="text-muted">Charpente métallique, radier industriel et stockage</small>
						</div>
					</div>

					<div class="d-flex justify-content-end mt-4">
						<button type="button" class="btn btn-primary px-4 py-2 font-weight-bold" data-wiz-next>
							Continuer vers le Standing <i class="fa fa-arrow-right ml-2"></i>
						</button>
					</div>
				</div>

				<!-- ÉTAPE 2 : NIVEAU DE STANDING -->
				<div class="fee-wizard-step" id="feewizStep2">
					<h4 class="font-weight-bold text-dark mb-2">Choisissez votre niveau de finition</h4>
					<p class="text-muted small mb-4">Le niveau de standing détermine la provenance des équipements, sanitaires, carrelages et menuiseries (via SIMS Quincaillerie & SIMS Décor).</p>
					
					<div class="fee-wizard-grid">
						<div class="fee-wizard-card" data-wiz-standing="standard">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-check-circle"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Éco-Rigueur</h6>
							<small class="text-muted">Gros œuvre certifié 350kg/m³, carrelage soigné et menuiseries aluminium</small>
						</div>
						<div class="fee-wizard-card selected" data-wiz-standing="comfort">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-star"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Grand Standing</h6>
							<small class="text-muted">Grès cérame grand format, faux-plafonds staff SIMS Décor et spots LED</small>
						</div>
						<div class="fee-wizard-card" data-wiz-standing="prestige">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-crown"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Prestige Diaspora</h6>
							<small class="text-muted">Sanitaires italiens, marbrerie d'exception, domotique et baies vitrées panoramiques</small>
						</div>
					</div>

					<div class="d-flex justify-content-between mt-4">
						<button type="button" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" data-wiz-prev>
							<i class="fa fa-arrow-left mr-2"></i> Retour
						</button>
						<button type="button" class="btn btn-primary px-4 py-2 font-weight-bold" data-wiz-next>
							Continuer vers le Terrain <i class="fa fa-arrow-right ml-2"></i>
						</button>
					</div>
				</div>

				<!-- ÉTAPE 3 : RELIEF & TOPOGRAPHIE -->
				<div class="fee-wizard-step" id="feewizStep3">
					<h4 class="font-weight-bold text-dark mb-2">Quelle est la configuration de votre terrain ?</h4>
					<p class="text-muted small mb-4">La topographie du sol influence directement l'ampleur des terrassements et le type de fondations.</p>
					
					<div class="fee-wizard-grid">
						<div class="fee-wizard-card selected" data-wiz-terrain="flat">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-square"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Terrain Plat Normal</h6>
							<small class="text-muted">Fouilles courantes et semelles superficielles classiques</small>
						</div>
						<div class="fee-wizard-card" data-wiz-terrain="slope">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-mountain"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Terrain en Pente Forte</h6>
							<small class="text-muted">Terrassement en redans, murs de soutènement et drains obligatoires</small>
						</div>
						<div class="fee-wizard-card" data-wiz-terrain="wetland">
							<span class="fee-wizard-card-check"><i class="fa fa-check"></i></span>
							<div class="fee-wizard-card-icon"><i class="fa fa-tint"></i></div>
							<h6 class="font-weight-bold text-dark mb-1">Sol Meuble / Bas-Fond</h6>
							<small class="text-muted">Radier généralisé renforcé et cuvelage anti-remontées d'humidité</small>
						</div>
					</div>

					<div class="d-flex justify-content-between mt-4">
						<button type="button" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" data-wiz-prev>
							<i class="fa fa-arrow-left mr-2"></i> Retour
						</button>
						<button type="button" class="btn btn-primary px-4 py-2 font-weight-bold" data-wiz-next>
							Continuer vers la Surface <i class="fa fa-arrow-right ml-2"></i>
						</button>
					</div>
				</div>

				<!-- ÉTAPE 4 : SURFACE, OPTIONS & RÉSULTAT FINAL -->
				<div class="fee-wizard-step" id="feewizStep4">
					<div class="row align-items-center">
						<div class="col-lg-6 mb-4 mb-lg-0">
							<h4 class="font-weight-bold text-dark mb-3">Superficie & Packs Additionnels</h4>
							
							<div class="form-group mb-4">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<label class="font-weight-bold text-dark small mb-0">Superficie couverte estimée :</label>
									<span class="badge badge-warning text-dark font-weight-bold px-3 py-1" style="font-size: 15px;" id="feewizSurfaceVal">200 m²</span>
								</div>
								<input type="range" min="50" max="1200" step="10" value="200" class="form-control-range" id="feewizSurfaceRange">
								<div class="d-flex justify-content-between text-muted small mt-1">
									<span>50 m²</span>
									<span>600 m²</span>
									<span>1 200 m²</span>
								</div>
							</div>

							<label class="font-weight-bold text-dark small mb-2">Options Clés en Main Recommandées :</label>
							<div class="border rounded p-3 mb-2" style="background: #f8fafc;">
								<div class="custom-control custom-checkbox mb-2">
									<input type="checkbox" class="custom-control-input" id="feewizOpt_forage">
									<label class="custom-control-label font-weight-bold text-dark small" for="feewizOpt_forage">
										Forage d'eau potable autonome (+ château d'eau & pompe)
									</label>
								</div>
								<div class="custom-control custom-checkbox mb-2">
									<input type="checkbox" class="custom-control-input" id="feewizOpt_fence">
									<label class="custom-control-label font-weight-bold text-dark small" for="feewizOpt_fence">
										Clôture périphérique maçonnée avec portail motorisé
									</label>
								</div>
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="feewizOpt_solar">
									<label class="custom-control-label font-weight-bold text-dark small" for="feewizOpt_solar">
										Centrale Solaire hybride autonome (Batteries Lithium)
									</label>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<!-- Panneau Sommaire & Devis Immédiat -->
							<div class="fee-wizard-summary-box" style="background: linear-gradient(145deg, #0c2e60 0%, #071933 100%);">
								<small class="text-uppercase font-weight-bold text-warning" style="letter-spacing: 1px;">
									Fourchette Estimative Globale Clés en Main :
								</small>
								<div class="fee-wizard-price-big my-2" id="feewizPriceFcfa">
									35 000 000 - 45 000 000 FCFA
								</div>
								<div class="fee-wizard-price-eur mb-3" id="feewizPriceEur">
									(~ 53 000 € à 68 000 €)
								</div>

								<div class="border-top border-secondary pt-3 mb-3">
									<div class="row text-center text-white-50 small">
										<div class="col-3 border-right border-secondary">
											<strong class="d-block text-white" id="feewizDuration">5 Mois</strong>
											<span>Délai</span>
										</div>
										<div class="col-3 border-right border-secondary">
											<strong class="d-block text-white" id="feewizCement">~ 1300 Sacs</strong>
											<span>Ciment</span>
										</div>
										<div class="col-3 border-right border-secondary">
											<strong class="d-block text-white" id="feewizSteel">~ 9 T</strong>
											<span>Acier HA</span>
										</div>
										<div class="col-3">
											<strong class="d-block text-white" id="feewizConcrete">~ 130 m³</strong>
											<span>Béton B25</span>
										</div>
									</div>
								</div>

								<div class="d-flex flex-wrap gap-2">
									<button type="button" class="btn btn-warning text-dark font-weight-bold btn-block py-3 mb-2" id="feewizBtnPdf">
										<i class="fa fa-file-pdf mr-2"></i> Télécharger cette Fiche Estimative (PDF)
									</button>
									<a href="#" class="btn btn-success btn-block font-weight-bold py-3" id="feewizBtnWhatsapp" target="_blank" rel="noopener">
										<i class="fab fa-whatsapp mr-2 font-weight-bold" style="font-size: 18px;"></i> Transmettre au Bureau d'Études sur WhatsApp
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="d-flex justify-content-start mt-4">
						<button type="button" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" data-wiz-prev>
							<i class="fa fa-arrow-left mr-2"></i> Modifier les paramètres
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     9. CHANTIERS & RÉALISATIONS PHARES
     ======================================================================== -->
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">PORTFOLIO BTP</span>
				<h2 class="mb-4 font-weight-bold">Nos Dernières Réalisations de Chantiers</h2>
				<p class="text-muted">Découvrez un aperçu de nos projets majeurs menés avec succès au Cameroun et à l'international.</p>
			</div>
		</div>
		<div class="row">
			<?php 
			$projIdx = 1;
			foreach ($projects as $proj): 
				$projImg = resolveImage($proj['main_image'] ?? '', 'images/project-' . $projIdx . '.jpg');
			?>
				<div class="col-md-4 ftco-animate mb-4">
					<div class="project-entry project-card-3d rounded shadow-sm overflow-hidden" style="position: relative; background: #fff;">
						<div class="project-img position-relative" style="height: 275px; background-image: url('<?= htmlspecialchars($projImg) ?>'); background-size: cover; background-position: center top !important;">
							<span class="badge badge-primary position-absolute" style="top: 15px; left: 15px; padding: 6px 10px; font-size: 11px;">
								<?= htmlspecialchars($proj['category'] ?? 'BTP') ?>
							</span>
							<?php if (!empty($proj['video_url'])): ?>
								<span class="badge badge-danger position-absolute" style="top: 15px; right: 15px; padding: 6px 10px; font-size: 11px;">
									<i class="fa fa-video-camera mr-1"></i> Vidéo
								</span>
							<?php endif; ?>
							<a href="<?= htmlspecialchars($projImg) ?>" class="image-popup d-flex justify-content-center align-items-center" style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(12,46,96,0.5); opacity:0; transition: all 0.3s ease;">
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
								<i class="fa fa-map-marker text-primary mr-1"></i> <?= htmlspecialchars($proj['location'] ?? 'Cameroun') ?>
								• <span class="badge badge-success-btp"><?= htmlspecialchars($proj['status'] ?? 'Terminé') ?></span>
							</p>
							<a href="project-details.php?id=<?= $proj['id'] ?>" class="small font-weight-bold text-primary">
								Voir la fiche complète & photos Avant/Après <i class="fa fa-angle-right ml-1"></i>
							</a>
						</div>
					</div>
				</div>
			<?php 
				$projIdx++;
				if ($projIdx > 6) $projIdx = 1;
			endforeach; 
			?>
		</div>
		<div class="text-center mt-3">
			<a href="project.php" class="btn btn-outline-primary py-3 px-5 font-weight-bold">
				Consulter la Galerie Complète des Chantiers <i class="fa fa-images ml-1"></i>
			</a>
		</div>
	</div>
</section>

<!-- ========================================================================
     9.1 CARTE INTERACTIVE DES RÉALISATIONS AU CAMEROUN (LEAFLET)
     ======================================================================== -->
<section class="fee-map-section" id="cameroun-map-section">
	<div class="container">
		<div class="row justify-content-center text-center mb-5">
			<div class="col-lg-10 ftco-animate">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(12, 46, 96, 0.08); border: 1.5px solid rgba(12, 46, 96, 0.25); color: #0c2e60; font-size: 11.5px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa fa-map mr-1" style="color: #d97706;"></i> MAILLAGE GÉOGRAPHIQUE & RAYONNEMENT
				</div>
				<h2 class="font-weight-bold mb-3" style="font-size: 2.3rem; color: #0c2e60; letter-spacing: -0.02em;">
					Nos Ouvrages d'Ingénierie à Travers le Cameroun
				</h2>
				<p class="text-muted lead mx-auto" style="max-width: 820px; font-size: 1.1rem; line-height: 1.7;">
					Explorez la répartition géographique de nos chantiers de construction, immeubles d'envergure, complexes résidentiels et ouvrages clés en main. Cliquez sur les marqueurs pour consulter les fiches détaillées.
				</p>
			</div>
		</div>

		<div class="fee-map-wrapper ftco-animate">
			<div class="fee-map-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
				<div>
					<h5 class="font-weight-bold mb-0 text-dark">
						<i class="fa fa-crosshairs text-danger mr-2"></i> Géolocalisation des Chantiers RMC BTP
					</h5>
					<small class="text-muted">Filtrez par pôle urbain pour zoomer sur les réalisations</small>
				</div>
				<div class="fee-map-filters">
					<button type="button" class="fee-map-filter-btn active" data-city="all">Tous les Chantiers</button>
					<button type="button" class="fee-map-filter-btn" data-city="Yaoundé">Yaoundé (Siège)</button>
					<button type="button" class="fee-map-filter-btn" data-city="Douala">Douala (Littoral)</button>
					<button type="button" class="fee-map-filter-btn" data-city="national">Kribi & Régions</button>
				</div>
			</div>
			<div id="feeCamerounMap"></div>
		</div>
	</div>
</section>

<!-- ========================================================================
     10. ATTESTATIONS D'EXÉCUTION & CONFIANCE CLIENTS (ÉDITION PRESTIGE)
     ======================================================================== -->
<?php if (!empty($testimonials)): ?>
<section class="ftco-section fee-testimonials-section py-5" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
	<div class="container">
		<!-- En-tête Prestigieux & Confiance -->
		<div class="row justify-content-center mb-5 text-center">
			<div class="col-lg-9 ftco-animate">
				<div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(12, 46, 96, 0.08); border: 1.5px solid rgba(12, 46, 96, 0.25); color: #0c2e60; font-size: 11.5px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa fa-certificate mr-1 text-warning"></i> ATTESTATIONS D'EXÉCUTION & CONFIANCE BTP
				</div>
				<h2 class="font-weight-bold mb-3" style="font-size: 2.3rem; color: #0c2e60; letter-spacing: -0.02em;">
					La Confiance Vérifiée de Nos Maîtres d'Ouvrage
				</h2>
				<p class="text-muted lead mx-auto" style="max-width: 780px; font-size: 1.05rem; line-height: 1.7;">
					Promoteurs immobiliers, institutions et familles de la diaspora témoignent de notre rigueur d'ingénierie, du respect strict des calendriers et de la transparence de nos chiffrages.
				</p>
			</div>
		</div>

		<!-- Grille des Attestations (3 Blocs Exécutifs Haut de Gamme) -->
		<div class="row align-items-stretch">
			<?php 
			foreach ($testimonials as $test): 
				$nameParts = explode(' ', trim($test['client_name'] ?? 'Client'));
				$initials = '';
				foreach ($nameParts as $part) {
					$clean = strtolower(trim($part));
					if (!empty($part) && !in_array($clean, ['dr.', 'dr', 'mme', 'm.', 'ing.', 'ing', 'mr', 'mr.', 'pr', 'pr.'])) {
						$initials .= strtoupper(substr($part, 0, 1));
					}
				}
				$initials = substr($initials, 0, 2);
				if (empty($initials)) $initials = 'RC';
			?>
				<div class="col-lg-4 col-md-6 d-flex ftco-animate mb-4 mb-lg-0">
					<div class="fee-testimony-card w-100 d-flex flex-column justify-content-between p-4 rounded-xl" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 30px rgba(12, 46, 96, 0.05); transition: all 0.35s ease;">
						
						<div>
							<!-- Haut de Carte : Étoiles + Badge Chantier Réceptionné -->
							<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom" style="border-color: #f1f5f9 !important;">
								<div class="d-flex align-items-center" style="gap: 3px;">
									<?php for ($i = 0; $i < ($test['rating'] ?? 5); $i++): ?>
										<i class="fa fa-star" style="color: #f59e0b; font-size: 13.5px;"></i>
									<?php endfor; ?>
									<span class="ml-1 font-weight-bold text-dark small" style="font-size: 12.5px;">5.0</span>
								</div>
								<span class="badge px-2 py-1" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 700; border-radius: 50px;">
									<i class="fa fa-shield mr-1"></i> Chantier Livré
								</span>
							</div>

							<!-- Citation Témoignage -->
							<p class="fee-testimony-quote mb-4" style="color: #334155; font-size: 0.95rem; line-height: 1.7; font-weight: 450;">
								« <?= htmlspecialchars($test['comment']) ?> »
							</p>
						</div>

						<!-- Bas de Carte : Auteur, Rôle & Projet -->
						<div class="pt-3 border-top d-flex align-items-center" style="border-color: #f1f5f9 !important;">
							<div class="fee-testimony-avatar mr-3" style="background: linear-gradient(135deg, #0c2e60, #1e40af); color: #fff;">
								<?= htmlspecialchars($initials) ?>
							</div>
							<div class="overflow-hidden">
								<h6 class="font-weight-bold mb-0 text-dark" style="font-size: 14.5px;"><?= htmlspecialchars($test['client_name']) ?></h6>
								<small class="text-muted d-block" style="font-size: 12px;"><?= htmlspecialchars($test['client_title'] ?? 'Maître d\'Ouvrage') ?></small>
								<small class="d-block font-weight-bold mt-1 text-truncate" style="color: #d97706; font-size: 11.5px;">
									<i class="fa fa-map-marker mr-1"></i> <?= htmlspecialchars($test['company'] ?? 'Yaoundé / Diaspora') ?>
								</small>
							</div>
						</div>

					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Bandeau d'Engagement Qualité BTP (Reassurance Metrics Bar) -->
		<div class="row mt-5">
			<div class="col-12 ftco-animate">
				<div class="p-4 rounded-xl d-flex flex-wrap align-items-center justify-content-around text-center" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); gap: 15px;">
					<div class="px-3 py-1">
						<div class="font-weight-bold" style="font-size: 1.75rem; color: #0c2e60; line-height: 1.1;">100%</div>
						<small class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Délais Contractuels Tenus</small>
					</div>
					<div class="border-left d-none d-lg-block" style="height: 38px; border-color: #cbd5e1 !important;"></div>
					<div class="px-3 py-1">
						<div class="font-weight-bold" style="font-size: 1.75rem; color: #d97706; line-height: 1.1;">350 kg/m³</div>
						<small class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Dosage Béton Normé & Éprouvettes</small>
					</div>
					<div class="border-left d-none d-lg-block" style="height: 38px; border-color: #cbd5e1 !important;"></div>
					<div class="px-3 py-1">
						<div class="font-weight-bold" style="font-size: 1.75rem; color: #0c2e60; line-height: 1.1;">0 Avenant</div>
						<small class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Transparence Budgétaire Totale</small>
					</div>
					<div class="border-left d-none d-lg-block" style="height: 38px; border-color: #cbd5e1 !important;"></div>
					<div class="px-3 py-1">
						<div class="font-weight-bold" style="font-size: 1.75rem; color: #d97706; line-height: 1.1;">24/7</div>
						<small class="text-muted text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">Reporting Vidéo pour la Diaspora</small>
					</div>
				</div>
			</div>
		</div>

	</div>
</section>
<?php endif; ?>

<!-- ========================================================================
     11. DERNIÈRES ACTUALITÉS BTP
     ======================================================================== -->
<?php if (!empty($articles)): ?>
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">ACTUALITÉS & VEILLE TECHNIQUE</span>
				<h2 class="mb-4 font-weight-bold">Suivi des Travaux & Innovations BTP</h2>
			</div>
		</div>
		<div class="row">
			<?php 
			$artIdx = 1;
			foreach ($articles as $art): 
				$artImg = resolveImage($art['image'] ?? '', 'images/image_' . $artIdx . '.jpg');
			?>
				<div class="col-md-4 d-flex ftco-animate mb-4">
					<div class="blog-entry align-self-stretch shadow-sm rounded overflow-hidden w-100 bg-white">
						<a href="blog-single.php?id=<?= $art['id'] ?>" class="block-20" style="background-image: url('<?= htmlspecialchars($artImg) ?>'); height: 220px; display: block; background-size: cover; background-position: center;">
						</a>
						<div class="text p-4 d-block">
							<div class="meta mb-2">
								<div><span class="fa fa-calendar mr-1 text-primary"></span> <?= date('d M Y', strtotime($art['created_at'])) ?></div>
								<div><span class="fa fa-tag mr-1 text-primary"></span> <?= htmlspecialchars($art['category'] ?? 'Chantiers') ?></div>
							</div>
							<h4 class="heading font-weight-bold"><a href="blog-single.php?id=<?= $art['id'] ?>" class="text-dark"><?= htmlspecialchars($art['title']) ?></a></h4>
							<p class="text-muted small"><?= htmlspecialchars($art['summary'] ?? '') ?></p>
							<p><a href="blog-single.php?id=<?= $art['id'] ?>" class="btn btn-primary py-2 px-3 small font-weight-bold">Lire l'article <i class="fa fa-angle-right ml-1"></i></a></p>
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
<?php endif; ?>

<!-- ========================================================================
     12. CALL TO ACTION FINAL HAUTE VISIBILITÉ
     ======================================================================== -->
<section class="ftco-intro py-5" style="background: linear-gradient(135deg, #071933 0%, #040e1e 100%); border-top: 1px solid rgba(255,153,0,0.3);">
	<div class="container">
		<div class="row justify-content-between align-items-center">
			<div class="col-md-8 text-white mb-3 mb-md-0">
				<h2 class="text-white font-weight-bold mb-2">Prêt à Démarrer Votre Projet avec RMC BTP ?</h2>
				<p class="mb-0 text-white-50">Nos ingénieurs et métreurs examinent vos plans et vous remettent une étude technique détaillée sous 24h.</p>
			</div>
			<div class="col-md-4 text-md-right">
				<a href="contact.php?type=devis" class="btn btn-gold-glow">
					<i class="fa fa-calculator mr-2"></i> Demander une Cotation Gratuite
				</a>
			</div>
		</div>
	</div>
</section>

<!-- ========================================================================
     SCRIPTS INTERACTIFS (SLIDER AVANT/APRÈS & SIMULATEUR DEVIS)
     ======================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
	// 1. Script Comparatif Avant/Après Interactif
	const slider = document.getElementById('baRangeSlider');
	const beforeLayer = document.getElementById('baBeforeLayer');
	const handle = document.getElementById('baHandle');

	if (slider && beforeLayer && handle) {
		function updateBA() {
			const val = slider.value;
			beforeLayer.style.width = val + '%';
			handle.style.left = val + '%';
		}
		slider.addEventListener('input', updateBA);
		slider.addEventListener('change', updateBA);
		updateBA();
	}

	// 2. Script Simulateur de Devis Dynamique
	const rangeSurface = document.getElementById('simSurfaceRange');
	const labelSurface = document.getElementById('simSurfaceVal');
	const selectType = document.getElementById('simProjectType');
	const priceResult = document.getElementById('simPriceResult');
	const ctaBtn = document.getElementById('simCtaBtn');

	const rates = {
		residential: { min: 150000, max: 200000 }, // FCFA / m²
		building: { min: 180000, max: 240000 },
		vrd: { min: 35000, max: 55000 },
		industrial: { min: 95000, max: 135000 }
	};

	function calculateSim() {
		if (!rangeSurface || !selectType || !priceResult) return;
		const surface = parseInt(rangeSurface.value, 10);
		labelSurface.textContent = surface.toLocaleString('fr-FR') + ' m²';

		const type = selectType.value;
		const rate = rates[type] || rates.residential;

		const minTotal = Math.round(surface * rate.min);
		const maxTotal = Math.round(surface * rate.max);

		priceResult.textContent = minTotal.toLocaleString('fr-FR') + ' - ' + maxTotal.toLocaleString('fr-FR') + ' FCFA';
		if (ctaBtn) {
			ctaBtn.href = 'contact.php?type=devis&surface=' + surface + '&ouvrage=' + encodeURIComponent(selectType.options[selectType.selectedIndex].text);
		}
	}

	if (rangeSurface && selectType) {
		rangeSurface.addEventListener('input', calculateSim);
		selectType.addEventListener('change', calculateSim);
		calculateSim();
	}
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
