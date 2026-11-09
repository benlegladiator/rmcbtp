<?php
/**
 * Gestionnaire de Visuels de Repère par Page & Section - RMC BTP SARL
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

        // Liste des visuels de repère fondamentaux du site
        $defaults = [
            // --- PAGE D'ACCUEIL (index.php) ---
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
                'section_name' => "Section B : Écosystème Intégré 360°",
                'title' => "Pilier 1 : RMC BTP SARL (Génie Civil & Gros Œuvre)",
                'description' => "Illustration de la filiale construction et structures en béton.",
                'image_url' => 'images/IMG-20261029-WA0099.jpg',
                'default_url' => 'images/IMG-20261029-WA0099.jpg',
                'focal_point' => 'center top',
                'sort_order' => 30
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ecosystem_quincaillerie',
                'section_name' => "Section B : Écosystème Intégré 360°",
                'title' => "Pilier 2 : SIMS Quincaillerie (Fourniture Directe Aciers & Ciments)",
                'description' => "Illustration de la filiale matériaux et quincaillerie.",
                'image_url' => 'images/IMG-20261029-WA0098.jpg',
                'default_url' => 'images/IMG-20261029-WA0098.jpg',
                'focal_point' => 'center top',
                'sort_order' => 40
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'ecosystem_decor',
                'section_name' => "Section B : Écosystème Intégré 360°",
                'title' => "Pilier 3 : SIMS Décor (Staff, Marbres & Finitions Haut Standing)",
                'description' => "Illustration de la filiale décoration et aménagement intérieur.",
                'image_url' => 'images/IMG-20261029-WA0097.jpg',
                'default_url' => 'images/IMG-20261029-WA0097.jpg',
                'focal_point' => 'center top',
                'sort_order' => 50
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'about_photo',
                'section_name' => "Section C : À Propos & Direction",
                'title' => "Photo Institutionnelle Présentation & Équipe",
                'description' => "Image de l'encart de présentation de l'entreprise.",
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

            // --- PAGE À PROPOS (about.php) ---
            [
                'page_slug' => 'about',
                'section_key' => 'about_banner',
                'section_name' => "En-tête de la Page À Propos",
                'title' => "Bannière Hero Page À Propos",
                'description' => "Grande bannière en haut de la page À Propos.",
                'image_url' => 'images/bg_1.jpg',
                'default_url' => 'images/bg_1.jpg',
                'focal_point' => 'center top',
                'sort_order' => 110
            ],
            [
                'page_slug' => 'about',
                'section_key' => 'about_presentation',
                'section_name' => "Présentation Historique",
                'title' => "Photo principale de l'Entreprise",
                'description' => "Photo illustrant l'histoire et les réalisations du groupe.",
                'image_url' => 'images/about.jpg',
                'default_url' => 'images/about.jpg',
                'focal_point' => 'center top',
                'sort_order' => 120
            ],

            // --- PAGE SERVICES (services.php) ---
            [
                'page_slug' => 'services',
                'section_key' => 'services_banner',
                'section_name' => "En-tête de la Page Services",
                'title' => "Bannière Hero Page Services",
                'description' => "Grande bannière en haut de la page Services.",
                'image_url' => 'images/bg_1.jpg',
                'default_url' => 'images/bg_1.jpg',
                'focal_point' => 'center top',
                'sort_order' => 210
            ],

            // --- PAGE PROJETS (project.php) ---
            [
                'page_slug' => 'projects',
                'section_key' => 'projects_banner',
                'section_name' => "En-tête du Portfolio de Chantiers",
                'title' => "Bannière Hero Page Projets & Réalisations",
                'description' => "Grande bannière en haut de la page Projets.",
                'image_url' => 'images/bg_1.jpg',
                'default_url' => 'images/bg_1.jpg',
                'focal_point' => 'center top',
                'sort_order' => 310
            ],

            // --- PAGE CONTACT (contact.php) ---
            [
                'page_slug' => 'contact',
                'section_key' => 'contact_banner',
                'section_name' => "En-tête de la Page Contact",
                'title' => "Bannière Hero Page Contact & Devis",
                'description' => "Grande bannière en haut de la page Contact.",
                'image_url' => 'images/bg_1.jpg',
                'default_url' => 'images/bg_1.jpg',
                'focal_point' => 'center top',
                'sort_order' => 410
            ]
        ];

        // Optimisation haute performance : si des visuels existent déjà, éviter les requêtes redondantes
        $checkExists = $pdo->query("SELECT 1 FROM page_visuals LIMIT 1");
        if ($checkExists && $checkExists->fetch()) {
            $initialized = true;
            return;
        }

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
