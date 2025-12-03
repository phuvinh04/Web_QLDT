      <!-- Footer -->
      <p class="auth-footer-text">
        &copy; 2024 PhoneStore Management System
      </p>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Common JS for Auth pages (Toggle Password)
  document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.querySelector('#togglePassword');
      const password = document.querySelector('#password');

      if (togglePassword && password) {
          togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
          });
      }

      const inputs = document.querySelectorAll('.form-control');
      inputs.forEach(input => {
          input.addEventListener('input', function() {
              this.classList.remove('is-invalid');
          });
      });
  });
</script>
</body>
</html>
