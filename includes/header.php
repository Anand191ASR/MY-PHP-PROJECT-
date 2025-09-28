<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RentEasy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
      <a class="navbar-brand" href="/renteasy/index.php">RentEasy</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarsExample">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="/renteasy/index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/renteasy/user/browse.php">Browse</a></li>
        </ul>
        <ul class="navbar-nav">
          <?php if (!empty($_SESSION['role'])): ?>
            <li class="nav-item">
              <span class="navbar-text me-3">
                Hi, <?php echo htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['role']); ?>
              </span>
            </li>
            <li class="nav-item">
              <a class="btn btn-outline-light" href="/renteasy/logout.php">Logout</a>
            </li>
          <?php else: ?>
            <li class="nav-item me-2">
              <a class="btn btn-outline-light" href="/renteasy/login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-warning" href="/renteasy/register.php">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <main class="container">
