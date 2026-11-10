<?php
$companyName = getSetting('company_name', 'RMC BTP SARL');
$phonePrimary = getSetting('phone_primary', '(+237) 697 39 84 55');
$phoneSecondary = getSetting('phone_secondary', '(+237) 693 90 91 59');
$whatsappNumber = getSetting('whatsapp_number', '237697398455');
$emailContact = getSetting('email_contact', 'info@rmcbtp.com');
$address = getSetting('address', 'Yaoundé & Douala, Cameroun');
$contractRef = getSetting('contract_ref', 'CT-WEB-RMCBTP-2026-01');
$developerCredit = getSetting('developer_credit', 'M. NDONGMO TEJIONA BEN ARTHUR (+237 691 67 06 26)');

$siteLogoWhite = getVisualInfo($identityVisuals ?? getPageVisuals($pdo, 'identity'), 'site_logo_white', 'images/logo-white.png', 'center center');
?>
	<!-- Pied de Page Principal -->
	<footer class="ftco-footer ftco-bg-dark ftco-section">
		<div class="container">
			<div class="row mb-5">
				<!-- Présentation Société -->
				<div class="col-lg-4 col-md-6 mb-4 mb-md-0">
					<div class="ftco-footer-widget">
						<div class="d-flex align-items-center mb-3">
							<img src="<?= htmlspecialchars($siteLogoWhite['url']) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="height: 60px; width: auto; object-fit: contain; margin-right: 14px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">
							<div>
								<h2 class="ftco-heading-2 text-white font-weight-bold mb-0" style="font-size: 1.35rem; line-height: 1.1;">
									<?= htmlspecialchars($companyName) ?>
								</h2>
								<small class="text-warning font-weight-bold" style="letter-spacing: 0.5px;">Construire aujourd'hui les solutions de demain</small>
							</div>
						</div>
						<p class="text-muted">
							Entreprise de référence en BTP et Génie Civil, spécialisée dans la <strong>construction de bâtiments clés en main, les travaux publics, le gros œuvre et la rénovation</strong> au Cameroun et à l'international. Synergie directe avec <strong>SIMS Quincaillerie</strong> et <strong>SIMS Décor</strong>.
						</p>
						<div class="mt-3">
							<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">
								<i class="fa fa-certificate mr-1"></i> Référence BTP Internationale
							</span>
						</div>
					</div>
				</div>

				<!-- Pôles de Services -->
				<div class="col-lg-3 col-md-6 mb-4 mb-md-0">
					<div class="ftco-footer-widget">
						<h2 class="ftco-heading-2 text-white font-weight-bold">Nos Métiers BTP</h2>
						<ul class="list-unstyled">
							<li><a href="services.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Gros Œuvre & Maçonnerie</a></li>
							<li><a href="services.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Génie Civil & VRD</a></li>
							<li><a href="services.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Second Œuvre & Finitions</a></li>
							<li><a href="services.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Rénovation Immobilière</a></li>
							<li><a href="services.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Études Techniques & Plans</a></li>
						</ul>
					</div>
				</div>

				<!-- Navigation Rapide -->
				<div class="col-lg-2 col-md-6 mb-4 mb-md-0">
					<div class="ftco-footer-widget">
						<h2 class="ftco-heading-2 text-white font-weight-bold">Liens Utiles</h2>
						<ul class="list-unstyled">
							<li><a href="index.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Accueil</a></li>
							<li><a href="about.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>À Propos & Équipe</a></li>
							<li><a href="project.php" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Galerie Chantiers</a></li>
							<li><a href="contact.php?type=devis" class="py-1 d-block"><span class="fa fa-angle-right mr-2 text-primary"></span>Demande de Devis</a></li>
							<li><a href="admin/login.php" class="py-1 d-block text-warning"><span class="fa fa-lock mr-2"></span>Espace Admin</a></li>
						</ul>
					</div>
				</div>

				<!-- Coordonnées Siège & Direction -->
				<div class="col-lg-3 col-md-6 mb-4 mb-md-0">
					<div class="ftco-footer-widget">
						<h2 class="ftco-heading-2 text-white font-weight-bold">Siège & Contact</h2>
						<div class="block-23 mb-3">
							<ul class="list-unstyled">
								<li class="d-flex mb-2">
									<span class="fa fa-map-marker text-primary mr-3 pt-1"></span>
									<span class="text-muted"><?= htmlspecialchars($address) ?></span>
								</li>
								<li class="d-flex mb-2">
									<span class="fa fa-phone text-primary mr-3 pt-1"></span>
									<span class="text-white"><?= htmlspecialchars($phonePrimary) ?><br><small class="text-muted"><?= htmlspecialchars($phoneSecondary) ?></small></span>
								</li>
								<li class="d-flex mb-2">
									<span class="fa fa-envelope text-primary mr-3 pt-1"></span>
									<span class="text-muted"><?= htmlspecialchars($emailContact) ?></span>
								</li>
								<li class="d-flex mb-2">
									<span class="fab fa-whatsapp text-success mr-3 pt-1 font-weight-bold" style="font-size: 18px;"></span>
									<span class="text-success font-weight-bold">WhatsApp Direct 24/7</span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<!-- Ligne de Copyright & Mentions Contractuelles -->
			<div class="row pt-4 border-top border-secondary">
				<div class="col-md-7 text-center text-md-left mb-2 mb-md-0">
					<p class="mb-0 text-muted small">
						&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($companyName) ?></strong>. Tous droits réservés.
						• Direction Générale : <em>M. Rodrigue DJOMO</em>.
					</p>
				</div>
				<div class="col-md-5 text-center text-md-right">
					<p class="mb-0 text-muted small">
						Conception & Réalisation : <strong><?= htmlspecialchars($developerCredit) ?></strong>
					</p>
				</div>
			</div>
		</div>
	</footer>

	<!-- Modal de Demande de Devis Express -->
	<div class="modal fade" id="modalDevisRapide" tabindex="-1" role="dialog" aria-labelledby="modalDevisRapideTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content shadow-lg border-0">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title font-weight-bold text-white" id="modalDevisRapideTitle">
						<i class="fa fa-calculator mr-2"></i> Étude de Prix Express • <?= htmlspecialchars($companyName) ?>
					</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body p-4">
					<p class="text-muted small mb-3">
						Remplissez ce formulaire succinct. Un ingénieur ou conducteur de travaux de <?= htmlspecialchars($companyName) ?> vous recontactera sous 24h.
					</p>
					<form action="process-contact.php" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="form_type" value="Devis Express">
						<div class="form-row">
							<div class="form-group col-md-6">
								<label class="small font-weight-bold">Nom complet *</label>
								<input type="text" name="full_name" class="form-control" placeholder="Votre nom" required>
							</div>
							<div class="form-group col-md-6">
								<label class="small font-weight-bold">Téléphone (WhatsApp) *</label>
								<input type="tel" name="phone" class="form-control" placeholder="(+237) 6..." required>
							</div>
						</div>
						<div class="form-group">
							<label class="small font-weight-bold">Email</label>
							<input type="email" name="email" class="form-control" placeholder="nom@exemple.com">
						</div>
						<div class="form-group">
							<label class="small font-weight-bold">Type d'Ouvrage ou Service *</label>
							<select name="subject" class="form-control" required>
								<option value="Gros Œuvre & Maçonnerie">Gros Œuvre & Maçonnerie</option>
								<option value="Second Œuvre & Finitions">Second Œuvre & Finitions</option>
								<option value="Génie Civil & VRD">Génie Civil & VRD</option>
								<option value="Rénovation / Réhabilitation">Rénovation / Réhabilitation</option>
								<option value="Études Techniques & Architecture">Études Techniques & Architecture</option>
								<option value="Autre demande BTP">Autre demande BTP</option>
							</select>
						</div>
						<div class="form-group">
							<label class="small font-weight-bold">Description sommaire du projet *</label>
							<textarea name="message" class="form-control" rows="3" placeholder="Localisation du chantier, superficie estimée, besoins spécifiques..." required></textarea>
						</div>
						<div class="form-group">
							<label class="small font-weight-bold">Joindre un plan ou document (Optionnel - PDF, Image)</label>
							<input type="file" name="attachment" class="form-control-file border p-1 rounded" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
						</div>
						<button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
							<i class="fa fa-paper-plane mr-2"></i> Envoyer ma demande de devis
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Bouton Flottant WhatsApp Officiel -->
	<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsappNumber) ?>?text=Bonjour%20RMC%20BTP%20SARL,%20je%20souhaite%20obtenir%20des%20informations%20et%20un%20devis%20pour%20mon%20projet%20de%20construction." 
	   class="whatsapp-float-btn" 
	   target="_blank" 
	   rel="noopener noreferrer" 
	   title="Échanger en direct sur WhatsApp avec RMC BTP">
		<i class="fab fa-whatsapp"></i>
		<span>WhatsApp Devis Direct</span>
	</a>

	<!-- Concierge & Messagerie Intelligente BTP -->
	<?php require_once __DIR__ . '/smart-chat.php'; ?>

	<!-- Loader désactivé pour affichage instantané sans blocage d'écran -->
	<div id="ftco-loader" style="display: none !important;"></div>

	<!-- Scripts JS avec Fallback CDN Haute Disponibilité -->
	<script src="js/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"><\/script>')</script>
	<script src="js/jquery-migrate-3.0.1.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.easing.1.3.js"></script>
	<script src="js/jquery.waypoints.min.js"></script>
	<script src="js/jquery.stellar.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.magnific-popup.min.js"></script>
	<script src="js/jquery.animateNumber.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jquery.timepicker.min.js"></script>
	<script src="js/scrollax.min.js"></script>
	<script src="js/main.js"></script>
	<!-- Librairies Spectaculaires Externes (Three.js, OrbitControls, Leaflet & html2pdf) -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

	<!-- Moteur 3D Interactif & Expérience Visuelle RMC BTP SARL (Cache Busting forcé) -->
	<script src="js/fee-3d-experience.js?v=<?= time() ?>"></script>
	<!-- Modules Spectaculaires RMC BTP SARL (Cache Busting forcé) -->
	<script src="js/fee-architect-cursor.js?v=<?= time() ?>"></script>
	<script src="js/fee-bim-3d.js?v=<?= time() ?>"></script>
	<script src="js/fee-chantier-tracker.js?v=<?= time() ?>"></script>
	<script src="js/fee-map-cameroun.js?v=<?= time() ?>"></script>
	<script src="js/fee-configurateur-pro.js?v=<?= time() ?>"></script>
</body>
</html>
