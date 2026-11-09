/**
 * RMC BTP SARL - Carte Interactive des Chantiers & Réalisations au Cameroun
 * Moteur Leaflet.js haute fluidité avec tuiles OpenStreetMap & filtres géographiques
 */

(function() {
  'use strict';

  let map;
  let markersLayer;

  const projectsGeo = [
    {
      id: 1,
      title: "Siège Social & Direction Générale RMC BTP",
      city: "Yaoundé",
      type: "Direction & Ingénierie",
      status: "Actif 24/7",
      lat: 3.8667,
      lng: 11.5167,
      surface: "Direction Générale",
      desc: "Siège administratif, bureau d'études techniques, coordination chantiers et centrale d'achats.",
      image: "images/_JMP6225.jpg",
      link: "contact.php"
    },
    {
      id: 2,
      title: "Villa Duplex Contemporaine Haut Standing",
      city: "Yaoundé",
      type: "Bâtiment Haut Standing",
      status: "En cours (78%)",
      lat: 3.8240,
      lng: 11.5350,
      surface: "450 m² couverte",
      desc: "Fondations en semelles isolées, poteaux béton armé et finitions de prestige SIMS Décor.",
      image: "images/IMG-20261029-WA0099.jpg",
      link: "project.php"
    },
    {
      id: 3,
      title: "Agence Régionale Littoral & Synergie Matériaux",
      city: "Douala",
      type: "Agence & Matériaux",
      status: "Actif",
      lat: 4.0511,
      lng: 9.7679,
      surface: "Showroom & Logistique",
      desc: "Showroom finitions SIMS Décor et centrale quincaillerie pour la zone côtière.",
      image: "images/_JMP6286.jpg",
      link: "contact.php"
    },
    {
      id: 4,
      title: "Immeuble Mixte Résidentiel & Commercial R+4",
      city: "Douala",
      type: "Immeuble & Commerces",
      status: "Livré avec Succès",
      lat: 4.0380,
      lng: 9.7020,
      surface: "1 400 m² sur 5 niveaux",
      desc: "Structure béton armé banché, dalles nervurées et ascenseur, réalisé sans intermédiaire.",
      image: "images/_JMP6259.jpg",
      link: "project.php"
    },
    {
      id: 5,
      title: "Résidence Balnéaire Clés en Main",
      city: "Kribi",
      type: "Résidentiel & Tourisme",
      status: "Livré",
      lat: 2.9370,
      lng: 9.9077,
      surface: "520 m² viabilisés",
      desc: "Conception bioclimatique côtière, traitements hydrofuges anti-corrosion marine.",
      image: "images/IMG-20261029-WA0098.jpg",
      link: "project.php"
    },
    {
      id: 6,
      title: "Superstructure & Gros Œuvre R+3",
      city: "Bafoussam",
      type: "Gros Œuvre & BTP",
      status: "Terminé",
      lat: 5.4778,
      lng: 10.4176,
      surface: "850 m²",
      desc: "Chantier gros œuvre avec approvisionnement direct en fers certifiés et ciment CPJ 42.5.",
      image: "images/IMG-20261029-WA0101.jpg",
      link: "project.php"
    }
  ];

  function initMap() {
    const mapEl = document.getElementById('feeCamerounMap');
    if (!mapEl) return;

    if (typeof L === 'undefined') {
      console.warn('Leaflet non chargé.');
      return;
    }

    // Coordonnées centrées sur le Cameroun (Yaoundé / axe Yaoundé - Douala)
    const centerCoords = [3.95, 10.60];
    map = L.map('feeCamerounMap', {
      center: centerCoords,
      zoom: 7,
      scrollWheelZoom: false,
      zoomControl: true
    });

    // Fond de carte OpenStreetMap haute lisibilité
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; OpenStreetMap &bull; <strong>RMC BTP SARL</strong>'
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    populateMarkers(projectsGeo);
    setupFilters();

    // Redimensionnement fluide lors des rotations d'écrans mobiles
    window.addEventListener('resize', () => {
      if (map) {
        map.invalidateSize();
      }
    });
  }

  function createCustomIcon(proj) {
    const isHQ = proj.city === 'Yaoundé' && proj.type.includes('Direction');
    return L.divIcon({
      className: 'fee-map-marker-container',
      html: `
        <div class="fee-map-custom-marker ${isHQ ? 'hq-marker' : ''}" style="${isHQ ? 'background: linear-gradient(135deg, #0c2e60, #1e40af);' : ''}">
          <i class="fa ${isHQ ? 'fa-building' : 'fa-hard-hat'}"></i>
        </div>
      `,
      iconSize: [36, 36],
      iconAnchor: [18, 18],
      popupAnchor: [0, -22]
    });
  }

  function createPopupContent(proj) {
    return `
      <div class="fee-map-popup-card">
        <div class="fee-map-popup-img" style="background-image: url('${proj.image}');">
          <span class="fee-map-popup-badge">${proj.type}</span>
        </div>
        <div class="fee-map-popup-body">
          <h6 class="fee-map-popup-title">${proj.title}</h6>
          <div class="fee-map-popup-meta">
            <i class="fa fa-map-marker text-danger mr-1"></i> ${proj.city} (Cameroun)
            &bull; <strong style="color: #ea580c;">${proj.status}</strong>
          </div>
          <p style="font-size: 11.5px; color: #475569; margin-bottom: 8px; line-height: 1.4;">${proj.desc}</p>
          <div style="background: #f8fafc; padding: 6px 10px; border-radius: 6px; font-size: 11px; margin-bottom: 10px;">
            <strong>Envergure :</strong> ${proj.surface}
          </div>
          <a href="${proj.link}" class="btn btn-sm btn-primary btn-block text-white font-weight-bold" style="border-radius: 50px; font-size: 11px;">
            Consulter ce projet BTP &rarr;
          </a>
        </div>
      </div>
    `;
  }

  function populateMarkers(list) {
    markersLayer.clearLayers();
    const bounds = [];

    list.forEach(proj => {
      const marker = L.marker([proj.lat, proj.lng], {
        icon: createCustomIcon(proj)
      });
      marker.bindPopup(createPopupContent(proj), { maxWidth: 320 });
      markersLayer.addLayer(marker);
      bounds.push([proj.lat, proj.lng]);
    });

    if (bounds.length > 0 && map) {
      map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
    }
  }

  function setupFilters() {
    const filterBtns = document.querySelectorAll('.fee-map-filter-btn');
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const city = btn.getAttribute('data-city');
        if (!city || city === 'all') {
          populateMarkers(projectsGeo);
        } else if (city === 'national') {
          const filtered = projectsGeo.filter(p => p.city === 'Douala' || p.city === 'Yaoundé');
          populateMarkers(filtered);
        } else {
          const filtered = projectsGeo.filter(p => p.city.toLowerCase() === city.toLowerCase());
          populateMarkers(filtered);
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMap);
  } else {
    initMap();
  }
})();
