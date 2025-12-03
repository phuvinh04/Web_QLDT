// ===================================
// MAIN JAVASCRIPT - Placeholder
// Sẽ xử lý PHP và JS sau
// ===================================

console.log('Quản lý cửa hàng điện thoại - UI Ready');

// Highlight active menu
document.addEventListener('DOMContentLoaded', function() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const menuLinks = document.querySelectorAll('.sidebar-menu a');
  
  menuLinks.forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });

  // Sidebar Toggle Logic (Mobile)
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  
  // Create overlay for mobile
  const overlay = document.createElement('div');
  overlay.className = 'sidebar-overlay';
  document.body.appendChild(overlay);

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('show');
      if (window.innerWidth <= 768) {
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
      }
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      overlay.style.display = 'none';
    });
  }
});
