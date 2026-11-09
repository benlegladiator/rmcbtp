/**
 * RMC BTP SARL - Administration Dashboard Helpers
 * Sidebar Toggle, Notification Auto-Dismiss & Image Preview Utilities
 */
(function() {
  'use strict';

  // 1. Toggle Sidebar sur Mobile/Tablette
  const toggleBtn = document.querySelector('.admin-sidebar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
      if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // 2. Disparition automatique fluide des alertes de succès après 5 secondes
  const alerts = document.querySelectorAll('.alert-success');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      setTimeout(function() {
        if (alert.parentNode) alert.parentNode.removeChild(alert);
      }, 600);
    }, 5000);
  });

  // 3. Prévisualisation en direct de l'image téléversée
  window.previewVisual = window.previewVisual || function(input, imgId) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.getElementById(imgId);
        if (img) img.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    }
  };

  // 4. Mise à jour instantanée du cadrage
  window.updateFocalPreview = window.updateFocalPreview || function(focal, imgId) {
    const img = document.getElementById(imgId);
    if (img) img.style.objectPosition = focal;
  };
})();
