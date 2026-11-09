/**
 * FEE BTP SARL - Réticule d'Ingénieur & Géomètre BTP (Micro-interactions)
 * Curseur haute précision fluide et magnétique sur éléments interactifs
 */

(function() {
  'use strict';

  // Ne pas activer sur écrans tactiles
  if ('ontouchstart' in window || navigator.maxTouchPoints > 0) return;

  const reticule = document.createElement('div');
  reticule.id = 'feeArchitectReticule';

  const dot = document.createElement('div');
  dot.id = 'feeArchitectReticuleDot';

  document.body.appendChild(reticule);
  document.body.appendChild(dot);

  let mouseX = -100, mouseY = -100;
  let reticuleX = -100, reticuleY = -100;
  let isVisible = false;

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!isVisible) {
      reticule.style.display = 'block';
      dot.style.display = 'block';
      isVisible = true;
    }
    dot.style.transform = `translate(${mouseX}px, ${mouseY}px) translate(-50%, -50%)`;
  });

  function renderCursor() {
    // Lerp pour effet de suivi fluide
    reticuleX += (mouseX - reticuleX) * 0.18;
    reticuleY += (mouseY - reticuleY) * 0.18;

    reticule.style.transform = `translate(${reticuleX}px, ${reticuleY}px) translate(-50%, -50%)`;
    requestAnimationFrame(renderCursor);
  }
  requestAnimationFrame(renderCursor);

  // Agrandissement magnétique au survol des liens et boutons
  function bindHoverEvents() {
    const targets = document.querySelectorAll('a, button, .btn, .fee-wizard-card, .fee-tracker-photo-thumb, input[type="range"]');
    targets.forEach(el => {
      el.addEventListener('mouseenter', () => {
        reticule.classList.add('hover-active');
      });
      el.addEventListener('mouseleave', () => {
        reticule.classList.remove('hover-active');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindHoverEvents);
  } else {
    bindHoverEvents();
  }
})();
