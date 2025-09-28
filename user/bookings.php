<?php
require __DIR__ . '/../db/config.php';

// check user login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: /renteasy/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT b.*, p.title, p.location, p.type 
                       FROM bookings b 
                       JOIN properties p ON p.id=b.property_id 
                       WHERE b.user_id=? 
                       ORDER BY b.created_at DESC");
$stmt->execute(array($_SESSION['user_id']));
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h2>My Bookings</h2>
<table class="table table-striped">
  <thead>
    <tr><th>Property</th><th>Type</th><th>Location</th><th>Status</th><th>Requested</th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?php echo htmlspecialchars($r['title']); ?></td>
      <td><?php echo htmlspecialchars($r['type']); ?></td>
      <td><?php echo htmlspecialchars($r['location']); ?></td>
      <td><?php echo htmlspecialchars($r['status']); ?></td>
      <td><?php echo htmlspecialchars($r['created_at']); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
