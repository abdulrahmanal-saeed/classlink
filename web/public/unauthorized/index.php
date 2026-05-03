<?php
/**
 * /unauthorized
 *
 * Safe page shown when a logged-in user attempts to access another role dashboard.
 */
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Unauthorized | Habiba Nabil Arabic Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
  <main class="app-shell">
    <section class="foundation-card text-center" style="max-width: 620px;">
      <div class="brand-mark mx-auto mb-3">ض</div>
      <h1 class="hero-title h3">Unauthorized access</h1>
      <p class="text-muted">You do not have permission to open this dashboard.</p>
      <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a class="btn btn-brand" href="/login">Go to login</a>
        <a class="btn btn-outline-brand" href="/logout">Logout</a>
      </div>
    </section>
  </main>
</body>
</html>
