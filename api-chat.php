<?php
/**
 * API Chatbot Intelligent & Concierge BTP - FEE BTP SARL
 * Architecture Double-Cerveau :
 * 1. Moteur Génératif Cloud (Google Gemini AI) si clé configurée
 * 2. Moteur Sémantique Local Haute Densité (30+ scénarios BTP Cameroun / Diaspora / Ouest)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/db.php';

// Récupération des données POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = trim($input['action'] ?? 'message');

// Paramètres de configuration du site
$siteSettings = getSiteSettings($pdo);
$companyName = $siteSettings['company_name'] ?? 'RMC BTP SARL';
$phonePrimary = $siteSettings['phone_primary'] ?? '(+237) 697 39 84 55';
$phoneSecondary = $siteSettings['phone_secondary'] ?? '(+237) 677 00 00 00';
$whatsappNumber = preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number'] ?? '237697398455');
$emailContact = $siteSettings['email_contact'] ?? 'contact@rmcbtp.com';
$address = $siteSettings['address'] ?? 'Yaoundé & Douala, Cameroun';
$directorName = $siteSettings['manager_name'] ?? 'M. Rodrigue DJOMO';
$geminiApiKey = trim($siteSettings['gemini_api_key'] ?? '');
$aiProvider = trim($siteSettings['ai_provider'] ?? 'auto');
$aiCustomPrompt = trim($siteSettings['ai_system_prompt'] ?? '');

/**
 * 1. ACTION : Message d'accueil initial
 */
if ($action === 'get_welcome') {
    $response = [
        'status' => 'success',
        'reply' => "👋 **Bonjour !** Bienvenue chez " . htmlspecialchars($companyName) . ".\n\nJe suis votre conseiller en bâtiment et travaux publics. Posez-moi directement votre question ci-dessous, ou choisissez une option :",
        'suggestions' => [
            ['label' => '🏗️ Estimer mon projet', 'action' => 'flow_devis'],
            ['label' => '💬 Échanger sur WhatsApp', 'action' => 'contact_whatsapp']
        ]
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 2. ACTION : Sauvegarde du lead dans la base de données
 */
if ($action === 'save_lead') {
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $service = trim($input['service'] ?? 'Projet BTP');
    $location = trim($input['location'] ?? 'Partout au Cameroun');
    $details = trim($input['details'] ?? '');
    $chatHistory = trim($input['chat_history'] ?? '');

    if (empty($name) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Le nom et le numéro sont obligatoires.']);
        exit;
    }

    $subject = "Projet Chatbot : " . $service . " (" . $location . ")";
    $fullMessage = "=== DEMANDE QUALIFIÉE VIA LE CHATBOT INTELLIGENT ===\n";
    $fullMessage .= "Client : " . $name . "\n";
    $fullMessage .= "Téléphone / WhatsApp : " . $phone . "\n";
    $fullMessage .= "Service concerné : " . $service . "\n";
    $fullMessage .= "Localisation du chantier : " . $location . "\n";
    $fullMessage .= "Détails exprimés : " . $details . "\n\n";
    if (!empty($chatHistory)) {
        $fullMessage .= "--- HISTORIQUE DU DIALOGUE ---\n" . $chatHistory . "\n";
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (type, sender_name, sender_email, sender_phone, service_requested, subject, message, status)
            VALUES ('livechat', ?, '', ?, ?, ?, ?, 'unread')
        ");
        $stmt->execute([$name, $phone, $service, $subject, $fullMessage]);
        $leadId = $pdo->lastInsertId();

        $waText = "Bonjour RMC BTP SARL (%2AM. Rodrigue DJOMO%2A), je suis " . urlencode($name) . ".\n";
        $waText .= "J'ai configuré mon projet sur votre site web :\n";
        $waText .= "• %2AType de projet :%2A " . urlencode($service) . "\n";
        $waText .= "• %2ALocalisation :%2A " . urlencode($location) . "\n";
        if (!empty($details)) {
            $waText .= "• %2ADétails :%2A " . urlencode($details) . "\n";
        }
        $waText .= "• %2AMon contact :%2A " . urlencode($phone) . "\n";
        $waText .= "Pouvez-vous me recontacter pour échanger sur la faisabilité et le devis ?";

        $waLink = "https://wa.me/" . $whatsappNumber . "?text=" . $waText;

        echo json_encode([
            'status' => 'success',
            'lead_id' => $leadId,
            'reply' => "🎉 **Parfait " . htmlspecialchars($name) . " ! Vos informations ont bien été transmises à notre bureau d'études.**\n\nNos ingénieurs vous recontacteront sous 24h ouvrées au **" . htmlspecialchars($phone) . "**.",
            'whatsapp_url' => $waLink,
            'suggestions' => [
                ['label' => '👉 Continuer la discussion sur WhatsApp', 'url' => $waLink],
                ['label' => '🏗️ Poser une autre question', 'action' => 'reset_chat']
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur technique.']);
        exit;
    }
}

/**
 * 3. ANALYSE DU MESSAGE UTILISATEUR
 */
$userMsg = trim($input['message'] ?? '');
$chatHistory = trim($input['chat_history'] ?? '');

if (empty($userMsg)) {
    echo json_encode([
        'status' => 'success',
        'reply' => "Je suis à votre écoute ! Que souhaitez-vous savoir concernant nos travaux ou votre projet de construction ?",
        'suggestions' => [['label' => '🏗️ Lancer mon devis express', 'action' => 'flow_devis']]
    ]);
    exit;
}

/**
 * TENTATIVE D'APPEL GOOGLE GEMINI AI (Si configuré)
 */
function callGeminiAI($apiKey, $userMessage, $history, $companyName, $directorName, $phone, $address, $customPrompt) {
    if (empty($apiKey)) return null;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=" . urlencode($apiKey);

    $systemInstruction = "Tu es l'ingénieur-conseil virtuel de {$companyName} ({$address}).
Histoire & Envergure de l'entreprise :
- Créée en 2017 par ses cofondateurs (Chirac, Makair et Rodrigue).
- Équipe d'ingénierie et bureau d'études dirigé sur le terrain par nos ingénieurs en génie civil et conducteurs de travaux certifiés.
- Slogan officiel : 'Construire aujourd\'hui les solutions de demain.'
- Bilan & Chiffres clés : Plus de 700 travaux réalisés au Cameroun, 52 travaux à l'international, réseau actif dans plus de 64 pays partenaires.
- Pôles d'activités intégrés :
  • RMC BTP : Construction clés en main, gros œuvre, dalles béton, architecture, génie civil, VRD.
  • SIMS QUINCAILLERIE : Matériaux de gros œuvre (ciments certifiés, fers à béton) et outillage professionnel sans intermédiaire.
  • SIMS DECOR : Finitions de prestige (faux plafonds staff, luminaires LED, carrelages et marbres, peinture haut standing).
Téléphone & WhatsApp officiel : {$phone}.

RÈGLES STRICTES DE RÉPONSE :
1. RÉPONSES DIRECTES ET FINALES UNIQUEMENT : Ne JAMAIS afficher de réflexion interne, d'anglais, de brouillon ou d'étapes de rédaction. Réponds DIRECTEMENT en français impeccable au client.
2. RECRUTEMENT / CANDIDATURE : Si l'utilisateur cherche du travail, un emploi, un stage ou souhaite postuler en tant qu'ingénieur, technicien ou ouvrier qualifié, accueille-le chaleureusement et invite-le à transmettre son CV et ses références directement sur WhatsApp ({$phone}) ou par email.
3. DONNER TOUJOURS UNE ESTIMATION CHIFFRÉE QUAND DEMANDÉ : Si le client demande un prix, une estimation ou un budget (ex: villa, 400m², dalle), donne TOUJOURS une fourchette réaliste en Francs CFA (FCFA) basée sur le marché camerounais :
   - Gros œuvre seul : ~90 000 à 130 000 FCFA / m²
   - Clé en main standard : ~160 000 à 210 000 FCFA / m²
   - Haut standing (avec finitions SIMS Décor) : ~250 000 à 350 000 FCFA / m²
   Explique avec pédagogie ce que cela comprend (fondations, béton armé vibré, finitions).
4. PAS DE RÉPONSES TRONQUÉES : Termine toujours tes phrases et tes paragraphes proprement.
5. SOUPLESSE GÉOGRAPHIQUE : RMC BTP intervient à Douala, Yaoundé, Dschang, Bafoussam, Kribi, Garoua, Maroua et partout au Cameroun.
6. RÈGLE CRUCIALE SUR LES NOMS ET COORDONNÉES :
   - NE MENTIONNE AUCUN NOM PROPRE DE PERSONNE (pas de nom de famille ou prénom). Dis simplement 'l'ingénieur', 'notre ingénieur référent' ou 'la Direction Technique'.
   - NE DONNE PAS les numéros de téléphone ou de WhatsApp à chaque message.
   - Fournis le numéro ({$phone}) UNIQUEMENT si le visiteur demande expressément le contact (ex: 'quel est votre contact', 'donne moi votre numéro', 'votre whatsapp', 'comment vous appeler'). Quand il le demande, dis simplement : 'Un ingénieur va vous répondre directement au {$phone} (appel et WhatsApp).' sans citer aucun nom propre.
   - Ne conclus jamais par une invitation forcée à contacter WhatsApp. Conclus naturellement et simplement tes explications techniques en demandant par exemple si le visiteur a une précision sur son terrain, sa ville ou la surface souhaitée.";

    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => "Contexte précédent :\n" . $history . "\n\nQuestion du client : " . $userMessage]
                ]
            ]
        ],
        "system_instruction" => [
            "parts" => [
                ["text" => $systemInstruction]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.65,
            "maxOutputTokens" => 1200
        ]
    ];

    $payloadJson = json_encode($payload);
    $models = ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-2.5-flash'];

    foreach ($models as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        // Sur Windows/XAMPP : PowerShell natif direct pour éviter le gel/timeout de PHP cURL
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $tempFile = tempnam(sys_get_temp_dir(), 'gem_in_');
            $tempOut = tempnam(sys_get_temp_dir(), 'gem_out_');
            file_put_contents($tempFile, $payloadJson);
            $psCmd = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "[Console]::OutputEncoding = [System.Text.Encoding]::UTF8; try { $res = Invoke-RestMethod -Uri \'' . $url . '\' -Method Post -InFile \'' . $tempFile . '\' -ContentType \'application/json\' -TimeoutSec 10; [System.IO.File]::WriteAllText(\'' . $tempOut . '\', $res.candidates[0].content.parts[0].text, [System.Text.Encoding]::UTF8) } catch { }"';
            @shell_exec($psCmd);
            @unlink($tempFile);
            if (file_exists($tempOut)) {
                $psOut = @file_get_contents($tempOut);
                @unlink($tempOut);
                if (!empty($psOut) && trim($psOut) !== '') {
                    return trim($psOut);
                }
            }
        } else {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $result) {
                $json = json_decode($result, true);
                if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($json['candidates'][0]['content']['parts'][0]['text']);
                }
            }
        }
    }

    return null;
}

// Essai du LLM Cloud si autorisé
$aiReply = null;
if (($aiProvider === 'auto' || $aiProvider === 'gemini') && !empty($geminiApiKey)) {
    $aiReply = callGeminiAI($geminiApiKey, $userMsg, $chatHistory, $companyName, $directorName, $phonePrimary, $address, $aiCustomPrompt);
}

if ($aiReply) {
    echo json_encode([
        'status' => 'success',
        'reply' => $aiReply,
        'suggestions' => [
            ['label' => '🏗️ Configurer mon devis express', 'action' => 'flow_devis'],
            ['label' => '💬 Échanger sur WhatsApp avec l\'ingénieur', 'action' => 'contact_whatsapp']
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 4. MOTEUR SÉMANTIQUE LOCAL HAUTE DENSITÉ (GRAPHE DE CONNAISSANCES BTP CAMEROUN)
 */
$cleanMsg = mb_strtolower($userMsg, 'UTF-8');

function hasWord($text, array $words) {
    foreach ($words as $w) {
        $w = trim($w);
        if (empty($w)) continue;
        if (mb_strlen($w, 'UTF-8') <= 4) {
            if (preg_match('/(?:\b|[\s\',.!?])' . preg_quote($w, '/') . '(?:\b|[\s\',.!?])/iu', ' ' . $text . ' ')) {
                return true;
            }
        } else {
            if (mb_strpos($text, $w, 0, 'UTF-8') !== false) {
                return true;
            }
        }
    }
    return false;
}

$reply = "";
$suggestions = [];

// 0. Recrutement / Emploi / Recherche de Travail / Stage / Candidature
if (hasWord($cleanMsg, [
    'travail', 'emploi', 'cherche travail', 'cherche du travail', 'cherche un travail', 
    'cherche un emploi', 'cherche emploi', 'job', 'recrutement', 'recruter', 'recrutez', 
    'embauche', 'embaucher', 'postuler', 'candidature', 'cv', 'stage', 'stagiaire', 
    'apporter mon experience', 'rejoindre votre equipe', 'rejoindre l\'equipe', 
    'rejoindre l\'entreprise', 'offre demploi', 'offre d\'emploi', 'cherche de travail', 
    'recherche travail', 'recherche emploi', 'cherche un poste', 'recrutez-vous', 
    'travailler avec vous', 'travailler chez vous', 'trouver du travail', 'boulot'
])) {
    // Détecter si la mention de diplôme ou génie civil est sous forme négative ("pas de...", "sans...", "aucune...")
    $hasNegatedQualifications = preg_match('/(?:pas\s+de|sans|aucun|aucune|pas\s+fait|jamais\s+fait|non\s+qualifi[eé]|sans\s+formation)\s+(?:qualification|dipl[oô]me|exp[eé]rience|formation|comp[eé]tence)?\s*(?:en|de)?\s*(?:g[eé]nie\s+civil|btp|b[aâ]timent|ing[eé]nierie)?/iu', $cleanMsg);

    // 0.A - Profil terrain, débutant, manœuvre, sans qualification académique en génie civil
    $isNoQualificationOrOuvrier = $hasNegatedQualifications || hasWord($cleanMsg, [
        'pas de qualification', 'sans qualification', 'aucune qualification', 'pas de diplome', 
        'sans diplome', 'aucun diplome', 'pas d\'experience', 'sans experience', 'debutant', 
        'débutant', 'manoeuvre', 'manœuvre', 'aide', 'aide-chantier', 'aide macon', 'aide maçon', 
        'ouvrier', 'sur le tas', 'apprendre', 'volontaire', 'journalier', 'manutention', 
        'bras valide', 'chauffeur', 'gardien', 'ferrailleur', 'macon', 'maçon', 'carreleur'
    ]);

    // 0.B - Profil technique diplômé / Cadre d'ingénierie (uniquement s'il n'a pas affirmé ne pas l'être)
    $isTechnicalCadre = !$hasNegatedQualifications && hasWord($cleanMsg, [
        'ingenieur', 'ingénieur', 'genie civil', 'génie civil', 'conducteur de travaux', 
        'metreur', 'métreur', 'topographe', 'architecte', 'technicien superieur', 
        'bts btp', 'dut', 'licence pro', 'master btp', 'calcul de structure', 'autocad'
    ]);

    if ($isNoQualificationOrOuvrier && !$isTechnicalCadre) {
        $reply = "💪 **Opportunités de Terrain & Équipes Chantier chez " . htmlspecialchars($companyName) . " :**\n\n"
               . "Sur nos chantiers, la rigueur de terrain, le courage et la discipline sont tout aussi essentiels que les diplômes universitaires ! Nous recrutons régulièrement des **manœuvres, aides-maçons, agents de manutention et jeunes motivés** désireux d'apprendre les métiers du bâtiment sur le terrain.\n\n"
               . "Ce qui compte avant tout pour nos équipes :\n"
               . "• **La ponctualité et le respect des consignes de sécurité** (port des EPI sur site).\n"
               . "• **L'honnêteté et l'esprit d'équipe**.\n"
               . "• **La volonté d'apprendre** aux côtés de nos maîtres-bâtisseurs.\n\n"
               . "📲 **Comment postuler directement :**\n"
               . "Envoyez simplement un message court ou une note vocale sur WhatsApp au **" . htmlspecialchars($phonePrimary) . "** en précisant :\n"
               . "1. Votre Nom et Prénom\n"
               . "2. Votre ville et quartier de résidence (ex: Yaoundé / Douala)\n"
               . "3. Votre disponibilité (immédiate ou partielle) et toute tâche manuelle déjà pratiquée.";
        $suggestions = [
            ['label' => '💬 Écrire au responsable de chantier sur WhatsApp', 'action' => 'contact_whatsapp'],
            ['label' => '📍 Localiser nos chantiers actuels', 'action' => 'quick_services'],
            ['label' => '🏗️ Découvrir nos projets réalisés', 'action' => 'quick_services']
        ];
    } elseif ($isTechnicalCadre) {
        $reply = "📐 **Recrutement Cadres Techniques & Ingénierie chez " . htmlspecialchars($companyName) . " :**\n\n"
               . "Notre bureau d'études et notre direction technique accueillent avec grand intérêt les profils d'excellence en génie civil et conduite de projets (Ingénieurs BTP, Conducteurs de travaux, Métreurs, Projeteurs et Topographes).\n\n"
               . "📋 **Dossier de candidature attendu :**\n"
               . "• CV à jour détaillant vos chantiers supervisés et ouvrages réalisés\n"
               . "• Logiciels maîtrisés (AutoCAD, Robot Structural Analysis, Arche, MS Project, etc.)\n"
               . "• Références vérifiables\n\n"
               . "Transmettez votre dossier directement :\n"
               . "• **Par WhatsApp :** au bureau technique au **" . htmlspecialchars($phonePrimary) . "**\n"
               . "• **Par E-mail :** à **" . htmlspecialchars($emailContact) . "**";
        $suggestions = [
            ['label' => '📄 Transmettre mon CV sur WhatsApp', 'action' => 'contact_whatsapp'],
            ['label' => '✉️ Envoyer mon dossier par e-mail', 'action' => 'contact_whatsapp'],
            ['label' => '🏗️ Consulter nos réalisations d\'ingénierie', 'action' => 'quick_services']
        ];
    } else {
        $reply = "🤝 **Recrutement & Carrières chez " . htmlspecialchars($companyName) . " :**\n\n"
               . "Nous recrutons à la fois sur le terrain et au bureau technique :\n\n"
               . "1. **Équipes de terrain & Chantiers :** Manœuvres, aides-maçons, coffreurs, ferrailleurs, chauffeurs et profils volontaires motivés (formation pratique sur site).\n"
               . "2. **Cadres & Techniciens BTP :** Ingénieurs génie civil, conducteurs de travaux, métreurs et topographes avec références.\n\n"
               . "📲 **Pour postuler :**\n"
               . "Contactez directement notre bureau par WhatsApp au **" . htmlspecialchars($phonePrimary) . "** ou par email à **" . htmlspecialchars($emailContact) . "** en indiquant le poste visé et votre disponibilité.";
        $suggestions = [
            ['label' => '💬 Postuler directement sur WhatsApp', 'action' => 'contact_whatsapp'],
            ['label' => '📄 Transmettre mon CV', 'action' => 'contact_whatsapp'],
            ['label' => '🏗️ Découvrir nos chantiers en cours', 'action' => 'quick_services']
        ];
    }
}

// 1. Diaspora & Suivi de chantier à distance
elseif (hasWord($cleanMsg, ['diaspora', 'etranger', 'étranger', 'france', 'europe', 'canada', 'usa', 'etats-unis', 'belgique', 'angleterre', 'allemagne', 'distance', 'confiance', 'escroquerie', 'famille', 'deception'])) {
    $reply = "🌍 **Gestion de Chantier Spéciale Diaspora chez RMC BTP SARL :**\n\nConstruire au pays depuis l'étranger est un investissement précieux qui exige une confiance absolue. Pour vous garantir une sérénité totale, nous appliquons un protocole rigoureux :\n• **Contrat d'entreprise formel** et facturation transparente jalonnée par étapes validées.\n• **Rapports hebdomadaires en direct** : vidéos HD, photos détaillées et appels vidéo WhatsApp sur site.\n• **Paiements échelonnés** : vous ne débloquez les fonds qu'après constatation visuelle de l'achèvement de chaque phase (fondations, élévation, dalle, toiture, finitions SIMS Décor).\n• Zéro intermédiaire familial pour éviter tout détournement de matériaux.";
    $suggestions = [
        ['label' => '📄 Demander un contrat type & devis diaspora', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger en direct avec la Direction RMC BTP', 'action' => 'contact_whatsapp']
    ];
}

// 2. Marécage, terrain inondable, radier, sol mou
elseif (hasWord($cleanMsg, ['marécage', 'marecage', 'inondable', 'boue', 'sol mou', 'sol humide', 'radier', 'pieux', 'nappe phréatique', 'remblai vaseux'])) {
    $reply = "💧 **Construction sur Sol Marécageux ou Inondable :**\n\nBâtir sur un sol instable ou humide ne s'improvise pas au Cameroun :\n• **Étude géotechnique préalable** : mesure de la profondeur du sol portant ferme.\n• **Solutions structurelles** : décapage de la vase, remblai en tout-venant compacté, et réalisation soit d'un **radier général nervuré**, soit de **longrines renforcées sur puits/pieux béton**.\n• **Étanchéité renforcée** : feutre bitumineux sous dallage et hydrofuge de masse dans le béton pour stopper toute remontée capillaire dans les murs.";
    $suggestions = [
        ['label' => '👷 Demander un diagnostic de terrain marécageux', 'action' => 'flow_devis'],
        ['label' => '💬 Poser ma question technique sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 3. Fosse septique écologique, bio-digesteur, assainissement autonome
elseif (hasWord($cleanMsg, ['fosse', 'septique', 'bio', 'écologique', 'ecologique', 'puisard', 'vidange', 'wc', 'toilette', 'egout', 'égout', 'eaux usees', 'eaux usées', 'assainissement'])) {
    $reply = "🌱 **Assainissement Moderne & Fosses Septiques Écologiques :**\n\nRMC BTP SARL conçoit des systèmes d'assainissement autonomes et durables :\n• **Fosse bio-digesteur / écologique** : traitement biologique des effluents par micro-organismes, sans odeurs nauséabondes et sans vidange pendant 15 à 25 ans.\n• **Fosse septique toutes eaux classique en béton armé** : compartiments de décantation étanches couplés à un **puisard filtrant (lit de gravier et sable)**.\n• Dimensionnement adapté au nombre d'usagers (villa familiale, immeuble de studios ou établissement commercial).";
    $suggestions = [
        ['label' => '📐 Devis pour installation de fosse autonome', 'action' => 'flow_devis'],
        ['label' => '💬 Consulter nos plans d\'assainissement sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 4. Murs de soutènement, talus, pente, stabilisation
elseif (hasWord($cleanMsg, ['soutènement', 'soutenement', 'pente', 'talus', 'glissement', 'eboulement', 'éboulement', 'ravin', 'terrassement', 'colline', 'relief', 'laterite', 'latérite', 'moellon'])) {
    $reply = "⛰️ **Murs de Soutènement, Terrains en Pente & Stabilisation :**\n\nLes terrains accidentés ou en fort dénivelé requièrent une ingénierie géotechnique de précision :\n• **Murs en béton armé banché encastrés (voiles)** : calculés pour retenir les poussées de terre et sécuriser vos constructions.\n• **Murs poids en moellons de carrière maçonnés** : robustes, durables et drainants pour stabiliser les talus.\n• **Système de drainage intégré** : pose de barbacanes d'évacuation et caniveaux pour dissiper la pression de l'eau en saison des pluies.";
    $suggestions = [
        ['label' => '📅 Programmer une visite de terrain en pente', 'action' => 'flow_devis'],
        ['label' => '💬 Envoyer la photo de mon talus sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 5. Dosages de béton, ciment, sable, gravier
elseif (hasWord($cleanMsg, ['dosage', 'ciment', 'sac', 'm3', 'mètre cube', 'metre cube', '350', '400', 'sable', 'gravier', 'dosage beton', 'dosage béton', 'brouette'])) {
    $reply = "🧱 **Dosages de Béton Conformes aux Règles de l'Art :**\n\nChez RMC BTP SARL, nous bannissons les sous-dosages :\n• **Béton armé de structure (Poteaux, Poutres, Dalle)** : dosé rigoureusement à **350 kg/m³** (soit environ 7 sacs de ciment CPJ 35 ou 42.5 pour 400 litres de sable propre et 800 litres de gravier concassé).\n• **Béton pour fondations spéciales & ouvrages d'art** : dosé à **400 kg/m³**.\n• **Béton de propreté** : dosé à **150 à 200 kg/m³** sous les semelles.\n• Matériaux d'approvisionnement direct via SIMS Quincaillerie et vibreurs thermiques pour éliminer les bulles d'air.";
    $suggestions = [
        ['label' => '🏗️ Lancer un chiffrage quantitatif (DQE)', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger sur les matériaux avec l\'ingénieur', 'action' => 'contact_whatsapp']
    ];
}

// 6. Fers à béton, armatures, diamètres
elseif (hasWord($cleanMsg, ['fer', 'armature', 'ferraillage', 'haute adherence', 'fe400', 'fe500', 'diamètre', 'diametre', 'tor', 'etrier', 'étrier', 'cadre'])) {
    $reply = "⛓️ **Ferraillage & Normes Antisismiques :**\n\nLa solidité d'un bâtiment repose sur son armature métallique :\n• Nous utilisons exclusivement des fers **Haute Adhérence (HA - FeE400 ou FeE500)** certifiés.\n• Dimensionnement précis : armatures longitudinales (fers de 10, 12, 14 ou 16 selon la portée) et étriers resserrés (fers de 6 ou 8) aux nœuds poteaux-poutres.\n• Respect absolu de l'**enrobage du béton** (3 cm minimum) grâce à des cales à béton pour éviter la corrosion future des aciers.";
    $suggestions = [
        ['label' => '📐 Confier l\'étude de structure à RMC BTP', 'action' => 'flow_devis'],
        ['label' => '💬 Poser ma question sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 7. Agglos de 15 vs Agglos de 20
elseif (hasWord($cleanMsg, ['agglo', 'parpaing', '15', '20', 'cloison', 'mur porteur', 'brique', 'creux', 'plein'])) {
    $reply = "🧱 **Quelle différence entre Agglos de 15 et Agglos de 20 ?**\n\n• **Agglos de 20 (cm) :** Recommandés pour les murs extérieurs périphériques, murs de soubassement et façades. Ils offrent une isolation thermique et acoustique renforcée et une grande inertie.\n• **Agglos de 15 (cm) :** Idéaux pour les cloisons intérieures séparatives (chambres, salons, couloirs) et les étages supérieurs pour alléger la charge sur les dalles.\n• RMC BTP privilégie les agglos vibrés lourds à forte densité de ciment.";
    $suggestions = [
        ['label' => '⚡ Estimer la quantité pour mon projet', 'action' => 'flow_devis'],
        ['label' => '💬 Demander conseil sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 8. Toitures, bac alu, tôles, étanchéité
elseif (hasWord($cleanMsg, ['toiture', 'tôle', 'tole', 'bac alu', 'charpente', 'fuite', 'gouttiere', 'gouttière', 'infiltration', 'etancheite', 'étanchéité', 'chevron', 'panne'])) {
    $reply = "🏠 **Toitures Tropicales & Charpentes Résistantes :**\n\nÀ Yaoundé, Douala et sur l'ensemble du Cameroun, une couverture durable requiert :\n• **Bacs aluminium thermo-laqués** (épaisseurs recommandées : 5/10 ou 6/10) : inaltérables, légers et résistants à la rouille.\n• **Charpente en bois dur local traité** contre les insectes xylophages.\n• Pente adéquate pour un écoulement parfait sans rétention d'eau, et solins d'étanchéité étanches au niveau des acrotères.";
    $suggestions = [
        ['label' => '🛠️ Demander une rénovation ou pose de toiture', 'action' => 'flow_devis'],
        ['label' => '💬 Demander les tarifs bac alu sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 9. Permis de bâtir, démarches et autorisations
elseif (hasWord($cleanMsg, ['permis', 'bâtir', 'batir', 'urbanisme', 'titre foncier', 'mairie', 'mindhu', 'certificat', 'autorisation', 'dossier', 'legal', 'légal'])) {
    $reply = "📑 **Obtention du Permis de Bâtir & Urbanisme au Cameroun :**\n\nPour construire en toute légalité et éviter les arrêts de chantier municipaux :\n1. Constitution du dossier architectural : plan de masse, plan de situation, plans de niveaux, coupes et façades.\n2. Note de calculs de structure béton et devis estimatif.\n3. Certificat d'urbanisme et titre de propriété (titre foncier ou certificat de propriété).\n• **RMC BTP SARL monte l'intégralité de votre dossier technique** et facilite son dépôt auprès des services municipaux compétents.";
    $suggestions = [
        ['label' => '📑 Confier mon dossier de permis de bâtir', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger sur les démarches foncières', 'action' => 'contact_whatsapp']
    ];
}

// 10. Délais, temps de séchage, durée de chantier
elseif (hasWord($cleanMsg, ['delai', 'délai', 'combien de temps', 'temps de séchage', 'duree', 'durée', 'sechage', 'séchage', 'decoffrage', 'décoffrage', 'calendrier'])) {
    $reply = "⏱️ **Délais d'Exécution & Temps de Séchage Normatifs :**\n\n• **Villa plain-pied (120 à 160 m²)** : 2 à 3 mois pour le gros œuvre complet.\n• **Duplex R+1 (200 à 300 m²)** : 4 à 5 mois pour la structure hors d'eau.\n• **Règle absolue de sécurité BTP** : respect strict des **21 à 28 jours de séchage** pour le décoffrage des dalles et poutres maîtresses en béton armé.\nRMC BTP planifie des rétroplannings rigoureux pour respecter les dates de livraison convenues.";
    $suggestions = [
        ['label' => '📅 Planifier mon calendrier de travaux', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger sur les délais avec l\'ingénieur', 'action' => 'contact_whatsapp']
    ];
}

// 11. Devis, prix, coûts, combien coûte une maison
elseif (hasWord($cleanMsg, ['devis', 'prix', 'cout', 'coût', 'combien', 'tarif', 'm2', 'mètre carré', 'metre carre', 'estimation', 'chiffrage', 'budget', 'dqe'])) {
    $reply = "📊 **Comment est calculé le coût d'une construction ?**\n\nAu Cameroun, estimer au 'hasard' est le plus grand piège. Le coût réel dépend de :\n1. **Gros œuvre seul** (fondations, élévation, dalle, toiture) : entre 130 000 et 180 000 FCFA/m² selon la topographie.\n2. **Clé en main (Finitions complètes)** : de 230 000 à 380 000 FCFA/m² selon le standing (carrelage grand format, baies vitrées, staff, sanitaires).\n\n👉 Nous établissons des **Devis Quantitatifs et Estimatifs (DQE)** ligne par ligne, avec chaque sac de ciment, tonne de fer et journée de travail chiffrée sans surprise.";
    $suggestions = [
        ['label' => '⚡ Démarrer mon devis express gratuit', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger sur mon budget sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 12. Historique, Fondateurs, Vision & Chiffres Clés RMC BTP
elseif (hasWord($cleanMsg, ['histoire', 'historique', 'fondateur', 'fondateurs', 'creation', 'création', 'cree', 'créé', 'chirac', 'makair', 'marcair', 'vision', 'chiffre', 'chiffres'])) {
    $reply = "🏛️ **Histoire & Envergure de RMC BTP SARL :**\n\n• **Fondation :** Créée en **2017** par ses trois cofondateurs — **Chirac, Makair et Rodrigue** — unis par une vision d'innovation et de rigueur constructive.\n• **Bilan d'excellence :** Plus de **700 chantiers réalisés** avec succès à Yaoundé, au Cameroun et à l'étranger.\n• **Rayonnement :** **52 réalisations à l'international** et un réseau actif de partenaires dans plus de **64 pays**.\n• **Devise :** *« Construire aujourd'hui les solutions de demain. »*";
    $suggestions = [
        ['label' => '👥 Découvrir nos fondateurs', 'action' => 'flow_devis'],
        ['label' => '🏗️ Lancer une estimation de projet', 'action' => 'flow_devis']
    ];
}

// 12b. Équipe & Direction Technique
elseif (hasWord($cleanMsg, ['equipe', 'équipe', 'staff', 'technique', 'ingenieur', 'ingénieur', 'bureau detude', 'bureau d\'étude'])) {
    $reply = "👷 **Direction Technique & Bureau d'Études RMC BTP :**\n\nSur le terrain, nos calculs et nos chantiers sont encadrés par des professionnels certifiés sous la supervision de l'**Ingénieur** :\n• Dimensionnement géotechnique des fondations et descentes de charges.\n• Respect absolu du béton armé dosé à 350 kg/m³ vibré et armatures haute adhérence.\n• Contrôle qualité continu et respect rigoureux des rétroplannings d'exécution.";
    $suggestions = [
        ['label' => '📐 Confier l\'étude de mes plans', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger avec l\'ingénieur sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 13. Visites de terrain & RDV (Yaoundé & Douala)
elseif (hasWord($cleanMsg, ['visite', 'visiter', 'terrain', 'rdv', 'rendez-vous', 'rencontrer', 'venir', 'parcelle', 'voir'])) {
    $reply = "📍 **Visite Technique de Terrain à Yaoundé, Douala & Régions :**\n\nNos ingénieurs se déplacent directement sur votre parcelle pour analyser :\n• La nature du sol et le relevé altimétrique (pente, marécage, portance).\n• L'accès des camions pour l'approvisionnement en matériaux via SIMS Quincaillerie.\n• L'orientation bioclimatique optimale du futur bâtiment.\n\nSouhaitez-vous planifier une visite sur votre terrain cette semaine ?";
    $suggestions = [
        ['label' => '📅 Réserver ma visite de terrain', 'action' => 'flow_devis'],
        ['label' => '💬 Caler la date sur WhatsApp (' . $phonePrimary . ')', 'action' => 'contact_whatsapp']
    ];
}

// 14. Plans & Architecture 3D
elseif (hasWord($cleanMsg, ['plan', '3d', 'architecte', 'architecture', 'dessin', 'conception', 'croquis', 'facade', 'façade'])) {
    $reply = "📐 **Conception de Plans & Modélisation 3D Tropicale :**\n\nNous concevons vos plans sur-mesure avant de poser le premier parpaing :\n• Plans 2D détaillés : distribution optimale des pièces, aération naturelle, intimité parentale.\n• Visuels 3D photoréalistes extérieurs et intérieurs pour visualiser votre future maison sous tous les angles.\n• Plans d'électricité encastrée, plomberie et fosse septique.";
    $suggestions = [
        ['label' => '📑 Demander la conception de mes plans', 'action' => 'flow_devis'],
        ['label' => '💬 Voir des modèles de plans sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 15. Rénovation, carrelage, peinture, finitions
elseif (hasWord($cleanMsg, ['renovation', 'rénovation', 'carrelage', 'peinture', 'staff', 'finition', 'faience', 'faïence', 'baie', 'plomberie'])) {
    $reply = "🎨 **Second Œuvre & Rénovation de Standing :**\n\nSublimez votre bâtiment existant ou terminez vos travaux inachevés :\n• Pose de grès cérame et faïence grand format avec alignement laser impeccable.\n• Faux-plafonds en staff décoratif avec gorges lumineuses et spots LED encastrés.\n• Peintures professionnelles hydrofuges, anti-moisissures et finitions haut de gamme (partenariat SIMS Décor).\n• Rénovation complète de plomberie et circuits électriques.";
    $suggestions = [
        ['label' => '🛠️ Estimer mon projet de rénovation', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger sur les finitions sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 16. Engins & Matériel
elseif (hasWord($cleanMsg, ['engin', 'materiel', 'matériel', 'pelleteuse', 'camion', 'benne', 'betonniere', 'bétonnière', 'compacteur', 'echafaudage', 'échafaudage'])) {
    $reply = "🚜 **Parc Matériel & Équipements RMC BTP SARL :**\n\nNous mobilisons nos propres moyens logistiques pour éviter toute dépendance ou retard :\n• Flotte d'engins lourds pour terrassement et fouilles à Yaoundé et Douala.\n• Camions d'approvisionnement direct pour le sable de Sanaga et gravier concassé.\n• Centrales et bétonnières de chantier à haut rendement avec aiguilles vibrantes.\n• Échafaudages tubulaires sécurisés pour façades et élévations.";
    $suggestions = [
        ['label' => '🏗️ Lancer mon devis travaux', 'action' => 'flow_devis'],
        ['label' => '💬 Poser ma question sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 17. Localisation, siège social, villes
elseif (hasWord($cleanMsg, ['où', 'ou etes', 'où êtes', 'localisation', 'situe', 'situé', 'adresse', 'bureau', 'siege', 'siège', 'yaoundé', 'yaounde', 'douala'])) {
    $reply = "📍 **Bureaux & Pôles d'Intervention :**\n\n• **Siège Social & Showroom :** " . htmlspecialchars($address) . ".\n• **Pôles d'intervention prioritaires :** Yaoundé, Douala, Kribi et l'ensemble des dix régions du Cameroun.\n• Pôles partenaires : SIMS Quincaillerie et SIMS Décor.";
    $suggestions = [
        ['label' => '📅 Prendre rendez-vous au bureau', 'action' => 'flow_devis'],
        ['label' => '💬 Échanger avec l\'ingénieur sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

// 18. Contact, téléphone, WhatsApp direct
elseif (hasWord($cleanMsg, ['contact', 'telephone', 'téléphone', 'numero', 'numéro', 'appel', 'appeler', 'whatsapp', 'horaire', 'mail', 'email'])) {
    $reply = "📞 **Contact RMC BTP SARL :**\n\nUn ingénieur va vous répondre directement pour échanger sur vos plans ou votre projet :\n• **Téléphone & WhatsApp :** " . htmlspecialchars($phonePrimary) . "\n• **Ligne secondaire :** " . htmlspecialchars($phoneSecondary) . "\n• **E-mail :** " . htmlspecialchars($emailContact);
    $suggestions = [
        ['label' => '💬 Échanger avec l\'ingénieur sur WhatsApp', 'action' => 'contact_whatsapp'],
        ['label' => '📞 Appeler le ' . $phonePrimary, 'action' => 'contact_call']
    ];
}

// 19. Salutations / Politesse
elseif (hasWord($cleanMsg, ['bonjour', 'salut', 'bonsoir', 'coucou', 'hello', 'allo', 'hey', 'bjr', 'bsr', 'merci', 'super', 'merci beaucoup', 'ok', 'daccord', 'd\'accord'])) {
    $reply = "C'est un plaisir de vous renseigner ! Chez **" . htmlspecialchars($companyName) . "**, nous sommes à votre disposition pour concrétiser vos projets de construction avec le plus haut niveau d'exigence.\n\nQue prévoyez-vous comme travaux prochainement ?";
    $suggestions = [
        ['label' => '🏗️ Estimer mon projet (Devis Express)', 'action' => 'flow_devis'],
        ['label' => '🌍 Projet Diaspora', 'action' => 'ask_diaspora'],
        ['label' => '💬 Échanger sur WhatsApp avec l\'ingénieur', 'action' => 'contact_whatsapp']
    ];
}

// 20. Question générale ou cas non répertorié : Analyse Contextuelle Intelligente
else {
    $reply = "C'est une excellente question pour votre projet de construction.\n\nPour vous donner une réponse technique parfaitement adaptée (selon la superficie, l'emplacement de votre terrain et vos matériaux), vous pouvez configurer rapidement une estimation détaillée en ligne ou nous préciser les détails de vos travaux.";
    $suggestions = [
        ['label' => '🏗️ Lancer le devis express pas-à-pas', 'action' => 'flow_devis'],
        ['label' => '💬 Poser ma question spécifique sur WhatsApp', 'action' => 'contact_whatsapp']
    ];
}

echo json_encode([
    'status' => 'success',
    'reply' => $reply,
    'suggestions' => $suggestions
], JSON_UNESCAPED_UNICODE);
exit;
