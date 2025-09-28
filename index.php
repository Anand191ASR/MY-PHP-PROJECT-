<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/includes/header.php';
?>
<style>
  body {
    background: url('https://th.bing.com/th/id/R.d5c5f2246919265f53e6f56f80071dd3?rik=Q7O1od8exJDnbQ&riu=http%3a%2f%2fcdn.wallpapersafari.com%2f37%2f82%2fBI78kU.jpg&ehk=yP98fVqkFdzsxnJdZ17FmIQKByMdglW%2bEUXxXB7Mf6o%3d&risl=&pid=ImgRaw&r=0') 
                no-repeat center center fixed;
    background-size: cover;
  }
  .overlay {
    background-color: rgba(0,0,0,0.6);
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
    border-radius: 12px;
    padding: 40px;
  }
</style>

<div class="overlay">
  <div>
    <h1 class="display-3 fw-bold">Welcome to RentEasy</h1>
    <p class="lead">Browse and book Hotels, PGs, and Apartments. Owners can list their properties. Admins manage it all.</p>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <p>You are logged in as <strong>admin</strong>.</p>
      <a href="/renteasy/admin/dashboard.php" class="btn btn-warning btn-lg">Admin Dashboard</a>

    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
      <p>You are logged in as <strong>owner</strong>.</p>
      <a href="/renteasy/owner/manage.php" class="btn btn-success btn-lg">Manage Properties</a>

    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
      <p>You are logged in as <strong>user</strong>.</p>
      <a href="/renteasy/user/browse.php" class="btn btn-primary btn-lg">Browse Properties</a>
      <a href="/renteasy/user/bookings.php" class="btn btn-info btn-lg ms-2">My Bookings</a>

    <?php else: ?>
      <a href="/renteasy/login.php" class="btn btn-outline-light btn-lg">Login</a>
      <a href="/renteasy/register.php" class="btn btn-warning btn-lg ms-2">Register</a>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
