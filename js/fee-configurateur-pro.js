/**
 * FEE BTP SARL - Configurateur de Projet BTP Étape par Étape
 * Chiffrage indicatif précis aux cours du Cameroun (FCFA & EUR),
 * Génération de devis PDF officiel et transfert direct sur WhatsApp.
 */

(function() {
  'use strict';

  // Taux de conversion FCFA / EUR
  const EUR_RATE = 655.957;

  // Tarifs au m² par type d'ouvrage (FCFA)
  const baseRates = {
    villa: { min: 165000, max: 210000, name: "Villa Contemporaine / Duplex" },
    building: { min: 195000, max: 255000, name: "Immeuble R+2 à R+4 (Commercial / Locatif)" },
    retaining: { min: 45000, max: 75000, name: "Génie Civil, Soutènement & VRD" },
    industrial: { min: 110000, max: 155000, name: "Hangar Métallique / Dépôt Industriel" }
  };

  // Coefficients de standing
  const standingMultipliers = {
    standard: { factor: 1.0, name: "Standard Éco-Rigueur (Béton normé, carrelage local)" },
    comfort: { factor: 1.25, name: "Grand Standing Confort (Grès cérame, alu thermolaqué)" },
    prestige: { factor: 1.55, name: "Prestige Diaspora Élite (Domotique, finitions de luxe)" }
  };

  // Coefficients topographiques (spécificité relief Dschang / Ouest)
  const terrainMultipliers = {
    flat: { factor: 1.0, name: "Terrain Plat Normal" },
    slope: { factor: 1.18, name: "Terrain en Pente Forte (Murs de soutènement & ancrages requis)" },
    wetland: { factor: 1.22, name: "Terrain Meuble ou Hydromorphe (Radier général & drainage lourd)" }
  };

  // Options complémentaires (prix forfaitaires estimés en FCFA)
  const optionsRates = {
    forage: { price: 3500000, name: "Forage d'eau autonome avec château d'eau et pompe solaire" },
    fence: { price: 2800000, name: "Clôture sécurisée maçonnée avec portail motorisé" },
    solar: { price: 4200000, name: "Centrale Solaire hybride autonome (Batteries Lithium 10kWh)" }
  };

  let state = {
    step: 1,
    projectType: 'villa',
    standing: 'comfort',
    terrain: 'slope',
    surface: 200,
    options: {
      forage: false,
      fence: false,
      solar: false
    }
  };

  function calculateQuote() {
    const base = baseRates[state.projectType] || baseRates.villa;
    const standing = standingMultipliers[state.standing] || standingMultipliers.comfort;
    const terrain = terrainMultipliers[state.terrain] || terrainMultipliers.slope;

    let minM2 = base.min * standing.factor * terrain.factor;
    let maxM2 = base.max * standing.factor * terrain.factor;

    let subTotalMin = Math.round(state.surface * minM2);
    let subTotalMax = Math.round(state.surface * maxM2);

    let optionsTotal = 0;
    if (state.options.forage) optionsTotal += optionsRates.forage.price;
    if (state.options.fence) optionsTotal += optionsRates.fence.price;
    if (state.options.solar) optionsTotal += optionsRates.solar.price;

    const totalMin = subTotalMin + optionsTotal;
    const totalMax = subTotalMax + optionsTotal;

    const totalMinEUR = Math.round(totalMin / EUR_RATE);
    const totalMaxEUR = Math.round(totalMax / EUR_RATE);

    // Estimations matières BTP
    const cementBags = Math.round(state.surface * 6.5);
    const steelTons = (state.surface * 0.045).toFixed(1);
    const concreteM3 = Math.round(state.surface * 0.65);
    const durationMonths = Math.max(3, Math.round(state.surface / 45));

    return {
      totalMin,
      totalMax,
      totalMinEUR,
      totalMaxEUR,
      cementBags,
      steelTons,
      concreteM3,
      durationMonths,
      baseName: base.name,
      standingName: standing.name,
      terrainName: terrain.name,
      optionsTotal
    };
  }

  function updateUI() {
    const result = calculateQuote();

    // Affichage des montants
    const elPriceFcfa = document.getElementById('feewizPriceFcfa');
    const elPriceEur = document.getElementById('feewizPriceEur');
    const elDuration = document.getElementById('feewizDuration');
    const elCement = document.getElementById('feewizCement');
    const elSteel = document.getElementById('feewizSteel');
    const elConcrete = document.getElementById('feewizConcrete');
    const elSurfaceVal = document.getElementById('feewizSurfaceVal');

    if (elPriceFcfa) {
      elPriceFcfa.textContent = `${result.totalMin.toLocaleString('fr-FR')} - ${result.totalMax.toLocaleString('fr-FR')} FCFA`;
    }
    if (elPriceEur) {
      elPriceEur.textContent = `(~ ${result.totalMinEUR.toLocaleString('fr-FR')} à ${result.totalMaxEUR.toLocaleString('fr-FR')} €)`;
    }
    if (elDuration) elDuration.textContent = `${result.durationMonths} Mois`;
    if (elCement) elCement.textContent = `~ ${result.cementBags} Sacs (CPJ 42.5)`;
    if (elSteel) elSteel.textContent = `~ ${result.steelTons} Tonnes (FeE500)`;
    if (elConcrete) elConcrete.textContent = `~ ${result.concreteM3} m³ (Dosé 350kg)`;
    if (elSurfaceVal) elSurfaceVal.textContent = `${state.surface} m²`;

    // Mise à jour du lien WhatsApp direct
    const btnWhatsapp = document.getElementById('feewizBtnWhatsapp');
    if (btnWhatsapp) {
      const msg = `Bonjour RMC BTP SARL ! J'ai configuré mon projet sur votre site :
• Type d'ouvrage : ${result.baseName}
• Superficie couverte : ${state.surface} m²
• Standing : ${result.standingName}
• Terrain : ${result.terrainName}
• Estimation : ${result.totalMin.toLocaleString('fr-FR')} à ${result.totalMax.toLocaleString('fr-FR')} FCFA (~${result.totalMinEUR.toLocaleString('fr-FR')} €)
Je souhaite affiner ce devis avec un ingénieur ou conducteur de travaux RMC BTP.`;
      btnWhatsapp.href = `https://wa.me/237697398455?text=${encodeURIComponent(msg)}`;
    }
  }

  function switchStep(newStep) {
    if (newStep < 1 || newStep > 4) return;
    state.step = newStep;

    // Mise à jour des onglets de navigation
    document.querySelectorAll('.fee-wizard-tab').forEach((tab, index) => {
      const stepNum = index + 1;
      tab.classList.remove('active', 'completed');
      if (stepNum === state.step) {
        tab.classList.add('active');
      } else if (stepNum < state.step) {
        tab.classList.add('completed');
      }
    });

    // Affichage de l'étape correspondante
    document.querySelectorAll('.fee-wizard-step').forEach((stepEl) => {
      stepEl.classList.remove('active');
    });
    const currentStepEl = document.getElementById(`feewizStep${state.step}`);
    if (currentStepEl) currentStepEl.classList.add('active');

    updateUI();
  }

  function generateDevisPDF() {
    const result = calculateQuote();

    const printArea = document.createElement('div');
    printArea.id = 'feePrintableReport';
    printArea.style.padding = '35px';
    printArea.style.background = '#ffffff';
    printArea.style.fontFamily = 'Helvetica, Arial, sans-serif';
    printArea.style.color = '#1e293b';

    printArea.innerHTML = `
      <div style="border-bottom: 3px solid #d97706; padding-bottom: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h2 style="margin: 0; color: #0c2e60; font-size: 24px; font-weight: bold;">RMC BTP SARL</h2>
          <p style="margin: 3px 0 0; color: #d97706; font-size: 12px; font-weight: bold; text-transform: uppercase;">Direction Générale & Ingénierie BTP • Yaoundé & Douala, Cameroun</p>
          <p style="margin: 2px 0 0; color: #64748b; font-size: 11px;">Tél: (+237) 697 39 84 55 / 693 90 91 59 &bull; info@rmcbtp.com</p>
        </div>
        <div style="text-align: right;">
          <div style="background: #0c2e60; color: #fff; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block;">
            ÉTUDE ESTIMATIVE DE PROJET BTP
          </div>
          <p style="margin: 5px 0 0; font-size: 11px; color: #64748b;">Réf: DEV-RMC-${Date.now().toString().slice(-6)} &bull; ${new Date().toLocaleDateString('fr-FR')}</p>
        </div>
      </div>

      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 25px;">
        <h4 style="margin: 0 0 10px; font-size: 15px; color: #0f172a;">Paramètres Retenus pour la Cotation :</h4>
        <table style="width: 100%; font-size: 13px; line-height: 1.8;">
          <tr>
            <td style="width: 50%;"><strong>Type d'Ouvrage :</strong> ${result.baseName}</td>
            <td style="width: 50%;"><strong>Superficie Projetée :</strong> ${state.surface} m² couverte</td>
          </tr>
          <tr>
            <td><strong>Niveau de Finition :</strong> ${result.standingName}</td>
            <td><strong>Configuration Relief :</strong> ${result.terrainName}</td>
          </tr>
          <tr>
            <td colspan="2"><strong>Options Incluses :</strong> 
              ${[
                state.options.forage ? 'Forage avec château d\'eau' : '',
                state.options.fence ? 'Clôture sécurisée maçonnée' : '',
                state.options.solar ? 'Kit solaire hybride autonome' : ''
              ].filter(Boolean).join(', ') || 'Gros œuvre & Second œuvre standard'}
            </td>
          </tr>
        </table>
      </div>

      <h4 style="color: #0f172a; margin-bottom: 12px; font-size: 15px;">Évaluation Budgétaire Indicative :</h4>
      <div style="background: #fff7ed; border: 2px solid #fed7aa; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 25px;">
        <small style="text-transform: uppercase; letter-spacing: 1px; color: #c2410c; font-weight: bold;">Fourchette Budgétaire Clés en Main :</small>
        <h2 style="color: #c2410c; margin: 8px 0; font-size: 28px; font-weight: bold;">
          ${result.totalMin.toLocaleString('fr-FR')} - ${result.totalMax.toLocaleString('fr-FR')} FCFA
        </h2>
        <span style="font-size: 14px; color: #64748b; font-weight: bold;">
          Soit environ ${result.totalMinEUR.toLocaleString('fr-FR')} € à ${result.totalMaxEUR.toLocaleString('fr-FR')} €
        </span>
      </div>

      <h4 style="color: #0f172a; margin-bottom: 12px; font-size: 15px;">Quantités Clés Estimées & Planning :</h4>
      <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 25px;">
        <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
          <th style="padding: 8px; text-align: left;">Poste / Ressource</th>
          <th style="padding: 8px; text-align: right;">Quantité Estimée</th>
          <th style="padding: 8px; text-align: left;">Spécification Technique RMC BTP</th>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
          <td style="padding: 8px;">Ciment CPJ 42.5</td>
          <td style="padding: 8px; text-align: right; font-weight: bold;">~ ${result.cementBags} sacs</td>
          <td style="padding: 8px; color: #64748b;">Dosage standard 350 kg/m³ de béton (SIMS Quincaillerie)</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
          <td style="padding: 8px;">Aciers Haute Adhérence (HA)</td>
          <td style="padding: 8px; text-align: right; font-weight: bold;">~ ${result.steelTons} tonnes</td>
          <td style="padding: 8px; color: #64748b;">FeE500 certifié conforme Eurocodes & MINTP</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
          <td style="padding: 8px;">Béton Armé Banché & Poteaux</td>
          <td style="padding: 8px; text-align: right; font-weight: bold;">~ ${result.concreteM3} m³</td>
          <td style="padding: 8px; color: #64748b;">Vibration mécanique et éprouvettes de contrôle</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
          <td style="padding: 8px;">Délai Prévisionnel d'Exécution</td>
          <td style="padding: 8px; text-align: right; font-weight: bold;">${result.durationMonths} Mois</td>
          <td style="padding: 8px; color: #64748b;">Rétro-planning contractuel avec pénalités de retard</td>
        </tr>
      </table>

      <div style="font-size: 11px; color: #64748b; line-height: 1.5; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
        * Cette cotation est une estimation préliminaire non contractuelle basée sur les barèmes moyens du bâtiment au Cameroun. Une visite technique du site et l'analyse de vos plans d'architecte permettront de fixer le décompte définitif sans dépassement.
      </div>

      <div style="margin-top: 35px; text-align: right;">
        <p style="margin: 0; font-size: 12px; font-weight: bold; color: #0c2e60;">Direction Technique & Métrés</p>
        <p style="margin: 2px 0 0; font-size: 12px; color: #d97706; font-weight: bold;">RMC BTP SARL &bull; Yaoundé & Douala</p>
      </div>
    `;

    document.body.appendChild(printArea);

    if (typeof html2pdf !== 'undefined') {
      const opt = {
        margin:       10,
        filename:     `Devis_Estimatif_RMC_BTP_${state.surface}m2.pdf`,
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

  function initWizard() {
    // Écouteurs de clics sur les cartes de choix
    document.querySelectorAll('.fee-wizard-card[data-wiz-type]').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.fee-wizard-card[data-wiz-type]').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        state.projectType = card.getAttribute('data-wiz-type');
        updateUI();
      });
    });

    document.querySelectorAll('.fee-wizard-card[data-wiz-standing]').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.fee-wizard-card[data-wiz-standing]').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        state.standing = card.getAttribute('data-wiz-standing');
        updateUI();
      });
    });

    document.querySelectorAll('.fee-wizard-card[data-wiz-terrain]').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.fee-wizard-card[data-wiz-terrain]').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        state.terrain = card.getAttribute('data-wiz-terrain');
        updateUI();
      });
    });

    // Slider de surface
    const surfaceInput = document.getElementById('feewizSurfaceRange');
    if (surfaceInput) {
      surfaceInput.addEventListener('input', (e) => {
        state.surface = parseInt(e.target.value, 10);
        updateUI();
      });
    }

    // Checkboxes des options
    ['forage', 'fence', 'solar'].forEach(opt => {
      const chk = document.getElementById(`feewizOpt_${opt}`);
      if (chk) {
        chk.addEventListener('change', (e) => {
          state.options[opt] = e.target.checked;
          updateUI();
        });
      }
    });

    // Navigation Étapes Suivant / Précédent
    document.querySelectorAll('[data-wiz-next]').forEach(btn => {
      btn.addEventListener('click', () => {
        switchStep(state.step + 1);
      });
    });

    document.querySelectorAll('[data-wiz-prev]').forEach(btn => {
      btn.addEventListener('click', () => {
        switchStep(state.step - 1);
      });
    });

    document.querySelectorAll('.fee-wizard-tab').forEach((tab, index) => {
      tab.addEventListener('click', () => {
        switchStep(index + 1);
      });
    });

    // Bouton Téléchargement Devis PDF (Façade d'accroche -> Redirection Immédiate WhatsApp)
    const btnPdf = document.getElementById('feewizBtnPdf');
    if (btnPdf) {
      btnPdf.addEventListener('click', (e) => {
        e.preventDefault();
        const result = calculateQuote();
        const optionsList = [
          state.options.forage ? "Forage avec château d'eau" : "",
          state.options.fence ? "Clôture sécurisée maçonnée" : "",
          state.options.solar ? "Centrale solaire hybride" : ""
        ].filter(Boolean).join(", ") || "Gros œuvre & Second œuvre standard";

        const msg = `Bonjour RMC BTP SARL ! J'ai configuré mon projet sur votre simulateur en ligne et je souhaite recevoir ma Fiche Estimative Officielle en format PDF :
• Type d'ouvrage : ${result.baseName}
• Superficie couverte : ${state.surface} m²
• Niveau de standing : ${result.standingName}
• Configuration terrain : ${result.terrainName}
• Options retenues : ${optionsList}
• Fourchette budgétaire estimée : ${result.totalMin.toLocaleString('fr-FR')} à ${result.totalMax.toLocaleString('fr-FR')} FCFA (~${result.totalMinEUR.toLocaleString('fr-FR')} € à ${result.totalMaxEUR.toLocaleString('fr-FR')} €)
• Délai prévisionnel : ${result.durationMonths} Mois

Pouvez-vous me transmettre la fiche de cotation détaillée complète en PDF par WhatsApp ? Merci d'avance !`;

        window.open(`https://wa.me/237697398455?text=${encodeURIComponent(msg)}`, '_blank');
      });
    }

    updateUI();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWizard);
  } else {
    initWizard();
  }
})();
