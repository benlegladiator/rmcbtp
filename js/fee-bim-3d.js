/**
 * RMC BTP SARL / FEE BTP SARL - Studio 3D BIM Architectural & Structural Viewer
 * Version Ultime : Ouvertures Réalistes, Hotspots BTP, Cycle Solaire (Jour/Golden Hour/Nuit),
 * Sélecteur de Finitions Façade, Cotations Architecturales, Échelle Humaine & Mode Plein Écran
 */

(function() {
  'use strict';

  let scene, camera, renderer, controls;
  let modelGroup, foundationGroup, rdcGroup, r1Group, roofGroup;
  let isExploded = false;
  let areDoorsOpen = false;
  let currentMode = 'prestige'; // 'prestige', 'blueprint', 'concrete'
  let autoRotate = true;
  let animationFrameId;

  // Éclairage & Ambiance
  let ambientLight, sunLight, fillLight, interiorLight, nightAppliques = [];
  let sunMode = 'day'; // 'day', 'sunset', 'night'

  // Finitions Façade
  let currentFinish = 'teck-white'; // 'teck-white', 'stone-gold', 'concrete-bronze'

  // Dimensions & Échelle humaine
  let dimensionsGroup, humanFigure;
  let showDimensions = false;

  // Objets interactifs mobiles (ouvertures)
  let doorPivot, slidingGlassPanel, balconyDoor;

  // Hotspots BTP
  let hotspotsVisible = true;
  const hotspotsData = [
    {
      id: 'hs-foundations',
      badge: 'GÉNIE CIVIL • NIVEAU 0',
      title: 'Semelles Isolées & Radier Hydrofuge',
      desc: 'Fondations calculées aux Eurocodes 2. Béton armé vibré 350 kg/m³ dosé avec plastifiant hydrofuge et aciers haute adhérence FeE500 anti-affaissement.',
      meta1: 'Garantie Décennale 10 ans',
      meta2: 'Contrôle Bureau Véritas',
      pos: { x: 4.5, y: -0.2, z: 3.4 }
    },
    {
      id: 'hs-pillars',
      badge: 'STRUCTURE PORTEUSE • RDC',
      title: 'Poteaux Béton Armé & Pierre Noble',
      desc: 'Poteaux 50x50 cm capables d’encaisser des descentes de charges critiques avec parement pierre teck et continuité parfaite de ferraillage.',
      meta1: 'Résistance 35 MPa certifiée',
      meta2: 'Enrobage aciers 3 cm respecté',
      pos: { x: -4.2, y: 2.2, z: 3.4 }
    },
    {
      id: 'hs-doors',
      badge: 'SECOND ŒUVRE & VITRAGES',
      title: 'Baies Coulissantes Double Vitrage',
      desc: 'Menuiseries aluminium rupture de pont thermique (SIMS Quincaillerie). Vitrages feuilletés 44.2 acoustiques et anti-effraction.',
      meta1: 'Étanchéité Air/Eau AEV A*4',
      meta2: 'Ouverture fluide sur rails inox',
      pos: { x: 0.8, y: 1.8, z: 3.9 }
    },
    {
      id: 'hs-slab',
      badge: 'PLANCHER INTERMÉDIAIRE • R+1',
      title: 'Dalle Pleine Nervurée 16+4 cm',
      desc: 'Hourdis allégés et table de compression vibrée garantissant zéro flèche, isolation phonique renforcée entre appartements et longue portée.',
      meta1: 'Charge admissible 400 kg/m²',
      meta2: 'Rupture thermique intégrée',
      pos: { x: -2.0, y: 4.0, z: 3.8 }
    },
    {
      id: 'hs-roof',
      badge: 'ÉTANCHÉITÉ & ÉNERGIE • R+2',
      title: 'Toiture-Terrasse & Pergola Solaire',
      desc: 'Étanchéité bicouche élastomère SBS protégée par chape gravillons, complétée par une pergola bioclimatique et 3 panneaux photovoltaïques.',
      meta1: 'Pente d’évacuation 2.5% normée',
      meta2: 'Production solaire 1.5 kWc',
      pos: { x: 2.8, y: 8.0, z: 0.0 }
    }
  ];

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

    // 1. Scène
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0c213d);
    scene.fog = new THREE.FogExp2(0x0c213d, 0.015);

    // 2. Caméra
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
      controls.minDistance = 6;
      controls.maxDistance = 50;
      controls.target.set(0, 3.2, 0);
    }

    // 5. Éclairage
    setupLights();

    // 6. Construction Maquette
    buildArchitecturalModel();

    // 7. Grille de sol
    buildGridFloor();

    // 8. Cotations & Échelle humaine (créées masquées)
    buildDimensionsAndHuman();

    // 9. Initialisation des Hotspots DOM
    initHotspotsDOM();

    // 10. Événements
    window.addEventListener('resize', onWindowResize);
    setupInteractions();

    // 11. Boucle d'animation
    animate();
  }

  function setupLights() {
    ambientLight = new THREE.AmbientLight(0xffffff, 0.75);
    scene.add(ambientLight);

    // Soleil principal BTP
    sunLight = new THREE.DirectionalLight(0xfff7ed, 1.4);
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

    fillLight = new THREE.DirectionalLight(0xe2e8f0, 0.45);
    fillLight.position.set(-18, 12, -15);
    scene.add(fillLight);

    // Éclairage intérieur salon
    interiorLight = new THREE.PointLight(0xfbbf24, 1.3, 24);
    interiorLight.position.set(0, 3.5, 0);
    scene.add(interiorLight);

    // Appliques architecturales extérieures pour mode Nuit
    const appliquePositions = [
      [-3.8, 2.5, 3.9], [3.8, 2.5, 3.9],
      [-3.8, 5.8, 3.9], [3.8, 5.8, 3.9]
    ];
    appliquePositions.forEach(pos => {
      const pLight = new THREE.PointLight(0x38bdf8, 0, 10);
      pLight.position.set(pos[0], pos[1], pos[2]);
      scene.add(pLight);
      nightAppliques.push(pLight);
    });
  }

  function buildGridFloor() {
    const grid = new THREE.GridHelper(32, 32, 0xf59e0b, 0x1d4ed8);
    grid.position.y = -0.05;
    scene.add(grid);
  }

  function createMaterial(type, colorHex, opacity = 1, roughness = 0.4, metalness = 0.15) {
    if (type === 'glass') {
      return new THREE.MeshPhysicalMaterial({
        color: 0x38bdf8,
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

  function addMeshWithEdges(group, geo, mat, isOpening = false, roleTag = 'general') {
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
    mesh.userData.roleTag = roleTag;

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

    const matConcrete = createMaterial('solid', 0x94a3b8, 1, 0.85, 0.05);
    const matRebar = createMaterial('solid', 0xf59e0b, 1, 0.35, 0.7);
    const matWhiteWall = createMaterial('solid', 0xfffef7, 1, 0.45, 0.05);
    const matBrownPillar = createMaterial('solid', 0x78350f, 1, 0.5, 0.15);
    const matBrownWood = createMaterial('solid', 0x92400e, 1, 0.4, 0.1);
    const matGoldYellow = createMaterial('solid', 0xf59e0b, 1, 0.25, 0.85);
    const matAzureFrame = createMaterial('solid', 0x2563eb, 1, 0.28, 0.65);
    const matNavyBlue = createMaterial('solid', 0x1e3a8a, 1, 0.35, 0.4);
    const matPergola = createMaterial('solid', 0xa16207, 1, 0.35, 0.15);
    const matGlass = createMaterial('glass', 0x38bdf8);

    // =========================================================================
    // NIVEAU 0 : FONDATIONS & RADIER
    // =========================================================================
    const slabGeo = new THREE.BoxGeometry(10.2, 0.6, 8.2);
    const foundationSlab = addMeshWithEdges(foundationGroup, slabGeo, matConcrete, false, 'foundation');
    foundationSlab.position.set(0, 0.3, 0);

    const footingPositions = [
      [-4.2, -0.6, -3.2], [0, -0.6, -3.2], [4.2, -0.6, -3.2],
      [-4.2, -0.6, 3.2],  [0, -0.6, 3.2],  [4.2, -0.6, 3.2]
    ];
    footingPositions.forEach(pos => {
      const footingGeo = new THREE.BoxGeometry(1.6, 0.8, 1.6);
      const footing = addMeshWithEdges(foundationGroup, footingGeo, matConcrete, false, 'foundation');
      footing.position.set(pos[0], pos[1], pos[2]);

      const rebarGeo = new THREE.CylinderGeometry(0.04, 0.04, 1.2, 8);
      for (let r = 0; r < 4; r++) {
        const rebar = addMeshWithEdges(foundationGroup, rebarGeo, matRebar, false, 'rebar');
        const rx = pos[0] + (r % 2 === 0 ? 0.35 : -0.35);
        const rz = pos[2] + (r > 1 ? 0.35 : -0.35);
        rebar.position.set(rx, pos[1] + 0.4, rz);
      }
    });

    // =========================================================================
    // NIVEAU 1 : REZ-DE-CHAUSSÉE
    // =========================================================================
    const colGeo = new THREE.BoxGeometry(0.5, 3.0, 0.5);
    footingPositions.forEach(pos => {
      const col = addMeshWithEdges(rdcGroup, colGeo, matBrownPillar, false, 'pillar');
      col.position.set(pos[0], 2.1, pos[2]);
    });

    // Murs maçonnés
    const backWallW = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.4, 3.0, 0.3), matWhiteWall, false, 'wall');
    backWallW.position.set(-3.5, 2.1, -3.8);
    const backWallC = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.4, 3.0, 0.3), matWhiteWall, false, 'wall');
    backWallC.position.set(0, 2.1, -3.8);
    const backWallE = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(2.4, 3.0, 0.3), matWhiteWall, false, 'wall');
    backWallE.position.set(3.5, 2.1, -3.8);

    const backWinGeo = new THREE.BoxGeometry(1.4, 1.6, 0.1);
    addMeshWithEdges(rdcGroup, backWinGeo, matGlass, true, 'glass').position.set(-1.8, 2.3, -3.8);
    addMeshWithEdges(rdcGroup, backWinGeo, matGlass, true, 'glass').position.set(1.8, 2.3, -3.8);

    const leftWall = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.3, 3.0, 7.6), matWhiteWall, false, 'wall');
    leftWall.position.set(-4.8, 2.1, 0);
    const rightWall = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(0.3, 3.0, 7.6), matWhiteWall, false, 'wall');
    rightWall.position.set(4.8, 2.1, 0);

    // Façade avant (Sud)
    const frontWallL = addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(1.8, 3.0, 0.3), matWhiteWall, false, 'wall');
    frontWallL.position.set(-3.9, 2.1, 3.8);

    addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(1.5, 2.4, 0.35), matAzureFrame, true, 'trim').position.set(-2.0, 1.8, 3.8);

    doorPivot = new THREE.Group();
    doorPivot.position.set(-2.6, 1.8, 3.8);
    rdcGroup.add(doorPivot);

    const doorLeafGeo = new THREE.BoxGeometry(1.2, 2.2, 0.12);
    const doorLeaf = addMeshWithEdges(doorPivot, doorLeafGeo, matBrownWood, true, 'trim');
    doorLeaf.position.set(0.6, 0, 0);

    const handleGeo = new THREE.CylinderGeometry(0.025, 0.025, 0.7, 8);
    const handle = addMeshWithEdges(doorPivot, handleGeo, matGoldYellow, true, 'trim');
    handle.position.set(1.05, 0, 0.12);

    addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(5.2, 0.6, 0.3), matWhiteWall, false, 'wall').position.set(1.8, 3.3, 3.8);
    addMeshWithEdges(rdcGroup, new THREE.BoxGeometry(5.2, 0.12, 0.35), matAzureFrame, true, 'trim').position.set(1.8, 0.66, 3.8);

    const fixedGlassGeo = new THREE.BoxGeometry(2.4, 2.4, 0.1);
    addMeshWithEdges(rdcGroup, fixedGlassGeo, matGlass, true, 'glass').position.set(0.6, 1.9, 3.75);

    slidingGlassPanel = new THREE.Group();
    slidingGlassPanel.position.set(3.0, 1.9, 3.85);
    rdcGroup.add(slidingGlassPanel);

    addMeshWithEdges(slidingGlassPanel, fixedGlassGeo, matGlass, true, 'glass').position.set(0, 0, 0);
    addMeshWithEdges(slidingGlassPanel, new THREE.BoxGeometry(2.45, 0.08, 0.14), matAzureFrame, true, 'trim').position.set(0, 1.2, 0);
    addMeshWithEdges(slidingGlassPanel, new THREE.BoxGeometry(2.45, 0.08, 0.14), matAzureFrame, true, 'trim').position.set(0, -1.2, 0);

    // =========================================================================
    // NIVEAU 2 : PREMIER ÉTAGE (R+1) & BALCON
    // =========================================================================
    const floorSlabGeo = new THREE.BoxGeometry(10.6, 0.45, 8.6);
    addMeshWithEdges(r1Group, floorSlabGeo, matConcrete, false, 'slab').position.set(0, 3.8, 0);

    const balconyGeo = new THREE.BoxGeometry(5.4, 0.35, 1.8);
    addMeshWithEdges(r1Group, balconyGeo, matBrownPillar, false, 'pillar').position.set(1.8, 3.8, 5.0);

    const glassRailingGeo = new THREE.BoxGeometry(5.4, 1.0, 0.08);
    addMeshWithEdges(r1Group, glassRailingGeo, matGlass, true, 'glass').position.set(1.8, 4.5, 5.85);
    const railTopGeo = new THREE.BoxGeometry(5.45, 0.08, 0.15);
    addMeshWithEdges(r1Group, railTopGeo, matGoldYellow, true, 'trim').position.set(1.8, 5.02, 5.85);

    footingPositions.forEach(pos => {
      const col = addMeshWithEdges(r1Group, colGeo, matBrownPillar, false, 'pillar');
      col.position.set(pos[0], 5.5, pos[2]);
    });

    addMeshWithEdges(r1Group, new THREE.BoxGeometry(9.6, 3.0, 0.3), matWhiteWall, false, 'wall').position.set(0, 5.5, -3.8);
    addMeshWithEdges(r1Group, new THREE.BoxGeometry(0.3, 3.0, 7.6), matWhiteWall, false, 'wall').position.set(-4.8, 5.5, 0);
    addMeshWithEdges(r1Group, new THREE.BoxGeometry(0.3, 3.0, 7.6), matWhiteWall, false, 'wall').position.set(4.8, 5.5, 0);

    addMeshWithEdges(r1Group, new THREE.BoxGeometry(4.2, 3.0, 0.3), matWhiteWall, false, 'wall').position.set(-2.7, 5.5, 3.8);
    addMeshWithEdges(r1Group, new THREE.BoxGeometry(4.8, 0.8, 0.3), matWhiteWall, false, 'wall').position.set(1.8, 6.6, 3.8);

    balconyDoor = new THREE.Group();
    balconyDoor.position.set(1.8, 5.1, 3.85);
    r1Group.add(balconyDoor);

    addMeshWithEdges(balconyDoor, new THREE.BoxGeometry(2.2, 2.2, 0.08), matGlass, true, 'glass').position.set(0, 0, 0);
    addMeshWithEdges(balconyDoor, new THREE.BoxGeometry(2.25, 0.08, 0.12), matAzureFrame, true, 'trim').position.set(0, 1.1, 0);
    addMeshWithEdges(balconyDoor, new THREE.BoxGeometry(2.25, 0.08, 0.12), matAzureFrame, true, 'trim').position.set(0, -1.1, 0);

    for (let l = 0; l < 4; l++) {
      const louvreGeo = new THREE.BoxGeometry(0.08, 2.6, 0.4);
      addMeshWithEdges(r1Group, louvreGeo, matBrownWood, true, 'trim').position.set(2.4 + l * 0.38, 5.3, 3.65);
    }

    // =========================================================================
    // NIVEAU 3 : TOITURE TERRASSE & PERGOLA
    // =========================================================================
    const roofSlabGeo = new THREE.BoxGeometry(11.2, 0.4, 9.2);
    addMeshWithEdges(roofGroup, roofSlabGeo, matBrownPillar, false, 'pillar').position.set(0, 7.0, 0);

    const acroGeoF = new THREE.BoxGeometry(11.2, 0.5, 0.25);
    addMeshWithEdges(roofGroup, acroGeoF, matNavyBlue, false, 'trim').position.set(0, 7.35, 4.45);
    const acroGeoB = new THREE.BoxGeometry(11.2, 0.5, 0.25);
    addMeshWithEdges(roofGroup, acroGeoB, matNavyBlue, false, 'trim').position.set(0, 7.35, -4.45);

    const acroTrimF = new THREE.BoxGeometry(11.25, 0.08, 0.28);
    addMeshWithEdges(roofGroup, acroTrimF, matGoldYellow, false, 'trim').position.set(0, 7.62, 4.45);
    const acroTrimB = new THREE.BoxGeometry(11.25, 0.08, 0.28);
    addMeshWithEdges(roofGroup, acroTrimB, matGoldYellow, false, 'trim').position.set(0, 7.62, -4.45);

    for (let p = 0; p < 7; p++) {
      const beamGeo = new THREE.BoxGeometry(0.15, 0.2, 5.2);
      addMeshWithEdges(roofGroup, beamGeo, matPergola, false, 'trim').position.set(-3.0 + p * 0.8, 8.4, 0);
    }
    const pergolaPostGeo = new THREE.BoxGeometry(0.2, 1.4, 0.2);
    [ [-3.0, 7.7, -2.5], [-3.0, 7.7, 2.5], [1.8, 7.7, -2.5], [1.8, 7.7, 2.5] ].forEach(pos => {
      addMeshWithEdges(roofGroup, pergolaPostGeo, matBrownPillar, false, 'pillar').position.set(pos[0], pos[1], pos[2]);
    });

    for (let s = 0; s < 3; s++) {
      const solarGeo = new THREE.BoxGeometry(1.6, 0.08, 2.4);
      const solar = addMeshWithEdges(roofGroup, solarGeo, matAzureFrame, false, 'trim');
      solar.rotation.x = -0.2;
      solar.position.set(3.2, 7.4, -2.0 + s * 2.2);

      const solarTrimGeo = new THREE.BoxGeometry(1.65, 0.03, 2.45);
      const solarTrim = addMeshWithEdges(roofGroup, solarTrimGeo, matGoldYellow, false, 'trim');
      solarTrim.rotation.x = -0.2;
      solarTrim.position.set(3.2, 7.37, -2.0 + s * 2.2);
    }
  }

  // =========================================================================
  // COTATIONS ARCHITECTURALES & ÉCHELLE HUMAINE
  // =========================================================================
  function buildDimensionsAndHuman() {
    dimensionsGroup = new THREE.Group();
    dimensionsGroup.visible = false;
    scene.add(dimensionsGroup);

    const lineMat = new THREE.LineDashedMaterial({
      color: 0x38bdf8,
      dashSize: 0.4,
      gapSize: 0.2,
      linewidth: 2
    });

    const hPoints = [ new THREE.Vector3(-6.2, 0, 4.2), new THREE.Vector3(-6.2, 8.8, 4.2) ];
    const hGeo = new THREE.BufferGeometry().setFromPoints(hPoints);
    const hLine = new THREE.Line(hGeo, lineMat);
    hLine.computeLineDistances();
    dimensionsGroup.add(hLine);

    const wPoints = [ new THREE.Vector3(-5.6, -0.4, 5.2), new THREE.Vector3(5.6, -0.4, 5.2) ];
    const wGeo = new THREE.BufferGeometry().setFromPoints(wPoints);
    const wLine = new THREE.Line(wGeo, lineMat);
    wLine.computeLineDistances();
    dimensionsGroup.add(wLine);

    humanFigure = new THREE.Group();
    humanFigure.position.set(-2.0, 0.6, 4.8);

    const bodyMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });
    const head = new THREE.Mesh(new THREE.SphereGeometry(0.12, 12, 12), bodyMat);
    head.position.y = 1.68;
    humanFigure.add(head);

    const torso = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.15, 0.8, 12), bodyMat);
    torso.position.y = 1.15;
    humanFigure.add(torso);

    const legs = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.75, 8), bodyMat);
    legs.position.set(-0.07, 0.38, 0);
    humanFigure.add(legs);
    const legs2 = legs.clone();
    legs2.position.set(0.07, 0.38, 0);
    humanFigure.add(legs2);

    dimensionsGroup.add(humanFigure);
  }

  // =========================================================================
  // GESTION DU CYCLE SOLAIRE (JOUR / CRÉPUSCULE / NUIT)
  // =========================================================================
  function setSunMode(mode) {
    sunMode = mode;
    const hudSun = document.getElementById('feeBimSunLabel');
    const btnSunText = document.getElementById('feeBtnBimSunText');
    const btnSun = document.getElementById('feeBtnBimSun');

    if (mode === 'day') {
      scene.background.setHex(0x0c213d);
      scene.fog.color.setHex(0x0c213d);
      ambientLight.color.setHex(0xffffff);
      ambientLight.intensity = 0.75;
      sunLight.color.setHex(0xfff7ed);
      sunLight.intensity = 1.4;
      sunLight.position.set(22, 28, 16);
      interiorLight.intensity = 1.3;
      interiorLight.color.setHex(0xfbbf24);
      nightAppliques.forEach(a => a.intensity = 0);
      if (hudSun) hudSun.textContent = '☀️ Plein Jour';
      if (btnSunText) btnSunText.textContent = 'Jour';
      if (btnSun) btnSun.querySelector('i').className = 'fa fa-sun';
    } else if (mode === 'sunset') {
      scene.background.setHex(0x271428);
      scene.fog.color.setHex(0x271428);
      ambientLight.color.setHex(0xfdba74);
      ambientLight.intensity = 0.6;
      sunLight.color.setHex(0xf97316);
      sunLight.intensity = 1.8;
      sunLight.position.set(28, 8, 12);
      interiorLight.intensity = 2.2;
      interiorLight.color.setHex(0xfbbf24);
      nightAppliques.forEach(a => a.intensity = 0.8);
      if (hudSun) hudSun.textContent = '🌆 Golden Hour';
      if (btnSunText) btnSunText.textContent = 'Crépuscule';
      if (btnSun) btnSun.querySelector('i').className = 'fa fa-cloud-sun';
    } else { // night
      scene.background.setHex(0x030712);
      scene.fog.color.setHex(0x030712);
      ambientLight.color.setHex(0x1e293b);
      ambientLight.intensity = 0.25;
      sunLight.intensity = 0.15;
      sunLight.color.setHex(0x93c5fd);
      interiorLight.intensity = 4.0;
      interiorLight.color.setHex(0xfbbf24);
      nightAppliques.forEach(a => a.intensity = 2.4);
      if (hudSun) hudSun.textContent = '🌙 Nuit VIP & LED';
      if (btnSunText) btnSunText.textContent = 'Nuit';
      if (btnSun) btnSun.querySelector('i').className = 'fa fa-moon';
    }
  }

  // =========================================================================
  // GESTION DU SÉLECTEUR DE FINITIONS FAÇADE
  // =========================================================================
  function applyFacadeFinish(finishKey) {
    currentFinish = finishKey;
    const finishLabel = document.getElementById('feeBimFinishLabel');

    let wallColor = 0xfffef7;
    let pillarColor = 0x78350f;
    let glassColor = 0x38bdf8;
    let trimColor = 0xf59e0b;

    if (finishKey === 'stone-gold') {
      wallColor = 0x64748b;
      pillarColor = 0x1e293b;
      glassColor = 0x38bdf8;
      trimColor = 0xf59e0b;
      if (finishLabel) finishLabel.textContent = 'Pierre Minérale & Anthracite';
    } else if (finishKey === 'concrete-bronze') {
      wallColor = 0x334155;
      pillarColor = 0x0f172a;
      glassColor = 0xd97706;
      trimColor = 0xb45309;
      if (finishLabel) finishLabel.textContent = 'Béton Ciré & Bronze Fumé';
    } else {
      wallColor = 0xfffef7;
      pillarColor = 0x78350f;
      glassColor = 0x38bdf8;
      trimColor = 0xf59e0b;
      if (finishLabel) finishLabel.textContent = 'Blanc Pur & Teck Noble';
    }

    if (currentMode === 'prestige') {
      modelGroup.traverse(child => {
        if (child.isMesh && child.material && child.userData.roleTag) {
          if (child.userData.roleTag === 'wall') child.material.color.setHex(wallColor);
          if (child.userData.roleTag === 'pillar') child.material.color.setHex(pillarColor);
          if (child.userData.roleTag === 'trim') child.material.color.setHex(trimColor);
          if (child.userData.roleTag === 'glass') child.material.color.setHex(glassColor);
        }
      });
    }
  }

  // =========================================================================
  // INITIALISATION DES HOTSPOTS DOM 3D
  // =========================================================================
  function initHotspotsDOM() {
    const layer = document.getElementById('feeBimHotspotsLayer');
    if (!layer) return;
    layer.innerHTML = '';

    hotspotsData.forEach((hs, idx) => {
      const pin = document.createElement('div');
      pin.className = 'fee-bim-hotspot-pin';
      pin.id = hs.id;
      pin.innerHTML = `
        <div class="fee-bim-pin-pulse"></div>
        <div class="fee-bim-pin-core">${idx + 1}</div>
      `;
      pin.addEventListener('click', () => showHotspotCard(hs));
      layer.appendChild(pin);
      hs.el = pin;
    });

    const closeBtn = document.getElementById('feeBimCardClose');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        const card = document.getElementById('feeBimHotspotCard');
        if (card) card.classList.add('d-none');
      });
    }
  }

  function showHotspotCard(hs) {
    const card = document.getElementById('feeBimHotspotCard');
    const badge = document.getElementById('feeBimCardBadge');
    const title = document.getElementById('feeBimCardTitle');
    const desc = document.getElementById('feeBimCardDesc');
    const meta = document.getElementById('feeBimCardMeta');
    if (!card) return;

    if (badge) badge.textContent = hs.badge;
    if (title) title.textContent = hs.title;
    if (desc) desc.textContent = hs.desc;
    if (meta) {
      meta.innerHTML = `
        <span><i class="fa fa-shield text-success mr-1"></i> ${hs.meta1}</span>
        <span><i class="fa fa-check text-primary mr-1"></i> ${hs.meta2}</span>
      `;
    }
    card.classList.remove('d-none');
  }

  function updateHotspotsScreenPosition() {
    if (!hotspotsVisible || !camera) return;
    const container = document.getElementById('feeBimCanvasWrap');
    if (!container) return;
    const w = container.clientWidth;
    const h = container.clientHeight;

    const tempV = new THREE.Vector3();

    hotspotsData.forEach(hs => {
      if (!hs.el) return;
      tempV.set(hs.pos.x, hs.pos.y, hs.pos.z);
      
      if (isExploded) {
        if (hs.id === 'hs-foundations') tempV.y += targetPositions.foundation.exploded;
        if (hs.id === 'hs-slab' || hs.id === 'hs-pillars') tempV.y += targetPositions.r1.exploded;
        if (hs.id === 'hs-roof') tempV.y += targetPositions.roof.exploded;
      }

      tempV.applyEuler(modelGroup.rotation);
      tempV.project(camera);

      if (tempV.z > 1) {
        hs.el.style.display = 'none';
        return;
      }

      hs.el.style.display = 'flex';
      const x = (tempV.x * 0.5 + 0.5) * w;
      const y = (-(tempV.y * 0.5) + 0.5) * h;
      hs.el.style.left = `${x}px`;
      hs.el.style.top = `${y}px`;
    });
  }

  // =========================================================================
  // GESTION DU RENDU (MODES : RÉALISTE, BLUEPRINT, BÉTON)
  // =========================================================================
  function applyRenderMode(mode) {
    currentMode = mode;
    const isWire = mode === 'blueprint';
    const isConcrete = mode === 'concrete';

    modelGroup.traverse((child) => {
      if (child.isMesh && child.material) {
        if (child.userData.edgeLines) {
          child.userData.edgeLines.visible = isWire;
        }

        if (isWire) {
          child.material.wireframe = false;
          child.material.transparent = true;
          child.material.opacity = child.userData.isOpening ? 0.35 : 0.2;
          child.material.color.setHex(child.userData.isOpening ? 0x0284c7 : 0x172554);
        } else if (isConcrete) {
          child.material.wireframe = false;
          child.material.transparent = child.userData.isGlass;
          child.material.opacity = child.userData.isGlass ? 0.25 : 1.0;
          child.material.color.setHex(child.userData.isGlass ? 0x38bdf8 : 0x94a3b8);
        } else {
          child.material.wireframe = false;
          child.material.transparent = child.userData.origTransparent !== undefined ? child.userData.origTransparent : false;
          child.material.opacity = child.userData.origOpacity !== undefined ? child.userData.origOpacity : 1.0;
          if (child.userData.origColor !== undefined) {
            child.material.color.setHex(child.userData.origColor);
          }
        }
      }
    });

    if (isWire) {
      scene.background.setHex(0x061426);
    } else {
      if (sunMode === 'day') scene.background.setHex(0x0c213d);
      if (sunMode === 'sunset') scene.background.setHex(0x271428);
      if (sunMode === 'night') scene.background.setHex(0x030712);
    }
  }

  // =========================================================================
  // ÉCOUTEURS D'INTERACTIONS & TOUCH
  // =========================================================================
  function setupInteractions() {
    const btnDoors = document.getElementById('feeBtnBimOpenDoors');
    const hudDoorLabel = document.getElementById('feeBimDoorLabel');
    if (btnDoors) {
      btnDoors.addEventListener('click', () => {
        areDoorsOpen = !areDoorsOpen;
        btnDoors.classList.toggle('active', areDoorsOpen);
        const icon = btnDoors.querySelector('i');
        const span = btnDoors.querySelector('span');
        if (icon) icon.className = areDoorsOpen ? 'fa fa-door-closed mr-1' : 'fa fa-door-open mr-1';
        if (span) span.textContent = areDoorsOpen ? 'Fermer' : 'Baies';

        if (hudDoorLabel) {
          hudDoorLabel.textContent = areDoorsOpen ? 'Grandes Ouvertes' : 'Fermées';
          hudDoorLabel.style.color = areDoorsOpen ? '#10b981' : '#00d2ff';
        }
        if (interiorLight) {
          interiorLight.intensity = areDoorsOpen ? 2.5 : 1.3;
        }
        if (areDoorsOpen && modelGroup) {
          modelGroup.rotation.y = 0.15;
          autoRotate = false;
          const btnRotate = document.getElementById('feeBtnBimRotate');
          if (btnRotate) btnRotate.classList.remove('active');
        }
      });
    }

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

    const btnSun = document.getElementById('feeBtnBimSun');
    if (btnSun) {
      btnSun.addEventListener('click', () => {
        if (sunMode === 'day') setSunMode('sunset');
        else if (sunMode === 'sunset') setSunMode('night');
        else setSunMode('day');
      });
    }

    const btnHotspots = document.getElementById('feeBtnBimHotspots');
    if (btnHotspots) {
      btnHotspots.addEventListener('click', () => {
        hotspotsVisible = !hotspotsVisible;
        btnHotspots.classList.toggle('active', hotspotsVisible);
        const layer = document.getElementById('feeBimHotspotsLayer');
        if (layer) layer.style.display = hotspotsVisible ? 'block' : 'none';
        const card = document.getElementById('feeBimHotspotCard');
        if (card && !hotspotsVisible) card.classList.add('d-none');
      });
    }

    const btnDimensions = document.getElementById('feeBtnBimDimensions');
    if (btnDimensions) {
      btnDimensions.addEventListener('click', () => {
        showDimensions = !showDimensions;
        btnDimensions.classList.toggle('active', showDimensions);
        if (dimensionsGroup) dimensionsGroup.visible = showDimensions;
      });
    }

    const btnFullscreen = document.getElementById('feeBtnBimFullscreen');
    const wrap = document.getElementById('feeBimCanvasWrap');
    if (btnFullscreen && wrap) {
      btnFullscreen.addEventListener('click', () => {
        wrap.classList.toggle('is-fullscreen');
        const icon = btnFullscreen.querySelector('i');
        const isFull = wrap.classList.contains('is-fullscreen');
        if (icon) icon.className = isFull ? 'fa fa-compress' : 'fa fa-expand';
        setTimeout(onWindowResize, 50);
      });
    }

    const paletteDots = document.querySelectorAll('.fee-palette-dot');
    paletteDots.forEach(dot => {
      dot.addEventListener('click', () => {
        paletteDots.forEach(d => d.classList.remove('active'));
        dot.classList.add('active');
        applyFacadeFinish(dot.getAttribute('data-finish'));
      });
    });

    const modeBtns = document.querySelectorAll('[data-bim-mode]');
    modeBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        modeBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyRenderMode(btn.getAttribute('data-bim-mode'));
      });
    });

    const btnRotate = document.getElementById('feeBtnBimRotate');
    if (btnRotate) {
      btnRotate.addEventListener('click', () => {
        autoRotate = !autoRotate;
        btnRotate.classList.toggle('active', autoRotate);
      });
    }

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

    if (window.DeviceOrientationEvent && typeof window.DeviceOrientationEvent.requestPermission !== 'function') {
      window.addEventListener('deviceorientation', handleOrientation);
    }
  }

  function handleOrientation(e) {
    if (!controls || !e.gamma || !e.beta) return;
    const tiltX = (e.gamma) * 0.002;
    if (modelGroup && !autoRotate) {
      modelGroup.rotation.y += tiltX * 0.1;
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

    const factor = isExploded ? 1 : 0;
    const speed = 0.08;

    foundationGroup.position.y += ((targetPositions.foundation.exploded * factor) - foundationGroup.position.y) * speed;
    rdcGroup.position.y += ((targetPositions.rdc.exploded * factor) - rdcGroup.position.y) * speed;
    r1Group.position.y += ((targetPositions.r1.exploded * factor) - r1Group.position.y) * speed;
    roofGroup.position.y += ((targetPositions.roof.exploded * factor) - roofGroup.position.y) * speed;

    const doorTargetAngle = areDoorsOpen ? -1.65 : 0;
    const slideTargetX = areDoorsOpen ? -2.7 : 0;
    const balconySlideX = areDoorsOpen ? 1.6 : 0;

    if (doorPivot) doorPivot.rotation.y += (doorTargetAngle - doorPivot.rotation.y) * 0.09;
    if (slidingGlassPanel) slidingGlassPanel.position.x += (slideTargetX - slidingGlassPanel.position.x) * 0.09;
    if (balconyDoor) balconyDoor.position.x += (balconySlideX - balconyDoor.position.x) * 0.09;

    if (autoRotate && modelGroup) {
      modelGroup.rotation.y += 0.0032;
    }

    if (controls) {
      controls.update();
    }

    if (hotspotsVisible) {
      updateHotspotsScreenPosition();
    }

    renderer.render(scene, camera);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBIM);
  } else {
    initBIM();
  }
})();
