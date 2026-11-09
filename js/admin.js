/**
 * RMC BTP SARL - Administration Dashboard Helpers
 * Sidebar Mobile Toggle, Backdrop, Auto-Dismiss Alerts & Visual Previews
 */
(function() {
  'use strict';

  // 1. Navigation Menu Mobile / Tablette
  function initMobileSidebar() {
    const toggleBtn = document.getElementById('adminSidebarToggle') || document.querySelector('.admin-sidebar-toggle');
    const sidebar = document.getElementById('adminSidebar') || document.querySelector('.admin-sidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop') || document.querySelector('.admin-sidebar-backdrop');
    const closeBtn = document.getElementById('sidebarCloseBtn') || document.querySelector('.sidebar-close-btn');

    function openSidebar() {
      if (sidebar) sidebar.classList.add('open');
      if (backdrop) backdrop.classList.add('active');
      document.body.classList.add('sidebar-locked');
    }

    function closeSidebar() {
      if (sidebar) sidebar.classList.remove('open');
      if (backdrop) backdrop.classList.remove('active');
      document.body.classList.remove('sidebar-locked');
    }

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (sidebar && sidebar.classList.contains('open')) {
          closeSidebar();
        } else {
          openSidebar();
        }
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closeSidebar();
      });
    }

    if (backdrop) {
      backdrop.addEventListener('click', function(e) {
        e.preventDefault();
        closeSidebar();
      });
    }

    // Fermeture avec la touche Échap
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
        closeSidebar();
      }
    });

    // Fermeture automatique lors du clic sur un lien du menu sur écran mobile
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
          closeSidebar();
        }
      });
    });
  }

  // Initialisation dès que le DOM est prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileSidebar);
  } else {
    initMobileSidebar();
  }

  // 2. Disparition automatique fluide des alertes après 5 secondes
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

  // 3. Prévisualisation en direct de l'image téléversée (Utilitaires visuels)
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
