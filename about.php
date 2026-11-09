<?php
$pageTitle = "À Propos de RMC BTP SARL | Notre Histoire, Vision & Réalisations";
$pageDescription = "Découvrez RMC BTP SARL : créée en 2017 par ses cofondateurs Chirac, Marcair et Rodrigue Djomo. Plus de 700 réalisations au Cameroun et à l'international.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$expYears = getSetting('stats_experience', '9');
$totalProjects = getSetting('stats_projects', '700');
$satisfaction = getSetting('stats_satisfaction', '99');
$equipments = getSetting('stats_equipments', '52');
$managerName = getSetting('manager_name', 'M. Rodrigue DJOMO');
$managerRole = getSetting('manager_role', 'Fondateur & Directeur Général');
?>

<!-- BANNIÈRE EN-TÊTE -->
<section class="hero-wrap hero-wrap-2" style="background-image: url('images/IMG-20261029-WA0110.jpg'); background-position: center top;" data-stellar-background-ratio="0.5">
	<div class="overlay"></div>
	<div class="container">
		<div class="row no-gutters slider-text align-items-end justify-content-start">
			<div class="col-md-9 ftco-animate pb-5">
				<p class="breadcrumbs mb-2"><span class="mr-2"><a href="index.php">Accueil <i class="fa fa-chevron-right"></i></a></span> <span>À Propos <i class="fa fa-chevron-right"></i></span></p>
				<h1 class="mb-3 bread text-white font-weight-bold">Construire Aujourd'hui les Solutions de Demain</h1>
			</div>
		</div>
	</div>
</section>

<!-- PRÉSENTATION DE L'ENTREPRISE, HISTOIRE & VISION -->
<section class="ftco-section">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-md-6 ftco-animate">
				<div class="img about-image rounded-xl shadow overflow-hidden position-relative" style="background-image: url('images/fondateurs/rodrigue-djomo.jpg'); min-height: 520px; background-size: cover; background-position: center top; border: 3px solid #fff; border-radius: 20px;">
					<div class="badge-btp position-absolute" style="bottom: 20px; left: 20px; background: rgba(12, 46, 96, 0.9); backdrop-filter: blur(10px); color: #fff; padding: 10px 20px; border-radius: 50px; border: 1px solid rgba(217, 119, 6, 0.5);">
						<i class="fa fa-calendar-check-o text-warning mr-1"></i> Fondée en 2017 • Yaoundé & International
					</div>
				</div>
			</div>
			<div class="col-md-6 pl-md-5 ftco-animate mt-4 mt-md-0">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">NOTRE HISTOIRE & NOTRE VISION</span>
				<h2 class="mb-4 font-weight-bold" style="font-size: 2.2rem;">Une Ambition Née en 2017</h2>
				<p class="text-muted" style="line-height: 1.8;">
					Créée en <strong>2017</strong> par trois cofondateurs — <strong>Chirac, Marcair et Rodrigue</strong> —, <strong>RMCBTP</strong> est née d'une ambition commune : développer des solutions novatrices et durables dans le secteur du bâtiment et des travaux publics, en plaçant la qualité, l'innovation technique et la satisfaction des clients au sommet de nos priorités.
				</p>
				<p class="text-muted" style="line-height: 1.8;">
					Au fil de son évolution, cette initiative s'est développée pour devenir la grande structure <strong>RMC BTP SARL</strong>, avec une vision résolument tournée vers l'expansion, la diversification des services (avec nos pôles intégrés <strong>SIMS Quincaillerie</strong> et <strong>SIMS Décor</strong>) et une ouverture affirmée à l'international.
				</p>
				<p class="text-muted" style="line-height: 1.8;">
					Aujourd'hui, <strong>RMC BTP SARL</strong> propose des solutions clés en main d'excellence adaptées aux exigences des particuliers, des entreprises et des investisseurs de la diaspora, au Cameroun comme à l'étranger.
				</p>
				<div class="p-3 mb-4 rounded" style="background: #fffbeb; border-left: 4px solid #d97706;">
					<h6 class="font-weight-bold mb-1" style="color: #92400e;"><i class="fa fa-quote-left mr-2"></i>Notre Devise</h6>
					<p class="font-italic text-dark mb-0">« RMC BTP SARL — Construire aujourd'hui les solutions de demain. »</p>
				</div>
				<div class="row mt-4">
					<div class="col-4 mb-3">
						<div class="border-left border-primary pl-3">
							<h4 class="font-weight-bold text-dark mb-0">+<?= htmlspecialchars($totalProjects) ?></h4>
							<small class="text-muted">Travaux Réalisés</small>
						</div>
					</div>
					<div class="col-4 mb-3">
						<div class="border-left border-warning pl-3">
							<h4 class="font-weight-bold text-dark mb-0">52</h4>
							<small class="text-muted">À l'International</small>
						</div>
					</div>
					<div class="col-4 mb-3">
						<div class="border-left border-primary pl-3">
							<h4 class="font-weight-bold text-dark mb-0">64</h4>
							<small class="text-muted">Pays Partenaires</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- SECTION COFONDATEURS & LEADERSHIP -->
<section class="ftco-section bg-light" id="fondateurs-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-9 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">AUX ORIGINES DE RMC BTP</span>
				<h2 class="mb-4 font-weight-bold">Nos Cofondateurs</h2>
				<p class="text-muted lead">RMC BTP SARL s'inscrit dans la continuité de l'initiative entrepreneuriale lancée en 2017 par ses trois cofondateurs :</p>
			</div>
		</div>

		<div class="row justify-content-center">
			<!-- Cofondateur 1 : CHIRAC -->
			<div class="col-lg-4 col-md-6 ftco-animate mb-4">
				<div class="staff bg-white rounded-xl shadow-sm overflow-hidden h-100 d-flex flex-column justify-content-between" style="border: 1.5px solid #e2e8f0; border-radius: 18px;">
					<div>
						<!-- Photo Réelle Cofondateur Chirac -->
						<div class="fondateur-photo-slot position-relative" style="height: 340px; background-image: url('images/fondateurs/chirac.jpg'); background-size: cover; background-position: center top;">
							<div class="position-absolute" style="top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(12,46,96,0.85) 0%, rgba(0,0,0,0) 50%);"></div>
							<div class="position-absolute" style="bottom: 15px; left: 20px;">
								<span class="badge badge-warning text-dark font-weight-bold px-3 py-1 shadow-sm">
									<i class="fa fa-handshake-o mr-1"></i> Initiative 2017
								</span>
							</div>
						</div>
						<div class="p-4 text-center">
							<h4 class="font-weight-bold mb-1 text-dark" style="font-size: 1.4rem;">CHIRAC</h4>
							<span class="text-primary font-weight-bold d-block mb-3" style="font-size: 0.95rem;">Cofondateur RMCBTP</span>
							<p class="text-muted small" style="line-height: 1.7;">
								Acteur clé de la création de la vision originelle en 2017, engagé dans le développement d'infrastructures pérennes et la culture de la rigueur opérationnelle.
							</p>
						</div>
					</div>
					<div class="p-3 bg-light border-top text-center">
						<small class="text-muted font-weight-bold"><i class="fa fa-check-circle text-success mr-1"></i> Pilier Fondateur</small>
					</div>
				</div>
			</div>

			<!-- Cofondateur 2 : MAKAIR -->
			<div class="col-lg-4 col-md-6 ftco-animate mb-4">
				<div class="staff bg-white rounded-xl shadow-sm overflow-hidden h-100 d-flex flex-column justify-content-between" style="border: 1.5px solid #e2e8f0; border-radius: 18px;">
					<div>
						<!-- Photo Réelle Cofondateur Makair -->
						<div class="fondateur-photo-slot position-relative" style="height: 340px; background-image: url('images/fondateurs/makair.jpg'); background-size: cover; background-position: center top;">
							<div class="position-absolute" style="top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(12,46,96,0.85) 0%, rgba(0,0,0,0) 50%);"></div>
							<div class="position-absolute" style="bottom: 15px; left: 20px;">
								<span class="badge badge-warning text-dark font-weight-bold px-3 py-1 shadow-sm">
									<i class="fa fa-handshake-o mr-1"></i> Initiative 2017
								</span>
							</div>
						</div>
						<div class="p-4 text-center">
							<h4 class="font-weight-bold mb-1 text-dark" style="font-size: 1.4rem;">MAKAIR</h4>
							<span class="text-primary font-weight-bold d-block mb-3" style="font-size: 0.95rem;">Cofondateur RMCBTP</span>
							<p class="text-muted small" style="line-height: 1.7;">
								Cofondateur engagé dans la dynamique de croissance, le développement de partenariats solides et l'ouverture des projets au-delà des frontières nationales.
							</p>
						</div>
					</div>
					<div class="p-3 bg-light border-top text-center">
						<small class="text-muted font-weight-bold"><i class="fa fa-check-circle text-success mr-1"></i> Pilier Fondateur</small>
					</div>
				</div>
			</div>

			<!-- Cofondateur 3 & Direction : RODRIGUE DJOMO (Mis en avant) -->
			<div class="col-lg-4 col-md-6 ftco-animate mb-4">
				<div class="staff bg-white rounded-xl shadow overflow-hidden h-100 d-flex flex-column justify-content-between" style="border: 2px solid #0c2e60; border-radius: 18px;">
					<div>
						<!-- Photo Officielle Rodrigue Djomo -->
						<div class="fondateur-photo-slot position-relative" style="height: 340px; background-image: url('images/fondateurs/rodrigue-djomo.jpg'); background-size: cover; background-position: center top;">
							<div class="position-absolute" style="top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(12,46,96,0.85) 0%, rgba(0,0,0,0) 50%);"></div>
							<div class="position-absolute" style="bottom: 15px; left: 20px;">
								<span class="badge badge-warning text-dark font-weight-bold px-3 py-1 shadow-sm">
									<i class="fa fa-star mr-1"></i> Cofondateur & Directeur Général
								</span>
							</div>
						</div>
						<div class="p-4 text-center">
							<h4 class="font-weight-bold mb-1 text-dark" style="font-size: 1.4rem;">RODRIGUE DJOMO</h4>
							<span class="badge badge-light border text-primary font-weight-bold px-3 py-1 mb-3" style="font-size: 0.9rem;">Directeur Général</span>
							<p class="text-muted small" style="line-height: 1.7;">
								Pilote opérationnel et stratégique de <strong>RMC BTP SARL</strong>, articulant le gros œuvre, la centrale <strong>SIMS Quincaillerie</strong> et le pôle <strong>SIMS Décor</strong> pour offrir des édifices clés en main.
							</p>
						</div>
					</div>
					<div class="p-3 bg-light border-top text-center">
						<small class="text-muted font-weight-bold"><i class="fa fa-shield text-primary mr-1"></i> Direction Générale • Yaoundé</small>
					</div>
				</div>
			</div>
		</div>

		<!-- DIRECTION TECHNIQUE & INGÉNIERIE : INGÉNIEUR TOUKAM PATRICK -->
		<div class="row justify-content-center mt-5">
			<div class="col-lg-8 col-md-10 ftco-animate">
				<div class="staff bg-white rounded-xl shadow overflow-hidden d-flex flex-column flex-md-row align-items-stretch" style="border: 2px solid rgba(12, 46, 96, 0.15); border-radius: 20px;">
					<!-- Photo Haute Définition Ingénieur Toukam Patrick (_JMP6320) -->
					<div class="col-md-5 p-0 position-relative" style="min-height: 360px;">
						<div class="w-100 h-100" style="background-image: url('images/_JMP6320.jpg'); background-size: cover; background-position: center top;"></div>
						<div class="position-absolute" style="bottom: 15px; left: 15px;">
							<span class="badge badge-primary text-white font-weight-bold px-3 py-1 shadow-sm">
								<i class="fa fa-hard-hat mr-1"></i> Direction Technique
							</span>
						</div>
					</div>
					<!-- Présentation Ingénieur Toukam Patrick -->
					<div class="col-md-7 p-4 p-lg-5 d-flex flex-column justify-content-between">
						<div>
							<span class="text-uppercase font-weight-bold" style="color: #0c2e60; font-size: 12px; letter-spacing: 1.5px;">Bureau d'Études & Suivi de Chantier</span>
							<h3 class="font-weight-bold text-dark mt-1 mb-2" style="font-size: 1.6rem;">Ing. TOUKAM PATRICK</h3>
							<p class="badge badge-light border text-primary font-weight-bold px-3 py-1 mb-3" style="font-size: 0.9rem;">Ingénieur Suivi de Travaux & Génie Civil</p>
							<p class="text-muted" style="line-height: 1.8; font-size: 0.95rem;">
								Responsable des études de structures, du calcul de portance et du contrôle qualité permanent sur l'ensemble de nos chantiers. M. TOUKAM veille au strict respect des dosages béton (350 kg/m³ vibré), des normes parasismiques et des rétro-plannings contractuels.
							</p>
						</div>
						<div class="pt-3 border-top d-flex justify-content-between align-items-center">
							<small class="text-muted font-weight-bold"><i class="fa fa-certificate text-warning mr-1"></i> Ingénierie & Suivi Qualité</small>
							<a href="contact.php" class="btn btn-sm btn-outline-primary px-3 font-weight-bold">Échanger sur vos Plans</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</section>

<!-- VALEURS & ENGAGEMENTS DE L'ENTREPRISE -->
<section class="ftco-section">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-md-8 text-center heading-section ftco-animate">
				<span class="subheading text-primary font-weight-bold" style="letter-spacing: 2px;">NOTRE ENGAGEMENT</span>
				<h2 class="mb-4 font-weight-bold">Des Solutions Durables & Fonctionnelles</h2>
				<p class="text-muted">Chez RMC BTP SARL, nous mobilisons le meilleur de la technologie constructive pour garantir la pérennité de vos investissements.</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 ftco-animate mb-4">
				<div class="bg-white p-4 rounded-xl shadow-sm text-center h-100" style="border: 1px solid #e2e8f0; border-radius: 16px;">
					<div class="icon mb-3 text-primary" style="font-size: 40px;"><i class="fa fa-cubes"></i></div>
					<h4 class="font-weight-bold mb-3">Synergie Intégrée 360°</h4>
					<p class="text-muted small">De l'étude de structure à l'approvisionnement direct via SIMS Quincaillerie et aux finitions de luxe avec SIMS Décor, vos chantiers avancent sans intermédiaire.</p>
				</div>
			</div>
			<div class="col-md-4 ftco-animate mb-4">
				<div class="bg-white p-4 rounded-xl shadow-sm text-center h-100" style="border: 1px solid #e2e8f0; border-radius: 16px;">
					<div class="icon mb-3 text-warning" style="font-size: 40px;"><i class="fa fa-globe"></i></div>
					<h4 class="font-weight-bold mb-3">Envergure Internationale</h4>
					<p class="text-muted small">52 travaux menés à l'international et un réseau actif dans plus de 64 pays permettant d'importer les meilleurs matériaux et techniques de construction.</p>
				</div>
			</div>
			<div class="col-md-4 ftco-animate mb-4">
				<div class="bg-white p-4 rounded-xl shadow-sm text-center h-100" style="border: 1px solid #e2e8f0; border-radius: 16px;">
					<div class="icon mb-3 text-primary" style="font-size: 40px;"><i class="fa fa-shield"></i></div>
					<h4 class="font-weight-bold mb-3">Garantie & Rigueur</h4>
					<p class="text-muted small">Respect scrupuleux des normes du génie civil, dosages de béton certifiés à 350 kg/m³ vibré et rétro-plannings d'exécution verrouillés.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
