<?php
/**
 * Gestionnaire Exhaustif de Visuels de Repère par Page & Section - RMC BTP SARL
 * Permet d'administrer 100% des images, bannières, logos et portraits du site.
 */

function initPageVisualsTable($pdo) {
    static $initialized = false;
    if ($initialized) return;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS page_visuals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_slug VARCHAR(50) NOT NULL,
            section_key VARCHAR(50) NOT NULL,
            section_name VARCHAR(120) NOT NULL,
            title VARCHAR(150) NOT NULL,
            description VARCHAR(255) NULL,
            image_url VARCHAR(255) NOT NULL,
            default_url VARCHAR(255) NOT NULL,
            focal_point VARCHAR(30) DEFAULT 'center top',
            sort_order INT DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_page_sec (page_slug, section_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Liste exhaustive des visuels du site RMC BTP SARL
        $defaults = [
            // ==========================================
            // 0. IDENTITÉ VISUELLE & LOGOS (header / footer / favicon)
            // ==========================================
            [
                'page_slug' => 'identity',
                'section_key' => 'site_logo',
                'section_name' => "Identité Visuelle & Navigation",
                'title' => "Logo Principal du Site (En-tête & Barre de Navigation)",
                'description' => "Logo affiché dans la barre de navigation sur fond clair (Format PNG avec transparence recommandé).",
                'image_url' => 'images/logo.png',
                'default_url' => 'images/logo.png',
                'focal_point' => 'center center',
                'sort_order' => 1
            ],
            [
                'page_slug' => 'identity',
                'section_key' => 'site_logo_white',
                'section_name' => "Identité Visuelle & Navigation",
                'title' => "Logo Pied de Page (Footer sur Fond Sombre)",
                'description' => "Logo blanc ou clair contrastant pour le bas de page officiel.",
                'image_url' => 'images/logo-white.png',
                'default_url' => 'images/logo-white.png',
                'focal_point' => 'center center',
                'sort_order' => 2
            ],
            [
                'page_slug' => 'identity',
                'section_key' => 'site_favicon',
                'section_name' => "Identité Visuelle & Navigation",
                'title' => "Favicon du Site (Icône d'onglet de navigateur)",
                'description' => "Petite icône carrée représentant la marque dans les onglets et favoris.",
                'image_url' => 'images/favicon-32x32.png',
                'default_url' => 'images/favicon-32x32.png',
                'focal_point' => 'center center',
                'sort_order' => 3
            ],

            // ==========================================
            // 1. PAGE D'ACCUEIL (index.php)
            // ==========================================
            [
                'page_slug' => 'home',
                'section_key' => 'hero_slide_1',
                'section_name' => "Section A : Hero Slider Principal",
                'title' => "Slide 1 : Bâtiments d'Exception & Clés en main",
                'description' => "Grand visuel plein écran en ouverture du site.",
                'image_url' => 'images/_JMP6225.jpg',
                'default_url' => 'images/_JMP6225.jpg',
                'focal_point' => 'center center',
                'sort_order' => 10
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'hero_slide_2',
                'section_name' => "Section A : Hero Slider Principal",
                'title' => "Slide 2 : Cadence Terrain & Gros Œuvre",
                'description' => "Deuxième arrière-plan du carrousel de bienvenue.",
                'image_url' => 'images/IMG-20261029-WA0099.jpg',
                'default_url' => 'images/IMG-20261029-WA0099.jpg',
                'focal_point' => 'center center',
                'sort_order' => 20
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'hero_slide_3',
                'section_name' => "Section A : Hero Slider Principal",
                'title' => "Slide 3 : Maîtrise des Budgets & Finitions",
                'description' => "Troisième arrière-plan du carrousel de bienvenue.",
                'image_url' => 'images/IMG-20261029-WA0098.jpg',
                'default_url' => 'images/IMG-20261029-WA0098.jpg',
                'focal_point' => 'center center',
                'sort_order' => 25
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ecosystem_btp',
                'section_name' => "Section B : Synergie en 3 Pôles d'Excellence",
                'title' => "Pôle 1 : RMC BTP SARL (Génie Civil, Gros Œuvre & Clés en Main)",
                'description' => "Illustration de la carte RMC BTP dans la section Synergie.",
                'image_url' => 'images/_JMP6225.jpg',
                'default_url' => 'images/_JMP6225.jpg',
                'focal_point' => 'center center',
                'sort_order' => 30
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ecosystem_quincaillerie',
                'section_name' => "Section B : Synergie en 3 Pôles d'Excellence",
                'title' => "Pôle 2 : SIMS Quincaillerie (Fourniture Matériaux & Aciers)",
                'description' => "Illustration de la carte SIMS Quincaillerie dans la section Synergie.",
                'image_url' => 'images/_JMP6308.jpg',
                'default_url' => 'images/_JMP6308.jpg',
                'focal_point' => 'center center',
                'sort_order' => 40
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ecosystem_decor',
                'section_name' => "Section B : Synergie en 3 Pôles d'Excellence",
                'title' => "Pôle 3 : SIMS Décor (Staff, Marbres & Finitions Haut Standing)",
                'description' => "Illustration de la carte SIMS Décor dans la section Synergie.",
                'image_url' => 'images/_JMP6286.jpg',
                'default_url' => 'images/_JMP6286.jpg',
                'focal_point' => 'center center',
                'sort_order' => 50
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'showcase_card_1',
                'section_name' => "Section B.2 : Résultats Concrets Livrés (Showcase Diaspora)",
                'title' => "Projet Livré 1 : Villa Duplex Contemporaine R+1 (Bastos/Odza)",
                'description' => "Photo de la villa livrée clé en main en 7 mois avec finitions complètes.",
                'image_url' => 'images/wa0062-framed-16x9.jpg',
                'default_url' => 'images/wa0062-framed-16x9.jpg',
                'focal_point' => 'center center',
                'sort_order' => 52
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'showcase_card_2',
                'section_name' => "Section B.2 : Résultats Concrets Livrés (Showcase Diaspora)",
                'title' => "Projet Livré 2 : Immeuble Mixte Résidentiel & Commercial",
                'description' => "Photo de l'immeuble d'appartements et commerces livré pour investisseur.",
                'image_url' => 'images/IMG-20261015-WA0062.jpg',
                'default_url' => 'images/IMG-20261015-WA0062.jpg',
                'focal_point' => 'center center',
                'sort_order' => 54
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'showcase_card_3',
                'section_name' => "Section B.2 : Résultats Concrets Livrés (Showcase Diaspora)",
                'title' => "Projet Livré 3 : Murs de Soutènement & Sécurisation",
                'description' => "Photo de l'ouvrage de soutènement banché garanti 10 ans sur terrain difficile.",
                'image_url' => 'images/IMG-20261015-WA0073.jpg',
                'default_url' => 'images/IMG-20261015-WA0073.jpg',
                'focal_point' => 'center center',
                'sort_order' => 56
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'about_photo',
                'section_name' => "Section C : À Propos & Direction",
                'title' => "Photo Institutionnelle Présentation & Équipe",
                'description' => "Image de l'encart de présentation générale de l'entreprise sur l'accueil.",
                'image_url' => 'images/about.jpg',
                'default_url' => 'images/about.jpg',
                'focal_point' => 'center top',
                'sort_order' => 60
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'dg_portrait',
                'section_name' => "Section C : À Propos & Direction",
                'title' => "Portrait Direction Générale (M. Rodrigue DJOMO)",
                'description' => "Photo officielle du fondateur et Directeur Général de RMC BTP.",
                'image_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'default_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'focal_point' => 'center top',
                'sort_order' => 65
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ba_before',
                'section_name' => "Section C.1 : Comparateur Avant / Après Travaux",
                'title' => "Visuel AVANT : État Initial ou Gros Œuvre Brut",
                'description' => "Image du chantier avant les interventions et finitions.",
                'image_url' => 'images/_JMP6244.jpg',
                'default_url' => 'images/_JMP6244.jpg',
                'focal_point' => 'center center',
                'sort_order' => 67
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ba_after',
                'section_name' => "Section C.1 : Comparateur Avant / Après Travaux",
                'title' => "Visuel APRÈS : Rendu Final & Finitions Clés en Main",
                'description' => "Image de l'ouvrage achevé avec finitions haut de gamme.",
                'image_url' => 'images/_JMP6225.jpg',
                'default_url' => 'images/_JMP6225.jpg',
                'focal_point' => 'center center',
                'sort_order' => 68
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'counter_bg',
                'section_name' => "Section D : Statistiques & Chiffres Clés",
                'title' => "Arrière-plan des Chiffres & Indicateurs BTP",
                'description' => "Image de fond avec effet parallaxe sous les compteurs.",
                'image_url' => 'images/bg_2.jpg',
                'default_url' => 'images/bg_2.jpg',
                'focal_point' => 'center center',
                'sort_order' => 70
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'cta_bg',
                'section_name' => "Section E : Appel à l'Action & Contact Final",
                'title' => "Bannière d'Appel à l'Action (Devis Express)",
                'description' => "Bannière en pied de page d'accueil invitant à soumettre un projet.",
                'image_url' => 'images/bg_1.jpg',
                'default_url' => 'images/bg_1.jpg',
                'focal_point' => 'center center',
                'sort_order' => 80
            ],

            // ==========================================
            // 2. PAGE À PROPOS (about.php)
            // ==========================================
            [
                'page_slug' => 'about',
                'section_key' => 'about_banner',
                'section_name' => "En-tête de la Page À Propos",
                'title' => "Bannière Hero Page À Propos",
                'description' => "Grande bannière en haut de la page À Propos.",
                'image_url' => 'images/IMG-20261029-WA0110.jpg',
                'default_url' => 'images/IMG-20261029-WA0110.jpg',
                'focal_point' => 'center top',
                'sort_order' => 110
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'about_presentation',
                'section_name' => "Présentation Historique & Vision 2017",
                'title' => "Photo Institutionnelle de l'Entreprise",
                'description' => "Grande photo illustrant l'histoire et l'ambition née en 2017.",
                'image_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'default_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'focal_point' => 'center top',
                'sort_order' => 120
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'founder_chirac',
                'section_name' => "Gouvernance & Cofondateurs RMCBTP",
                'title' => "Portrait Cofondateur : CHIRAC",
                'description' => "Photo officielle de M. CHIRAC, cofondateur originel de 2017.",
                'image_url' => 'images/fondateurs/chirac.jpg',
                'default_url' => 'images/fondateurs/chirac.jpg',
                'focal_point' => 'center top',
                'sort_order' => 130
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'founder_makair',
                'section_name' => "Gouvernance & Cofondateurs RMCBTP",
                'title' => "Portrait Cofondateur : MAKAIR",
                'description' => "Photo officielle de M. MAKAIR, cofondateur originel de 2017.",
                'image_url' => 'images/fondateurs/makair.jpg',
                'default_url' => 'images/fondateurs/makair.jpg',
                'focal_point' => 'center top',
                'sort_order' => 140
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'founder_rodrigue',
                'section_name' => "Gouvernance & Cofondateurs RMCBTP",
                'title' => "Portrait Cofondateur & Directeur Général : RODRIGUE DJOMO",
                'description' => "Photo officielle de M. Rodrigue DJOMO, Directeur Général.",
                'image_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'default_url' => 'images/fondateurs/rodrigue-djomo.jpg',
                'focal_point' => 'center top',
                'sort_order' => 150
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'direction_toukam',
                'section_name' => "Direction Technique & Ingénierie",
                'title' => "Portrait Directeur Technique : INGÉNIEUR TOUKAM PATRICK",
                'description' => "Photo grand format de l'Ingénieur en Chef Toukam Patrick.",
                'image_url' => 'images/_JMP6320.jpg',
                'default_url' => 'images/_JMP6320.jpg',
                'focal_point' => 'center top',
                'sort_order' => 160
            ],

            // ==========================================
            // 3. PAGE SERVICES (services.php)
            // ==========================================
            [
                'page_slug' => 'services',
                'section_key' => 'services_banner',
                'section_name' => "En-tête de la Page Services",
                'title' => "Bannière Hero Page Services",
                'description' => "Grande bannière en haut de la page Catalogue des Prestations.",
                'image_url' => 'images/IMG-20261029-WA0101.jpg',
                'default_url' => 'images/IMG-20261029-WA0101.jpg',
                'focal_point' => 'center center',
                'sort_order' => 210
            ],

            // ==========================================
            // 4. PAGE PROJETS / PORTFOLIO (project.php)
            // ==========================================
            [
                'page_slug' => 'projects',
                'section_key' => 'projects_banner',
                'section_name' => "En-tête du Portfolio de Chantiers",
                'title' => "Bannière Hero Page Projets & Réalisations",
                'description' => "Grande bannière en haut de la galerie des réalisations BTP.",
                'image_url' => 'images/IMG-20261029-WA0129.jpg',
                'default_url' => 'images/IMG-20261029-WA0129.jpg',
                'focal_point' => 'center center',
                'sort_order' => 310
            ],

            // ==========================================
            // 5. PAGE CONTACT (contact.php)
            // ==========================================
            [
                'page_slug' => 'contact',
                'section_key' => 'contact_banner',
                'section_name' => "En-tête de la Page Contact",
                'title' => "Bannière Hero Page Contact & Devis Express",
                'description' => "Grande bannière en haut de la page de demande de devis.",
                'image_url' => 'images/IMG-20261029-WA0099.jpg',
                'default_url' => 'images/IMG-20261029-WA0099.jpg',
                'focal_point' => 'center center',
                'sort_order' => 410
            ],

            // ==========================================
            // 6. PAGE BLOG & ACTUALITÉS (blog.php)
            // ==========================================
            [
                'page_slug' => 'blog',
                'section_key' => 'blog_banner',
                'section_name' => "En-tête du Blog & Actualités",
                'title' => "Bannière Hero Page Blog & Chantiers en Direct",
                'description' => "Grande bannière en haut de la page des actualités et conseils d'ingénierie.",
                'image_url' => 'images/IMG-20261029-WA0103.jpg',
                'default_url' => 'images/IMG-20261029-WA0103.jpg',
                'focal_point' => 'center center',
                'sort_order' => 510
            ]
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO page_visuals 
            (page_slug, section_key, section_name, title, description, image_url, default_url, focal_point, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($defaults as $d) {
            $stmt->execute([
                $d['page_slug'],
                $d['section_key'],
                $d['section_name'],
                $d['title'],
                $d['description'],
                $d['image_url'],
                $d['default_url'],
                $d['focal_point'],
                $d['sort_order']
            ]);
        }

        $initialized = true;
    } catch (Exception $e) {
        // En cas d'erreur de schéma, on ne bloque pas le site
    }
}

/**
 * Récupère tous les visuels d'une page donnée sous forme de tableau indexé par section_key
 */
function getPageVisuals($pdo, $pageSlug) {
    if (!$pdo) return [];
    initPageVisualsTable($pdo);
    static $cache = [];
    if (isset($cache[$pageSlug])) {
        return $cache[$pageSlug];
    }

    $visuals = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM page_visuals WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$pageSlug]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $visuals[$row['section_key']] = $row;
        }
    } catch (Exception $e) {}

    $cache[$pageSlug] = $visuals;
    return $visuals;
}

/**
 * Helper pratique pour récupérer l'URL et le cadrage d'un visuel
 */
function getVisualInfo($pageVisuals, $sectionKey, $defaultUrl = '', $defaultFocal = 'center top') {
    if (isset($pageVisuals[$sectionKey]) && !empty($pageVisuals[$sectionKey]['image_url'])) {
        return [
            'url' => $pageVisuals[$sectionKey]['image_url'],
            'focal' => $pageVisuals[$sectionKey]['focal_point'] ?: $defaultFocal,
            'title' => $pageVisuals[$sectionKey]['title'] ?? ''
        ];
    }
    return [
        'url' => $defaultUrl,
        'focal' => $defaultFocal,
        'title' => ''
    ];
}
