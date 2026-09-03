<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Page Not Found | Findownn Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;800&display=swap" rel="stylesheet">
  <style>
    body { background:#080c09; color:#f0fdf4; font-family:'Plus Jakarta Sans',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
    .error-box { text-align:center; padding:40px 20px; }
    .error-code { font-size:7rem; font-weight:800; background:linear-gradient(135deg,#dcfce7,#3887C6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; line-height:1; }
    .error-title { font-size:1.5rem; font-weight:700; margin:16px 0 8px; }
    .error-sub { color:#86a892; margin-bottom:32px; }
    .btn-go { background:linear-gradient(135deg,#3887C6,#2a6ba0); color:#fff; border:none; padding:12px 32px; border-radius:50px; font-weight:700; text-decoration:none; }
    .btn-go:hover { color:#fff; opacity:.9; }
  </style>
</head>
<body>
  <div class="error-box">
    <div class="error-code">404</div>
    <h1 class="error-title">Page Not Found</h1>
    <p class="error-sub">The page you're looking for doesn't exist or was moved.</p>
    <a href="<?= url('/dashboard') ?>" class="btn-go"><i class="bi bi-house me-2"></i>Back to Dashboard</a>
  </div>
</body>
</html>
