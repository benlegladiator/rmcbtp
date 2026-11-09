/**
 * RMC BTP SARL - Module Démonstrateur Live Chantier Tracker (Espace Diaspora)
 * Suivi en temps réel des jalons, des dosages de béton et génération PDF
 */

(function() {
  'use strict';

  const chantiersData = {
    1: {
      id: "RMC-YDE-2026-08",
      title: "Villa Duplex Contemporaine R+1",
      location: "Yaoundé, Quartier Bastos / Odza",
      client: "M. BASSOCK Jean-Paul (Diaspora - Bruxelles, Belgique)",
      conducteur: "Ing. Patrick TOUKAM (Directeur Technique RMC BTP)",
      progress: 78,
      startDate: "10 Mai 2026",
      estDelivery: "20 Décembre 2026",
      concreteSpecs: "Dosage 350 kg/m³ vibré • Ciment CPJ 42.5 • Éprouvettes validées à 28j (29.2 MPa)",
      steelSpecs: "Acier Haute Adhérence FeE500 (Approvisionnement direct SIMS Quincaillerie)",
      steps: [
        { name: "Études de Sol & Terrassement", status: "completed", date: "10 Mai - 25 Mai", note: "Sondages géotechniques conformes, plateforme viabilisée au niveau laser." },
        { name: "Fondations & Semelles Renforcées", status: "completed", date: "28 Mai - 20 Juin", note: "Semelles isolées avec longrines de redressement béton armé." },
        { name: "Gros Œuvre & Dalles R+1", status: "completed", date: "25 Juin - 18 Sept", note: "Poteaux BA, dalles pleines coulées au vibrateur et maçonnerie porteuse." },
        { name: "Second Œuvre & Lots Techniques", status: "in-progress", date: "En cours (Électricité, Plomberie)", note: "Canalisations encastrées, réseau SIMS Quincaillerie aux normes." },
        { name: "Finitions de Prestige & Remise des Clés", status: "pending", date: "Prévu Décembre 2026", note: "Carrelages grands formats et faux-plafonds staff SIMS Décor." }
      ],
      photos: [
        { url: "images/IMG-20261029-WA0099.jpg", title: "Contrôle des élévations et ferraillage poteaux", date: "Passage DG : 02 Nov 2026" },
        { url: "images/IMG-20261029-WA0098.jpg", title: "Coffrage dalle et calage des armatures", date: "24 Oct 2026" },
        { url: "images/_JMP6225.jpg", title: "Supervision Direction Technique in situ", date: "15 Oct 2026" },
        { url: "images/IMG-20261029-WA0101.jpg", title: "Coulage dalle de compression béton armé", date: "08 Oct 2026" }
      ]
    },
    2: {
      id: "RMC-DLA-2026-03",
      title: "Immeuble Mixte Résidentiel & Commercial R+4",
      location: "Douala, Boulevard Bonanjo / Akwa",
      client: "Mme TCHUENTE Chantal (Diaspora - Montréal, Canada)",
      conducteur: "M. Rodrigue DJOMO (Supervision Générale)",
      progress: 100,
      startDate: "15 Janvier 2026",
      estDelivery: "Livré & Réceptionné sans réserve",
      concreteSpecs: "Béton B25 C30 normé • Contrôle laboratoire labellisé",
      steelSpecs: "Armatures certifiées NF / MINTP Cameroun",
      steps: [
        { name: "Terrassement & Fouilles en puits", status: "completed", date: "Janvier 2026", note: "Excavation de 1 200 m³ évacuée." },
        { name: "Infrastructures & Radiers", status: "completed", date: "Février 2026", note: "Semelles filantes sur sol porteur." },
        { name: "Superstructures R+1 à R+4", status: "completed", date: "Mars - Juin 2026", note: "Coulages successifs selon calendrier strict." },
        { name: "Cloisonnements & Lots Techniques", status: "completed", date: "Juillet - Août 2026", note: "Menuiseries alu, sanitaires et staff posés." },
        { name: "Réception Définitive des Ouvrages", status: "completed", date: "Octobre 2026", note: "Clés remises avec PV de parfait achèvement." }
      ],
      photos: [
        { url: "images/_JMP6259.jpg", title: "Façade achevée et finitions de grand standing", date: "Réception Finale" },
        { url: "images/_JMP6244.jpg", title: "Vue d'ensemble de l'ouvrage livré", date: "Visite Maître d'Ouvrage" },
        { url: "images/_JMP6286.jpg", title: "Détail aménagements intérieurs SIMS Décor", date: "Livraison Clés" }
      ]
    }
  };
        { url: "images/IMG-20261015-WA0120.jpg", title: "Supervision technique finale des équipes", date: "Audit Qualité" },
        { url: "images/wa0062-framed-16x9.jpg", title: "Aménagement des abords et accès pavés", date: "Livraison" }
      ]
    }
  };

  let activeChantierId = 1;

  function renderTracker(data) {
    // Header & Meta
    const elTitle = document.getElementById('feeTrackTitle');
    const elRef = document.getElementById('feeTrackRef');
    const elClient = document.getElementById('feeTrackClient');
    const elLocation = document.getElementById('feeTrackLocation');
    const elConducteur = document.getElementById('feeTrackConducteur');
    const elDelivery = document.getElementById('feeTrackDelivery');
    const elPercent = document.getElementById('feeTrackPercent');
    const elBar = document.getElementById('feeTrackProgressBar');
    const elConcrete = document.getElementById('feeTrackConcrete');
    const elSteel = document.getElementById('feeTrackSteel');

    if (elTitle) elTitle.textContent = data.title;
    if (elRef) elRef.textContent = "Dossier : " + data.id;
    if (elClient) elClient.textContent = data.client;
    if (elLocation) elLocation.textContent = data.location;
    if (elConducteur) elConducteur.textContent = data.conducteur;
    if (elDelivery) elDelivery.textContent = data.estDelivery;
    if (elPercent) elPercent.textContent = data.progress + "%";
    if (elBar) elBar.style.width = data.progress + "%";
    if (elConcrete) elConcrete.textContent = data.concreteSpecs;
    if (elSteel) elSteel.textContent = data.steelSpecs;

    // Timeline Steps
    const elSteps = document.getElementById('feeTrackStepsWrap');
    if (elSteps) {
      elSteps.innerHTML = '';
      data.steps.forEach((step, index) => {
        const stepDiv = document.createElement('div');
        stepDiv.className = `fee-tracker-step ${step.status}`;
        
        let iconHtml = '<i class="fa fa-clock-o"></i>';
        if (step.status === 'completed') iconHtml = '<i class="fa fa-check"></i>';
        if (step.status === 'in-progress') iconHtml = '<i class="fa fa-cogs"></i>';

        let badgeLabel = 'À venir';
        if (step.status === 'completed') badgeLabel = 'Validé 100%';
        if (step.status === 'in-progress') badgeLabel = 'En cours d\'exécution';

        stepDiv.innerHTML = `
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fee-tracker-step-badge">${iconHtml}</div>
            <span class="badge ${step.status === 'completed' ? 'badge-success' : (step.status === 'in-progress' ? 'badge-warning text-dark' : 'badge-light')}" style="font-size: 10px;">${badgeLabel}</span>
          </div>
          <h6 class="font-weight-bold mb-1" style="font-size: 13.5px; color: #0f172a;">${index + 1}. ${step.name}</h6>
          <small class="text-muted d-block mb-1"><i class="fa fa-calendar-check-o mr-1"></i> ${step.date}</small>
          <small class="d-block" style="font-size: 11px; color: #64748b; line-height: 1.4;">${step.note}</small>
        `;
        elSteps.appendChild(stepDiv);
      });
    }

    // Photos
    const elPhotos = document.getElementById('feeTrackPhotosWrap');
    if (elPhotos) {
      elPhotos.innerHTML = '';
      data.photos.forEach(photo => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3 mb-3';
        col.innerHTML = `
          <div class="fee-tracker-photo-thumb" style="background-image: url('${photo.url}');" onclick="window.open('${photo.url}', '_blank')">
            <div class="fee-tracker-photo-date">${photo.date}</div>
          </div>
          <small class="d-block text-truncate mt-1 font-weight-bold text-dark" style="font-size: 11px;" title="${photo.title}">${photo.title}</small>
        `;
        elPhotos.appendChild(col);
      });
    }
  }

  function generateChantierPDF() {
    const data = chantiersData[activeChantierId];
    if (!data) return;

    // Création d'une structure HTML imprimable aux normes FEE BTP
    const printArea = document.createElement('div');
    printArea.id = 'feePrintableReport';
    printArea.style.padding = '35px';
    printArea.style.background = '#ffffff';
    printArea.style.fontFamily = 'Helvetica, Arial, sans-serif';
    printArea.style.color = '#1e293b';

    printArea.innerHTML = `
      <div style="border-bottom: 3px solid #d97706; padding-bottom: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h2 style="margin: 0; color: #0c2e60; font-size: 24px; font-weight: bold; letter-spacing: -0.5px;">RMC BTP SARL</h2>
          <p style="margin: 3px 0 0; color: #d97706; font-size: 12px; font-weight: bold; text-transform: uppercase;">Construire aujourd'hui les solutions de demain • Yaoundé & Douala</p>
          <p style="margin: 2px 0 0; color: #64748b; font-size: 11px;">Bâtiment & Génie Civil • Direction Générale : M. Rodrigue DJOMO & Ing. Patrick TOUKAM</p>
        </div>
        <div style="text-align: right;">
          <div style="background: #0c2e60; color: #fff; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block;">
            RAPPORT OFFICIEL DE SUIVI CHANTIER
          </div>
          <p style="margin: 5px 0 0; font-size: 11px; color: #64748b;">Généré le : ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
        </div>
      </div>

      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 25px;">
        <table style="width: 100%; font-size: 13px; line-height: 1.8;">
          <tr>
            <td style="width: 50%;"><strong>Ouvrage :</strong> ${data.title}</td>
            <td style="width: 50%;"><strong>Réf. Dossier :</strong> ${data.id}</td>
          </tr>
          <tr>
            <td><strong>Maître d'Ouvrage :</strong> ${data.client}</td>
            <td><strong>Localisation :</strong> ${data.location}</td>
          </tr>
          <tr>
            <td><strong>Direction Technique :</strong> ${data.conducteur}</td>
            <td><strong>Avancement Général :</strong> <span style="color: #d97706; font-weight: bold; font-size: 15px;">${data.progress}%</span></td>
          </tr>
        </table>
      </div>

      <h4 style="color: #0c2e60; margin-bottom: 12px; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">État d'Exécution des Jalons Techniques</h4>
      <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 25px;">
        <thead>
          <tr style="background: #0c2e60; color: #ffffff;">
            <th style="padding: 8px 10px; text-align: left;">Jalon / Phase</th>
            <th style="padding: 8px 10px; text-align: left;">Période</th>
            <th style="padding: 8px 10px; text-align: center;">Statut</th>
            <th style="padding: 8px 10px; text-align: left;">Contrôles & Observations</th>
          </tr>
        </thead>
        <tbody>
          ${data.steps.map((s, idx) => `
            <tr style="border-bottom: 1px solid #e2e8f0; background: ${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
              <td style="padding: 8px 10px; font-weight: bold;">${idx + 1}. ${s.name}</td>
              <td style="padding: 8px 10px; color: #64748b;">${s.date}</td>
              <td style="padding: 8px 10px; text-align: center;">
                <span style="font-weight: bold; color: ${s.status === 'completed' ? '#059669' : (s.status === 'in-progress' ? '#d97706' : '#94a3b8')};">
                  ${s.status === 'completed' ? 'VALIDÉ' : (s.status === 'in-progress' ? 'EN COURS' : 'PROGRAMMÉ')}
                </span>
              </td>
              <td style="padding: 8px 10px; color: #334155;">${s.note}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>

      <div style="background: #fffbeb; border-left: 4px solid #d97706; padding: 14px; border-radius: 6px; margin-bottom: 25px; font-size: 12px;">
        <strong>Conformité Géotechnique & Matériaux :</strong><br>
        • Béton : ${data.concreteSpecs}<br>
        • Aciers : ${data.steelSpecs}
      </div>

      <div style="margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="font-size: 11px; color: #64748b; max-width: 320px;">
          Document certifié par RMC BTP SARL. Fait foi auprès du maître d'ouvrage pour déblocage des tranches d'avancement contractuelles.
        </div>
        <div style="text-align: center; border: 1px dashed #cbd5e1; padding: 15px 25px; border-radius: 8px;">
          <p style="margin: 0 0 5px; font-size: 12px; font-weight: bold; color: #0c2e60;">Visa Direction Générale RMC BTP</p>
          <p style="margin: 0; font-size: 11px; color: #d97706; font-weight: bold;">M. Rodrigue DJOMO & Ing. Patrick TOUKAM</p>
          <small style="color: #94a3b8; font-size: 10px;">[Signature Numérique & Cachet BTP]</small>
        </div>
      </div>
    `;

    document.body.appendChild(printArea);

    if (typeof html2pdf !== 'undefined') {
      const opt = {
        margin:       10,
        filename:     `Rapport_Chantier_${data.id}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(printArea).save().then(() => {
        document.body.removeChild(printArea);
      }).catch(() => {
        window.print();
        document.body.removeChild(printArea);
      });
    } else {
      window.print();
      document.body.removeChild(printArea);
    }
  }

  function initTracker() {
    renderTracker(chantiersData[activeChantierId]);

    // Boutons de bascule de chantier
    const toggleBtns = document.querySelectorAll('[data-track-chantier]');
    toggleBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        toggleBtns.forEach(b => b.classList.remove('btn-primary', 'text-white'));
        toggleBtns.forEach(b => b.classList.add('btn-outline-secondary'));
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary', 'text-white');
        
        activeChantierId = parseInt(btn.getAttribute('data-track-chantier'), 10);
        renderTracker(chantiersData[activeChantierId]);
      });
    });

    // Bouton de Téléchargement PDF (Façade d'accroche -> Redirection Immédiate WhatsApp)
    const btnDownload = document.getElementById('feeBtnDownloadTrackPdf');
    if (btnDownload) {
      btnDownload.addEventListener('click', (e) => {
        e.preventDefault();
        const data = chantiersData[activeChantierId] || chantiersData[1];
        const msg = `Bonjour RMC BTP SARL ! Je consulte actuellement votre site et je souhaite recevoir le Rapport Officiel de Suivi de Chantier en version PDF certifiée :
• Ouvrage : ${data.title}
• Réf. Dossier : ${data.id}
• Localisation : ${data.location}
• Maître d'Ouvrage : ${data.client}
• Avancement actuel : ${data.progress}%
Pouvez-vous me transmettre ce rapport PDF officiel avec le visa de la Direction Générale ? Merci !`;

        window.open(`https://wa.me/237697398455?text=${encodeURIComponent(msg)}`, '_blank');
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTracker);
  } else {
    initTracker();
  }
})();
