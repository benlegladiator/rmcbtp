/**
 * FEE BTP SARL - Moteur 3D Interactif & Expérience Visuelle Next-Gen
 * - 1. Wireframe 3D Blueprint Canvas interactif (Hero)
 * - 2. Moteur de Tilt 3D Physique avec réflexion spéculaire (Cartes, Piliers, Projets)
 * - 3. Parallaxe de profondeur gyroscopique / au scroll
 */

(function() {
  'use strict';

  /* ==========================================================================
     1. HERO 3D BLUEPRINT CANVAS (Maillage géométrique architectural rotatif)
     ========================================================================== */
  function initHero3DCanvas() {
    const canvas = document.getElementById('feeHero3DCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height;
    let mouseX = 0, mouseY = 0;
    let targetX = 0, targetY = 0;
    let angleX = 0.2, angleY = 0.3;

    function resize() {
      const parent = canvas.parentElement;
      width = canvas.width = parent.clientWidth || window.innerWidth;
      height = canvas.height = parent.clientHeight || window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    // Suivi de la souris pour inclinaison dynamique
    window.addEventListener('mousemove', (e) => {
      const rect = canvas.getBoundingClientRect();
      if (e.clientY < rect.bottom) {
        targetX = (e.clientX - width / 2) * 0.0005;
        targetY = (e.clientY - height / 2) * 0.0005;
      }
    });

    // ========================================================================
    // STRUCTURE GÉOMÉTRIQUE IMPOSANTE : TOUR RMC BTP HIGH-RISE & FONDATIONS
    // ========================================================================
    const nodes = [];
    const edges = [];
    const floors = 14; // Gratte-ciel de 14 niveaux
    const baseRadius = Math.min(width, height) * 0.216; // Réduit de 40% (0.36 * 0.60 = 0.216) pour cadrage parfait
    const pointsPerFloor = 8; // Octogone architectural moderne

    for (let f = 0; f < floors; f++) {
      // Étage avec profil aérodynamique décroissant et flèche sommitale
      const floorProgress = f / (floors - 1);
      const floorRadius = baseRadius * (1 - Math.pow(floorProgress, 1.4) * 0.45);
      const y = (f - floors * 0.42) * (baseRadius * 0.22);

      // Anneau extérieur octogonal
      for (let i = 0; i < pointsPerFloor; i++) {
        const theta = (i / pointsPerFloor) * Math.PI * 2;
        nodes.push({
          x0: Math.cos(theta) * floorRadius,
          y0: -y, // Croissance vers le haut
          z0: Math.sin(theta) * floorRadius,
          floor: f,
          isCore: false
        });
      }

      // Noyau central porteur (Piliers intérieurs de génie civil)
      const coreRadius = floorRadius * 0.42;
      for (let i = 0; i < 4; i++) {
        const theta = (i / 4) * Math.PI * 2 + Math.PI / 4;
        nodes.push({
          x0: Math.cos(theta) * coreRadius,
          y0: -y,
          z0: Math.sin(theta) * coreRadius,
          floor: f,
          isCore: true
        });
      }
    }

    // Flèche sommitale / Antenne de prestige (Point culminant)
    const topFloorY = (floors - 1 - floors * 0.42) * (baseRadius * 0.22);
    const apexIndex = nodes.length;
    nodes.push({
      x0: 0,
      y0: -(topFloorY + baseRadius * 0.65),
      z0: 0,
      floor: floors,
      isCore: true,
      isApex: true
    });

    // Lier les arrêtes du bâtiment
    const nodeStride = pointsPerFloor + 4; // 8 extérieurs + 4 intérieurs = 12 par étage
    for (let f = 0; f < floors; f++) {
      const baseIdx = f * nodeStride;

      // 1. Périmètre extérieur
      for (let i = 0; i < pointsPerFloor; i++) {
        const cur = baseIdx + i;
        const next = baseIdx + ((i + 1) % pointsPerFloor);
        edges.push([cur, next, 'ring']);

        // Colonnes verticales extérieures
        if (f < floors - 1) {
          const above = baseIdx + nodeStride + i;
          edges.push([cur, above, 'column']);

          // Contreventements croisés en X (Treillis BTP de haute ingénierie)
          if (f % 2 === 0) {
            const aboveNext = baseIdx + nodeStride + ((i + 1) % pointsPerFloor);
            edges.push([cur, aboveNext, 'brace']);
          }
        }
      }

      // 2. Noyau intérieur central
      const coreOffset = baseIdx + pointsPerFloor;
      for (let i = 0; i < 4; i++) {
        const curCore = coreOffset + i;
        const nextCore = coreOffset + ((i + 1) % 4);
        edges.push([curCore, nextCore, 'core-ring']);

        if (f < floors - 1) {
          const aboveCore = coreOffset + nodeStride + i;
          edges.push([curCore, aboveCore, 'core-column']);
        }

        // Poutres radiales reliant le noyau à la façade extérieure
        edges.push([curCore, baseIdx + (i * 2), 'radial']);
      }
    }

    // Relier les derniers étages à l'apex (flèche sommitale)
    const lastFloorBase = (floors - 1) * nodeStride;
    for (let i = 0; i < pointsPerFloor; i += 2) {
      edges.push([lastFloorBase + i, apexIndex, 'spire']);
    }

    // 3. Fondations Massives & Semelles Béton Armé (Base du Bâtiment)
    const foundationPads = [];
    const groundY = (floors * 0.35) * (baseRadius * 0.22);

    // Semelles isolées et massifs de fondation sous chaque colonne extérieure
    for (let i = 0; i < pointsPerFloor; i++) {
      const theta = (i / pointsPerFloor) * Math.PI * 2;
      const padX = Math.cos(theta) * baseRadius;
      const padZ = Math.sin(theta) * baseRadius;
      const padSize = baseRadius * 0.14;
      foundationPads.push({ x: padX, y: groundY, z: padZ, size: padSize });
    }

    // Grille de fondation géotechnique au sol (Sol de chantier / Radier général)
    const gridLines = [];
    const gridSize = baseRadius * 1.6;
    const gridSteps = 7;
    for (let g = -gridSteps; g <= gridSteps; g++) {
      const pos = (g / gridSteps) * gridSize;
      gridLines.push([
        { x: -gridSize, y: groundY, z: pos },
        { x: gridSize, y: groundY, z: pos }
      ]);
      gridLines.push([
        { x: pos, y: groundY, z: -gridSize },
        { x: pos, y: groundY, z: gridSize }
      ]);
    }

    // Particules volumétriques dorées et d'énergie de construction
    const particles = [];
    for (let p = 0; p < 50; p++) {
      particles.push({
        x: (Math.random() - 0.5) * baseRadius * 3.2,
        y: (Math.random() - 0.5) * baseRadius * 3.2,
        z: (Math.random() - 0.5) * baseRadius * 3.2,
        size: Math.random() * 2.5 + 1.2,
        speed: Math.random() * 0.007 + 0.003,
        alpha: Math.random() * 0.6 + 0.4
      });
    }

    let autoRotation = 0; // Rotation continue à 360°
    let scanLaserY = 0;

    function render() {
      ctx.clearRect(0, 0, width, height);

      // Adaptation responsive progressive : rétrécit harmonieusement sans disparaître
      const responsiveScale = width <= 480 ? 0.40 : (width <= 768 ? 0.55 : (width <= 991 ? 0.72 : 1.0));

      // Rotation continue 360° fluide & cinématique (vitesse type turntable architectural)
      autoRotation += 0.0075;

      // angleX = rotation 360° continue + influence douce de la souris
      angleX = autoRotation + (targetX * 2);
      
      // angleY = légère inclinaison de perspective en plongée/contre-plongée
      angleY += (targetY - angleY) * 0.05;

      const cosX = Math.cos(angleX);
      const sinX = Math.sin(angleX);
      const cosY = Math.cos(angleY + 0.18); // Légère contre-plongée majestueuse
      const sinY = Math.sin(angleY + 0.18);

      // Centrage équilibré selon la taille d'écran
      const cx = width > 992 ? width * 0.74 : (width > 600 ? width * 0.50 : width * 0.50);
      const cy = width > 992 ? height * 0.50 : (width > 600 ? height * 0.52 : height * 0.54);
      const fov = 750;

      // Opacité adaptée sur mobile pour laisser respirer les textes et photos
      const prevAlpha = ctx.globalAlpha;
      ctx.globalAlpha = width <= 576 ? 0.42 : (width <= 991 ? 0.60 : 1.0);

      // Fonction de projection 3D mise à l'échelle responsive
      function projectPoint(x, y, z) {
        const sx = x * responsiveScale;
        const sy = y * responsiveScale;
        const sz = z * responsiveScale;
        let x1 = sx * cosX - sz * sinX;
        let z1 = sz * cosX + sx * sinX;
        let y1 = sy * cosY - z1 * sinY;
        let z2 = z1 * cosY + sy * sinY + 750;
        const scale = fov / Math.max(100, z2);
        return {
          px: cx + x1 * scale,
          py: cy + y1 * scale,
          scale: scale,
          z: z2
        };
      }

      // --- A. RADIER GÉNÉRAL & GRILLE GÉOTECHNIQUE AU SOL (FONDATIONS CLAIRES ET VISIBLES) ---
      ctx.lineWidth = 1.6;
      ctx.strokeStyle = 'rgba(0, 180, 255, 0.35)'; // Grille au sol bleu cyan technologique RMC BTP
      for (let g = 0; g < gridLines.length; g++) {
        const pt1 = projectPoint(gridLines[g][0].x, gridLines[g][0].y, gridLines[g][0].z);
        const pt2 = projectPoint(gridLines[g][1].x, gridLines[g][1].y, gridLines[g][1].z);
        ctx.beginPath();
        ctx.moveTo(pt1.px, pt1.py);
        ctx.lineTo(pt2.px, pt2.py);
        ctx.stroke();
      }

      // Semelles de fondation béton au sol (Carrés massifs de soutènement)
      for (let s = 0; s < foundationPads.length; s++) {
        const pad = foundationPads[s];
        const half = pad.size;
        const pCorners = [
          projectPoint(pad.x - half, pad.y, pad.z - half),
          projectPoint(pad.x + half, pad.y, pad.z - half),
          projectPoint(pad.x + half, pad.y, pad.z + half),
          projectPoint(pad.x - half, pad.y, pad.z + half)
        ];
        ctx.beginPath();
        ctx.moveTo(pCorners[0].px, pCorners[0].py);
        for (let c = 1; c < pCorners.length; c++) {
          ctx.lineTo(pCorners[c].px, pCorners[c].py);
        }
        ctx.closePath();
        ctx.fillStyle = 'rgba(0, 114, 255, 0.25)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(0, 210, 255, 0.9)';
        ctx.lineWidth = 2.4;
        ctx.stroke();
      }

      // Projection de tous les nœuds de la tour
      const projected = [];
      for (let i = 0; i < nodes.length; i++) {
        const n = nodes[i];
        projected.push(projectPoint(n.x0, n.y0, n.z0));
      }

      // --- B. TRACÉ DES ARRÊTES ARCHITECTURALES TRÈS ÉPAISSES & CONTRASTÉES ---
      for (let e = 0; e < edges.length; e++) {
        const p1 = projected[edges[e][0]];
        const p2 = projected[edges[e][1]];
        const type = edges[e][2];

        ctx.beginPath();
        ctx.moveTo(p1.px, p1.py);
        ctx.lineTo(p2.px, p2.py);

        if (type === 'spire') {
          ctx.strokeStyle = '#00f0ff'; // Flèche cyan éclatante
          ctx.lineWidth = 3.4;
        } else if (type === 'column') {
          ctx.strokeStyle = '#0084ff'; // Colonnes porteuses bleu royal vibrant RMC
          ctx.lineWidth = 3.0;
        } else if (type === 'brace') {
          ctx.strokeStyle = 'rgba(0, 210, 255, 0.75)'; // Treillis de renfort BTP cyan
          ctx.lineWidth = 1.8;
        } else if (type === 'core-column' || type === 'core-ring') {
          ctx.strokeStyle = '#ffffff'; // Cœur béton armé blanc haute luminosité
          ctx.lineWidth = 2.2;
        } else if (type === 'radial') {
          ctx.strokeStyle = 'rgba(180, 220, 255, 0.45)';
          ctx.lineWidth = 1.5;
        } else {
          ctx.strokeStyle = '#0055cc'; // Dalles d'étages bleu de référence RMC BTP
          ctx.lineWidth = 2.2;
        }
        ctx.stroke();
      }

      // --- C. SCAN LASER ÉCHÉANCIER / NIVEAU LASER DE CHANTIER BTP ---
      scanLaserY += 0.015;
      const currentLaserFloor = (Math.sin(scanLaserY) * 0.5 + 0.5) * (floors - 1);
      const laserFloorInt = Math.floor(currentLaserFloor);
      if (laserFloorInt < floors - 1) {
        const baseScan = laserFloorInt * nodeStride;
        ctx.beginPath();
        for (let i = 0; i < pointsPerFloor; i++) {
          const pt = projected[baseScan + i];
          if (i === 0) ctx.moveTo(pt.px, pt.py);
          else ctx.lineTo(pt.px, pt.py);
        }
        ctx.closePath();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2.5;
        ctx.shadowColor = '#00d2ff';
        ctx.shadowBlur = 18;
        ctx.stroke();
        ctx.shadowBlur = 0;
      }

      // --- D. NŒUDS LUMINEUX D'ASSEMBLAGE (ÉTOILES DE STRUCTURE) ---
      for (let i = 0; i < projected.length; i++) {
        const p = projected[i];
        const isApex = nodes[i].isApex;
        const radiusPt = isApex ? 5.0 : Math.max(1.8, p.scale * 3.2);

        ctx.beginPath();
        ctx.arc(p.px, p.py, radiusPt, 0, Math.PI * 2);
        ctx.fillStyle = isApex ? '#ffffff' : (nodes[i].isCore ? '#ffffff' : '#00e5ff');
        ctx.shadowColor = isApex ? '#ffffff' : '#00aaff';
        ctx.shadowBlur = isApex ? 22 : 10;
        ctx.fill();
        ctx.shadowBlur = 0;
      }

      // --- E. PARTICULES DE POUSSIÈRE LUMINEUSE D'ATMOSPHÈRE ---
      for (let i = 0; i < particles.length; i++) {
        const pt = particles[i];
        pt.y -= pt.speed * 70;
        if (pt.y < -baseRadius * 2) pt.y = baseRadius * 2;

        const proj = projectPoint(pt.x, pt.y, pt.z);
        ctx.beginPath();
        ctx.arc(proj.px, proj.py, Math.max(0.6, pt.size * proj.scale), 0, Math.PI * 2);
        ctx.fillStyle = `rgba(0, 210, 255, ${pt.alpha})`;
        ctx.fill();
      }

      ctx.globalAlpha = prevAlpha;
      requestAnimationFrame(render);
    }

    render();
  }

  /* ==========================================================================
     2. MOTEUR TILT 3D PHYSIQUE AVEC GLOW & PERSPECTIVE
     ========================================================================== */
  function init3DTilt() {
    const tiltElements = document.querySelectorAll('[data-3d-tilt], .pillar-card, .card-wow-service, .project-card-3d');
    
    tiltElements.forEach(el => {
      // S'assurer qu'on a le conteneur de reflet spéculaire
      let glare = el.querySelector('.fee-3d-glare');
      if (!glare) {
        glare = document.createElement('div');
        glare.className = 'fee-3d-glare';
        el.style.position = 'relative';
        el.style.overflow = 'hidden';
        el.appendChild(glare);
      }

      el.addEventListener('mousemove', (e) => {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * -12; // Max 12 deg
        const rotateY = ((x - centerX) / centerX) * 12;

        el.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-8px) scale3d(1.02, 1.02, 1.02)`;

        // Déplacer le reflet de lumière
        const glareX = (x / rect.width) * 100;
        const glareY = (y / rect.height) * 100;
        glare.style.background = `radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255, 255, 255, 0.28) 0%, rgba(255, 255, 255, 0) 70%)`;
        glare.style.opacity = '1';
      });

      el.addEventListener('mouseleave', () => {
        el.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) scale3d(1, 1, 1)';
        glare.style.opacity = '0';
      });
    });
  }

  /* ==========================================================================
     3. CONTRÔLEUR VIDÉO CINÉMATIQUE 4K (Ultra-Fluide, Lazy Loaded, Fallback)
     ========================================================================== */
  function initCinematicVideo() {
    const video = document.getElementById('feeHeroCinematicVideo');
    if (!video) return;

    // Détection connexion / basse consommation
    video.play().catch(() => {
      // Si la lecture automatique est bloquée par le navigateur, activer l'écouteur du premier clic
      const startVideo = () => {
        video.play();
        window.removeEventListener('click', startVideo);
        window.removeEventListener('scroll', startVideo);
      };
      window.addEventListener('click', startVideo, { once: true });
      window.addEventListener('scroll', startVideo, { once: true });
    });
  }

  // Initialisation dès que le DOM est prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initHero3DCanvas();
      init3DTilt();
      initCinematicVideo();
    });
  } else {
    initHero3DCanvas();
    init3DTilt();
    initCinematicVideo();
  }
})();
