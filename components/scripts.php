<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo $base_url; ?>assets/js/main.js"></script>
<script>
// Fix modal centering - remove Bootstrap's padding adjustment
document.addEventListener('DOMContentLoaded', function() {
  // Override Bootstrap's modal padding adjustment
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'style') {
        document.body.style.paddingRight = '0';
      }
    });
  });
  
  observer.observe(document.body, { attributes: true });
  
  // Fix all modals
  document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('show.bs.modal', function() {
      setTimeout(function() {
        document.body.style.paddingRight = '0';
        modal.style.paddingLeft = '0';
      }, 0);
    });
  });
});
</script>
