<?php
/**
 * /login
 *
 * Public login page with client-side loading/error states.
 * The real security check happens in POST /api/auth/login.
 */
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Habiba Nabil Arabic Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
  <main class="app-shell">
    <section class="foundation-card" style="max-width: 520px;">
      <div class="text-center mb-4">
        <div class="brand-mark mx-auto mb-3">ض</div>
        <h1 class="hero-title h3">Login</h1>
        <p class="text-muted mb-0">Access your dashboard securely.</p>
      </div>

      <div id="loginAlert" class="alert alert-danger d-none" role="alert"></div>

      <form id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input class="form-control" type="email" id="email" name="email" placeholder="owner@demo.com" required>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" type="password" id="password" name="password" placeholder="demo password" required>
          <div class="invalid-feedback">Password is required.</div>
        </div>

        <button id="loginButton" class="btn btn-brand w-100" type="submit">
          <span class="button-text">Login</span>
          <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
        </button>
      </form>

      <div class="mt-4 small text-muted">
        <div class="fw-bold mb-2">Demo accounts password: <code>demo password</code></div>
        <ul class="mb-0">
          <li>owner@demo.com</li>
          <li>adult.student@demo.com</li>
          <li>parent@demo.com</li>
          <li>academy@demo.com</li>
        </ul>
      </div>
    </section>
  </main>

  <script>
    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('loginAlert');
    const button = document.getElementById('loginButton');
    const buttonText = button.querySelector('.button-text');
    const spinner = button.querySelector('.spinner-border');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      alertBox.classList.add('d-none');

      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      button.disabled = true;
      buttonText.textContent = 'Loading...';
      spinner.classList.remove('d-none');

      try {
        const response = await fetch('/api/auth/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
          })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Login failed.');
        }

        window.location.href = result.data.redirect;
      } catch (error) {
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
      } finally {
        button.disabled = false;
        buttonText.textContent = 'Login';
        spinner.classList.add('d-none');
      }
    });
  </script>
</body>
</html>
