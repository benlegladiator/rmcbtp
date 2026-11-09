/**
 * RMC BTP SARL - Studio 3D BIM Architectural & Structural Viewer
 * Version Haute Précision : Ouvertures Réalistes, Baies Coulissantes, 
 * Porte d'Entrée Pivotante & Plan Filaire Orthogonal (Revit / AutoCAD CAD Edges)
 */

(function() {
  'use strict';

  let scene, camera, renderer, controls;
  let modelGroup, foundationGroup, rdcGroup, r1Group, roofGroup, blueprintLinesGroup;
  let isExploded = false;
  let areDoorsOpen = false;
  let currentMode = 'prestige'; // 'prestige', 'blueprint', 'concrete'
  let autoRotate = true;
  let animationFrameId;
  let interiorLight;

  // Objets interactifs mobiles (ouvertures)
  let doorPivot, slidingGlassPanel, balconyDoor;

  const targetPositions = {
    foundation: { normal: 0, exploded: -3.8 },
    rdc: { normal: 0, exploded: 0 },
    r1: { normal: 0, exploded: 3.8 },
    roof: { normal: 0, exploded: 7.6 }
  };

  function initBIM() {
    const container = document.getElementById('feeBimCanvasWrap');
    const canvas = document.getElementById('feeBim3DCanvas');
    if (!container || !canvas) return;

    if (typeof THREE === 'undefined') {
      console.warn('Three.js non chargé.');
      return;
    }

    const width = container.clientWidth;
    const height = container.clientHeight;

    // 1. Scène & Atmosphère architecturale lumineuse (Ciel Azur & Studio BTP)
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0c213d); // Bleu architectural profond lumineux
    scene.fog = new THREE.FogExp2(0x0c213d, 0.018);

    // 2. Caméra (Dézoom proportionné sur mobile pour que l'immeuble tienne entièrement dans le cadre)
    camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
    if (width <= 768) {
      camera.position.set(28, 22, 34);
    } else {
      camera.position.set(17, 13, 21);
    }

    // 3. Renderer WebGL
    renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      antialias: true,
      alpha: true,
      powerPreference: 'high-performance'
    });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // 4. Contrôles OrbitControls
    if (typeof THREE.OrbitControls !== 'undefined') {
      controls = new THREE.OrbitControls(camera, renderer.domElement);
      controls.enableDamping = true;
      controls.dampingFactor = 0.05;
      controls.maxPolarAngle = Math.PI / 2 + 0.05;
      controls.minDistance = 7;
      controls.maxDistance = 45;
      controls.target.set(0, 3.2, 0);
    }

    // 5. Éclairage Studio & Lumière du jour
    setupLights();

    // 6. Construction de la maquette détaillée avec ouvertures
    buildArchitecturalModel();

    // 7. Grille de sol architecturale
    buildGridFloor();

    // 8. Événements & Écouteurs
    window.addEventListener('resize', onWindowResize);
    setupInteractions();

    // 9. Boucle d'animation
    animate();
  }

  function setupLights() {
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
    scene.add(ambientLight);

    // Soleil principal BTP chaud
    const sunLight = new THREE.DirectionalLight(0xfff7ed, 1.4);
    sunLight.position.set(22, 28, 16);
    sunLight.castShadow = true;
    sunLight.shadow.mapSize.width = 1024;
    sunLight.shadow.mapSize.height = 1024;
    sunLight.shadow.camera.near = 0.5;
    sunLight.shadow.camera.far = 70;
    const d = 16;
    sunLight.shadow.camera.left = -d;
    sunLight.shadow.camera.right = d;
    sunLight.shadow.camera.top = d;
    sunLight.shadow.camera.bottom = -d;
    scene.add(sunLight);

    // Remplissage d'adoucissement naturel
    const fillLight = new THREE.DirectionalLight(0xe2e8f0, 0.45);
    fillLight.position.set(-18, 12, -15);
    scene.add(fillLight);

    // Éclairage d'ambiance intérieur (lumières du salon)
    interiorLight = new THREE.PointLight(0xfbbf24, 1.3, 24);
    interiorLight.position.set(0, 3.5, 0);
    scene.add(interiorLight);
  }

  function buildGridFloor() {
    // Grille de sol lumineuse : Axe Jaune Doré et Carroyage Bleu Azur
    const grid = new THREE.GridHelper(30, 30, 0xf59e0b, 0x1d4ed8);
    grid.position.y = -0.05;
    scene.add(grid);
  }

  function createMaterial(type, colorHex, opacity = 1, roughness = 0.4, metalness = 0.15) {
    if (type === 'glass') {
      return new THREE.MeshPhysicalMaterial({
        color: 0x38bdf8, // Bleu Ciel Azur Cristallin
        transmission: 0.85,
        opacity: 0.6,
        transparent: true,
        roughness: 0.08,
        metalness: 0.1,
        reflectivity: 0.95,
        clearcoat: 1.0,
        clearcoatRoughness: 0.1
      });
    }

    return new THREE.MeshStandardMaterial({
      color: colorHex,
      roughness: roughness,
      metalness: metalness,
      transparent: opacity < 1,
      opacity: opacity
    });
  }

  function addMeshWithEdges(group, geo, mat, isOpening = false) {
    const clonedMat = mat.clone();
    const mesh = new THREE.Mesh(geo, clonedMat);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    mesh.userData.origColor = clonedMat.color ? clonedMat.color.getHex() : 0xffffff;
    mesh.userData.origOpacity = clonedMat.opacity;
    mesh.userData.origTransparent = clonedMat.transparent;
    mesh.userData.origRoughness = clonedMat.roughness;
    mesh.userData.origMetalness = clonedMat.metalness;
    mesh.userData.isGlass = (clonedMat.transmission > 0 || clonedMat.transparent);
    mesh.userData.isOpening = isOpening;

    // Arêtes filaires CAO : Jaune Or pour les ouvertures & Bleu Azur pour la structure
    const edgesGeo = new THREE.EdgesGeometry(geo, 15);
    const edgesMat = new THREE.LineBasicMaterial({
      color: isOpening ? 0xfbbf24 : 0x38bdf8,
      linewidth: isOpening ? 2 : 1,
      transparent: true,
      opacity: 0.95
    });
    const edgeLines = new THREE.LineSegments(edgesGeo, edgesMat);
    edgeLines.visible = false;
    mesh.add(edgeLines);
    mesh.userData.edgeLines = edgeLines;

    group.add(mesh);
    return mesh;
  }

  function buildArchitecturalModel() {
    modelGroup = new THREE.Group();
    scene.add(modelGroup);

    foundationGroup = new THREE.Group();
    rdcGroup = new THREE.Group();
    r1Group = new THREE.Group();
    roofGroup = new THREE.Group();

    modelGroup.add(foundationGroup);
    modelGroup.add(rdcGroup);
    modelGroup.add(r1Group);
    modelGroup.add(roofGroup);

    // =========================================================================
    // MATÉRIAUX LUMINEUX HAUT DE GAMME : MARRON NOBLE, JAUNE OR & BLEU AZUR
    // =========================================================================
    const matConcrete = createMaterial('solid', 0x94a3b8, 1, 0.85, 0.05); // Béton armé clair
    const matRebar = createMaterial('solid', 0xf59e0b, 1, 0.35, 0.7); // Acier HA Jaune Doré BTP
    const matWhiteWall = createMaterial('solid', 0xfffef7, 1, 0.45, 0.05); // Murs Blanc Solaire Lumineux
    const matBrownPillar = createMaterial('solid', 0x78350f, 1, 0.5, 0.15); // Piliers Marron Teck / Pierre de taille
    const matBrownWood = createMaterial('solid', 0x92400e, 1, 0.4, 0.1); // Bois teck noble chaleureux
    const matGoldYellow = createMaterial('solid', 0xf59e0b, 1, 0.25, 0.85); // Jaune Or métallique brossé
    const matAzureFrame = createMaterial('solid', 0x2563eb, 1, 0.28, 0.65); // Menuiseries Bleu Saphir / Cobalt
    const matNavyBlue = createMaterial('solid', 0x1e3a8a, 1, 0.35, 0.4); // Acrotères & bandeaux Bleu Royal
    const matPergola = createMaterial('solid', 0xa16207, 1, 0.35, 0.15); // Pergola Marron Chêne Doré
    const matGlass = createMaterial('glass', 0x38bdf8); // Vitrage Bleu Ciel Azur

    // =========================================================================
    // NIVEAU 0 : FONDATIONS GÉOTECHNIQUES & SEMELLES BÉTON ARMÉ
    // =========================================================================
    const slabGeo = new THREE.BoxGeometry(10.2, 0.6, 8.2);
    const foundationSlab = addMeshWithEdges(foundationGroup, slabGeo, matConcrete);
    foundationSlab.position.set(0, 0.3, 0);

    const footingPositions = [
      [-4.2, -0.6, -3.2], [0, -0.6, -3.2], [4.2, -0.6, -3.2],
      [-4.2, -0.6, 3.2],  [0, -0.6, 3.2],  [4.2, -0.6, 3.2]
    ];
    footingPositions.forEach(pos => {
      const footingGeo = new THREE.BoxGeometry(1.6, 0.8, 1.6);
      const footing = addMeshWithEdges(foundationGroup, footingGeo, matConcrete);
      footing.position.set(pos[0], pos[1], pos[2]);

      const rebarGeo = new THREE.CylinderGeometry(0.04, 0.04, 1.2, 8);
      for (let r = 0; r < 4; r++) {
        const rebar = addMeshWithEdges(foundationGroup, rebarGeo, matRebar);
        const rx = pos[0] + (r % 2 === 0 ? 0.35 : -0.35);
        const rz = pos[2] + (r > 1 ? 0.35 : -0.35);
        rebar.position.set(rx, pos[1] + 0.4, rz);
      }
    });

    // =========================================================================
    // NIVEAU 1 : REZ-DE-CHAUSSÉE (AVEC OUVERTURES, BAIES & PORTE PIVOTANTE)
    // =========================================================================
    // Piliers structuraux en Marron Teck / Pierre Noble
    const colGeo = new THREE.BoxGeometry(0.5, 3.0, 0.5);
    footingPositions.forEach(pos => {
      const col = addMeshWithEdges(rdcGroup, colGeo, matBrownPillar);
      col.position.set(pos[0], 2.1, pos[2]);
    });

    // Murs maçonnés avec ouvertures découpées
    // 1. Mur arrière (Nord) avec 2 fenêtres
    const rearWallL = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(3.5, 3.0, 0.25), matWhiteWall);
    rearWallL.position.set(-3.25, 2.1, -3.95);
    const rearWallR = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(3.5, 3.0, 0.25), matWhiteWall);
    rearWallR.position.set(3.25, 2.1, -3.95);
    const rearWallTop = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(3.0, 0.8, 0.25), matWhiteWall);
    rearWallTop.position.set(0, 3.2, -3.95);
    const rearWallBottom = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(3.0, 1.0, 0.25), matWhiteWall);
    rearWallBottom.position.set(0, 1.1, -3.95);

    // Fenêtre arrière (cuisine / bureau) avec cadre Bleu Cobalt
    const rearWinFrame = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.9, 1.2, 0.28), matAzureFrame, true);
    rearWinFrame.position.set(0, 2.2, -3.95);
    const rearWinGlass = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.7, 1.0, 0.08), matGlass, true);
    rearWinGlass.position.set(0, 2.2, -3.95);

    // 2. Mur gauche (Ouest) avec fenêtre verticale en bandeau
    const leftWallA = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.25, 3.0, 2.5), matWhiteWall);
    leftWallA.position.set(-4.95, 2.1, -2.5);
    const leftWallB = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.25, 3.0, 2.5), matWhiteWall);
    leftWallB.position.set(-4.95, 2.1, 2.5);
    const leftWallTop = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.25, 0.8, 2.8), matWhiteWall);
    leftWallTop.position.set(-4.95, 3.2, 0);
    const leftWallBot = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.25, 0.9, 2.8), matWhiteWall);
    leftWallBot.position.set(-4.95, 1.05, 0);

    const leftWinFrame = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.28, 1.3, 2.7), matAzureFrame, true);
    leftWinFrame.position.set(-4.95, 2.15, 0);
    const leftWinGlass = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.08, 1.1, 2.5), matGlass, true);
    leftWinGlass.position.set(-4.95, 2.15, 0);

    // 3. Façade Avant (Sud) : Baie Vitrée Coulissante & Entrée Principale
    // Mur maçonné d'angle droit
    const frontWallR = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(1.2, 3.0, 0.25), matWhiteWall);
    frontWallR.position.set(4.4, 2.1, 3.95);

    // Linteau supérieur au-dessus de la baie et de la porte
    const frontLintel = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(9.0, 0.4, 0.25), matWhiteWall);
    frontLintel.position.set(-0.5, 3.4, 3.95);

    // A. ENSEMBLE PORTE D'ENTRÉE PIVOTANTE MONUMENTALE (BLEU, MARRON & OR)
    const doorFrameGeo = new THREE.BoxGeometry(1.5, 2.8, 0.28);
    const doorFrame = addMeshWithEdges(rdcGroup, doorFrameGeo, matAzureFrame, true);
    doorFrame.position.set(3.0, 1.8, 3.95);

    // Groupe Pivot de la porte (axe de rotation sur gonds)
    doorPivot = new THREE.Group();
    doorPivot.position.set(2.35, 0.4, 3.95); // Axe gauche
    rdcGroup.add(doorPivot);

    // Vantail en beau Marron Teck
    const doorLeafGeo = new THREE.BoxGeometry(1.3, 2.6, 0.08);
    const doorLeaf = addMeshWithEdges(doorPivot, doorLeafGeo, matBrownWood, true);
    doorLeaf.position.set(0.65, 1.4, 0); // Décalé par rapport à l'axe pivot

    // Poignée Jaune Or Métallique brossé
    const handleGeo = new THREE.CylinderGeometry(0.025, 0.025, 1.2, 8);
    const handle = addMeshWithEdges(doorPivot, handleGeo, matGoldYellow, true);
    handle.position.set(1.2, 1.4, 0.07);

    // B. GRANDE BAIE VITRÉE COULISSANTE DU SALON (CHÂSSIS BLEU & POIGNÉE OR)
    const bayFrameGeo = new THREE.BoxGeometry(6.0, 2.8, 0.28);
    const bayFrame = addMeshWithEdges(rdcGroup, bayFrameGeo, matAzureFrame, true);
    bayFrame.position.set(-1.4, 1.8, 3.95);

    // Panneau vitré FIXE gauche
    const fixedPanelGeo = new THREE.BoxGeometry(2.8, 2.6, 0.06);
    const fixedGlass = addMeshWithEdges(rdcGroup, fixedPanelGeo, matGlass, true);
    fixedGlass.position.set(-2.8, 1.8, 3.92);

    const fixedFrameOutline = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.85, 2.65, 0.08), matAzureFrame, true);
    fixedFrameOutline.position.set(-2.8, 1.8, 3.92);

    // Panneau vitré COULISSANT droit (groupe mobile avec poignée)
    slidingGlassPanel = new THREE.Group();
    slidingGlassPanel.position.set(0, 0, 0);
    rdcGroup.add(slidingGlassPanel);

    const slidingPanelGeo = new THREE.BoxGeometry(2.8, 2.6, 0.06);
    const slidingGlass = addMeshWithEdges(slidingGlassPanel, slidingPanelGeo, matGlass, true);
    slidingGlass.position.set(-0.2, 1.8, 3.98);

    const slidingFrame = addMeshWithEdges(slidingGlassPanel, new THREE.BoxGeometry(2.85, 2.65, 0.08), matAzureFrame, true);
    slidingFrame.position.set(-0.2, 1.8, 3.98);

    const slidingGrip = addMeshWithEdges(slidingGlassPanel, new THREE.BoxGeometry(0.05, 0.7, 0.05), matGoldYellow, true);
    slidingGrip.position.set(-1.5, 1.8, 4.05);

    // Portique architectural suspendu Marron & Or
    const porchGeo = new THREE.BoxGeometry(3.6, 0.3, 2.2);
    const porch = addMeshWithEdges(rdcGroup, porchGeo, matBrownPillar);
    porch.position.set(2.6, 3.6, 4.8);
    const porchTrim = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(3.65, 0.06, 2.25), matGoldYellow);
    porchTrim.position.set(2.6, 3.75, 4.8);

    // =========================================================================
    // NIVEAU 2 : ÉTAGE R+1 (CORNER WINDOW, PORTE-FENÊTRE BALCON & CLAUSTRAS)
    // =========================================================================
    // Dalle de compression intermédiaire
    const floorSlabGeo = new THREE.BoxGeometry(10.8, 0.45, 8.8);
    const floorSlab = addMeshWithEdges(r1Group, floorSlabGeo, matConcrete);
    floorSlab.position.set(0, 3.8, 0);

    // Volume maçonné principal R+1
    const r1MainGeo = new THREE.BoxGeometry(7.6, 2.8, 7.6);
    const r1Main = addMeshWithEdges(r1Group, r1MainGeo, matWhiteWall);
    r1Main.position.set(-1.2, 5.4, -0.2);

    // A. PORTE-FENÊTRE COULISSANTE ACCÈS BALCON (CHÂSSIS BLEU COBALT & POIGNÉE OR)
    const r1BalconyFrame = addMeshWithEdges(r1Group, new THREE.BoxGeometry(2.6, 2.4, 0.25), matAzureFrame, true);
    r1BalconyFrame.position.set(1.4, 5.3, 3.65);

    balconyDoor = new THREE.Group();
    balconyDoor.position.set(0, 0, 0);
    r1Group.add(balconyDoor);

    const r1BalconyDoorMesh = addMeshWithEdges(balconyDoor, new THREE.BoxGeometry(1.25, 2.3, 0.06), matGlass, true);
    r1BalconyDoorMesh.position.set(0.8, 5.3, 3.68);
    const r1BalconyDoorFrame = addMeshWithEdges(balconyDoor, new THREE.BoxGeometry(1.3, 2.35, 0.08), matAzureFrame, true);
    r1BalconyDoorFrame.position.set(0.8, 5.3, 3.68);

    // B. CORNER WINDOW (FENÊTRE D'ANGLE PANORAMIQUE CONTEMPORAINE)
    // Vitrage Sud d'angle
    const cornerGlassSouth = addMeshWithEdges(r1Group, new THREE.BoxGeometry(2.8, 2.0, 0.06), matGlass, true);
    cornerGlassSouth.position.set(-3.4, 5.3, 3.62);
    const cornerFrameSouth = addMeshWithEdges(r1Group, new THREE.BoxGeometry(2.85, 2.05, 0.08), matAzureFrame, true);
    cornerFrameSouth.position.set(-3.4, 5.3, 3.62);

    // Vitrage Ouest d'angle
    const cornerGlassWest = addMeshWithEdges(r1Group, new THREE.BoxGeometry(0.06, 2.0, 2.4), matGlass, true);
    cornerGlassWest.position.set(-4.98, 5.3, 2.4);
    const cornerFrameWest = addMeshWithEdges(r1Group, new THREE.BoxGeometry(0.08, 2.05, 2.45), matAzureFrame, true);
    cornerFrameWest.position.set(-4.98, 5.3, 2.4);

    // Poteau d'angle fin acier architectural Jaune Or
    const cornerPost = addMeshWithEdges(r1Group, new THREE.BoxGeometry(0.12, 2.05, 0.12), matGoldYellow, true);
    cornerPost.position.set(-4.85, 5.3, 3.62);

    // C. BALCON EN PORTE-À-FAUX & GARDE-CORPS VITRÉ
    const balconyGlassGeo = new THREE.BoxGeometry(3.6, 1.05, 0.08);
    const balconyGlass = addMeshWithEdges(r1Group, balconyGlassGeo, matGlass, true);
    balconyGlass.position.set(3.4, 4.5, 3.9);

    const railCapGeo = new THREE.BoxGeometry(3.65, 0.08, 0.1);
    const railCap = addMeshWithEdges(r1Group, railCapGeo, matGoldYellow, true);
    railCap.position.set(3.4, 5.05, 3.9);

    // Brise-soleil architectural en bois Marron Teck chaleureux
    for (let l = 0; l < 5; l++) {
      const louvreGeo = new THREE.BoxGeometry(0.08, 2.4, 0.35);
      const louvre = addMeshWithEdges(r1Group, louvreGeo, matBrownWood, true);
      louvre.position.set(2.4 + l * 0.38, 5.3, 3.65);
    }

    // =========================================================================
    // NIVEAU 3 : TOITURE TERRASSE & PERGOLA DESIGN (MARRON, JAUNE & BLEU)
    // =========================================================================
    // Dalle supérieure toiture en Marron Teck / Pierre
    const roofSlabGeo = new THREE.BoxGeometry(11.2, 0.4, 9.2);
    const roofSlab = addMeshWithEdges(roofGroup, roofSlabGeo, matBrownPillar);
    roofSlab.position.set(0, 7.0, 0);

    // Acrotères Bleu Royal avec liseré supérieur Jaune Doré
    const acroGeoF = new THREE.BoxGeometry(11.2, 0.5, 0.25);
    addMeshWithEdges(roofGroup, acroGeoF, matNavyBlue).position.set(0, 7.35, 4.45);
    const acroGeoB = new THREE.BoxGeometry(11.2, 0.5, 0.25);
    addMeshWithEdges(roofGroup, acroGeoB, matNavyBlue).position.set(0, 7.35, -4.45);

    const acroTrimF = new THREE.BoxGeometry(11.25, 0.08, 0.28);
    addMeshWithEdges(roofGroup, acroTrimF, matGoldYellow).position.set(0, 7.62, 4.45);
    const acroTrimB = new THREE.BoxGeometry(11.25, 0.08, 0.28);
    addMeshWithEdges(roofGroup, acroTrimB, matGoldYellow).position.set(0, 7.62, -4.45);

    // Pergola bioclimatique Marron Chêne Doré & Poteaux Marron Teck
    for (let p = 0; p < 7; p++) {
      const beamGeo = new THREE.BoxGeometry(0.15, 0.2, 5.2);
      const beam = addMeshWithEdges(roofGroup, beamGeo, matPergola);
      beam.position.set(-3.0 + p * 0.8, 8.4, 0);
    }
    const pergolaPostGeo = new THREE.BoxGeometry(0.2, 1.4, 0.2);
    [ [-3.0, 7.7, -2.5], [-3.0, 7.7, 2.5], [1.8, 7.7, -2.5], [1.8, 7.7, 2.5] ].forEach(pos => {
      const post = addMeshWithEdges(roofGroup, pergolaPostGeo, matBrownPillar);
      post.position.set(pos[0], pos[1], pos[2]);
    });

    // Panneaux solaires photovoltaïques Bleu Cobalt & Cadre Jaune Or
    for (let s = 0; s < 3; s++) {
      const solarGeo = new THREE.BoxGeometry(1.6, 0.08, 2.4);
      const solar = addMeshWithEdges(roofGroup, solarGeo, matAzureFrame);
      solar.rotation.x = -0.2;
      solar.position.set(3.2, 7.4, -2.0 + s * 2.2);

      const solarTrimGeo = new THREE.BoxGeometry(1.65, 0.03, 2.45);
      const solarTrim = addMeshWithEdges(roofGroup, solarTrimGeo, matGoldYellow);
      solarTrim.rotation.x = -0.2;
      solarTrim.position.set(3.2, 7.37, -2.0 + s * 2.2);
    }
  }

  function applyRenderMode(mode) {
    currentMode = mode;
    const isWire = mode === 'blueprint';
    const isConcrete = mode === 'concrete';

    modelGroup.traverse((child) => {
      if (child.isMesh && child.material) {
        // Gestion des arêtes filaires CAO (Blueprint pur sans diagonales parasites)
        if (child.userData.edgeLines) {
          child.userData.edgeLines.visible = isWire;
        }

        if (isWire) {
          // En mode Blueprint : les solides deviennent translucides bleu cobalt CAO,
          // les arêtes d'ingénierie (Edges) s'illuminent en jaune doré et bleu cyan pur !
          child.material.wireframe = false;
          child.material.transparent = true;
          child.material.opacity = child.userData.isOpening ? 0.35 : 0.2;
          child.material.color.setHex(child.userData.isOpening ? 0x0284c7 : 0x172554);
        } else if (isConcrete) {
          // Mode Structure Béton & Aciers
          child.material.wireframe = false;
          child.material.transparent = child.userData.isGlass;
          child.material.opacity = child.userData.isGlass ? 0.25 : 1.0;
          if (child.userData.isGlass) {
            child.material.color.setHex(0x38bdf8);
          } else {
            child.material.color.setHex(0x94a3b8);
          }
        } else {
          // Mode Prestige Réaliste : Restauration parfaite des textures d'origine Marron, Jaune & Bleu
          child.material.wireframe = false;
          child.material.transparent = child.userData.origTransparent !== undefined ? child.userData.origTransparent : false;
          child.material.opacity = child.userData.origOpacity !== undefined ? child.userData.origOpacity : 1.0;
          if (child.userData.origColor !== undefined) {
            child.material.color.setHex(child.userData.origColor);
          }
          if (child.userData.origRoughness !== undefined) {
            child.material.roughness = child.userData.origRoughness;
          }
          if (child.userData.origMetalness !== undefined) {
            child.material.metalness = child.userData.origMetalness;
          }
        }
      }
    });

    if (isWire) {
      scene.background.setHex(0x061426);
    } else {
      scene.background.setHex(0x0c213d);
    }
  }

  function setupInteractions() {
    // Bouton Ouvrir / Fermer les Ouvertures (Portes & Baies)
    const btnDoors = document.getElementById('feeBtnBimOpenDoors');
    const hudDoorLabel = document.getElementById('feeBimDoorLabel');

    if (btnDoors) {
      btnDoors.addEventListener('click', () => {
        areDoorsOpen = !areDoorsOpen;
        btnDoors.classList.toggle('active', areDoorsOpen);
        const icon = btnDoors.querySelector('i');
        const span = btnDoors.querySelector('span');
        if (icon) {
          icon.className = areDoorsOpen ? 'fa fa-door-closed mr-1' : 'fa fa-door-open mr-1';
        }
        if (span) {
          span.textContent = areDoorsOpen ? 'Fermer les Baies' : 'Ouvrir les Baies';
        }

        // Mise à jour de l'indicateur HUD
        if (hudDoorLabel) {
          hudDoorLabel.textContent = areDoorsOpen ? 'Grandes Ouvertes (Pivot & Rails)' : 'Fermées';
          hudDoorLabel.style.color = areDoorsOpen ? '#22c55e' : '#ea580c';
        }

        // Éclairage intérieur et orientation face caméra pour vue directe
        if (interiorLight) {
          interiorLight.intensity = areDoorsOpen ? 2.5 : 1.3;
        }

        if (areDoorsOpen && modelGroup) {
          // Orienter la villa face aux baies pour voir immédiatement le mouvement
          modelGroup.rotation.y = 0.15;
          autoRotate = false;
          const btnRotate = document.getElementById('feeBtnBimRotate');
          if (btnRotate) btnRotate.classList.remove('active');
        }
      });
    }

    // Bouton Écorché / Explode
    const btnExplode = document.getElementById('feeBtnBimExplode');
    if (btnExplode) {
      btnExplode.addEventListener('click', () => {
        isExploded = !isExploded;
        btnExplode.classList.toggle('active', isExploded);
        const icon = btnExplode.querySelector('i');
        if (icon) {
          icon.className = isExploded ? 'fa fa-compress mr-1' : 'fa fa-layer-group mr-1';
        }
      });
    }

    // Boutons de Modes de Rendu
    const modeBtns = document.querySelectorAll('[data-bim-mode]');
    modeBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        modeBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyRenderMode(btn.getAttribute('data-bim-mode'));
      });
    });

    // Bouton Rotation Auto
    const btnRotate = document.getElementById('feeBtnBimRotate');
    if (btnRotate) {
      btnRotate.addEventListener('click', () => {
        autoRotate = !autoRotate;
        btnRotate.classList.toggle('active', autoRotate);
      });
    }

    // Bouton Réinitialiser Caméra
    const btnReset = document.getElementById('feeBtnBimReset');
    if (btnReset) {
      btnReset.addEventListener('click', () => {
        camera.position.set(17, 13, 21);
        if (controls) {
          controls.target.set(0, 3.2, 0);
          controls.update();
        }
      });
    }
  }

  function onWindowResize() {
    const container = document.getElementById('feeBimCanvasWrap');
    if (!container || !renderer || !camera) return;
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    if (width <= 768) {
      camera.position.set(28, 22, 34);
    } else {
      camera.position.set(17, 13, 21);
    }
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
  }

  function animate() {
    animationFrameId = requestAnimationFrame(animate);

    // 1. Interpolation douce de la vue éclatée (Explode View)
    const factor = isExploded ? 1 : 0;
    const speed = 0.08;

    foundationGroup.position.y += ((targetPositions.foundation.exploded * factor) - foundationGroup.position.y) * speed;
    rdcGroup.position.y += ((targetPositions.rdc.exploded * factor) - rdcGroup.position.y) * speed;
    r1Group.position.y += ((targetPositions.r1.exploded * factor) - r1Group.position.y) * speed;
    roofGroup.position.y += ((targetPositions.roof.exploded * factor) - roofGroup.position.y) * speed;

    // 2. Interpolation fluide de l'ouverture des portes et baies vitrées coulissantes
    const doorTargetAngle = areDoorsOpen ? -1.65 : 0; // ~95° d'ouverture sur gonds
    const slideTargetX = areDoorsOpen ? -2.7 : 0; // Glissement grande baie salon
    const balconySlideX = areDoorsOpen ? 1.6 : 0; // Glissement porte balcon R+1

    if (doorPivot) {
      doorPivot.rotation.y += (doorTargetAngle - doorPivot.rotation.y) * 0.09;
    }
    if (slidingGlassPanel) {
      slidingGlassPanel.position.x += (slideTargetX - slidingGlassPanel.position.x) * 0.09;
    }
    if (balconyDoor) {
      balconyDoor.position.x += (balconySlideX - balconyDoor.position.x) * 0.09;
    }

    // 3. Rotation orbitale automatique (uniquement si activée)
    if (autoRotate && modelGroup) {
      modelGroup.rotation.y += 0.0035;
    }

    // 4. Mise à jour de la caméra
    if (controls) {
      controls.update();
    }

    renderer.render(scene, camera);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBIM);
  } else {
    initBIM();
  }
})();
