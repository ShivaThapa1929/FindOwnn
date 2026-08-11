<?php
/** @var string $content Rendered view output from Controller::render() */
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($title ?? 'Findownn Admin') ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e(url('/public/assets/images/favicon-32x32.png') . '?v=6') ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e(url('/public/assets/images/favicon-16x16.png') . '?v=6') ?>">
  <link rel="shortcut icon" href="<?= e(url('/public/assets/images/favicon-32x32.png') . '?v=6') ?>">
  <link rel="apple-touch-icon" href="<?= e(url('/public/assets/images/apple-touch-icon.png') . '?v=6') ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="<?= url('/public/assets/css/admin.css') ?>?v=2.7" rel="stylesheet">
</head>
<body class="auth-body">
  <?= $content ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
