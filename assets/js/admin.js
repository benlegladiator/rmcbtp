/**
 * RMC BTP SARL - Administration Dashboard Helpers
 */
(function() {
  'use strict';

  // Toggle Sidebar sur Mobile/Tablette
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

  // Disparition automatique fluide des alertes de succès après 5 secondes
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
})();
