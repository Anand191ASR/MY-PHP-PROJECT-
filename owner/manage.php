<?php
session_start();
require __DIR__ . '/../db/config.php';

// check owner login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: /renteasy/login.php');
    exit;
}

// owner id from session (PHP7 compatible)
$owner_id = isset($_SESSION['owner_id']) ? intval($_SESSION['owner_id']) : 0;

// ----------------- Delete property (owner only) -----------------
if (isset($_GET['delete'])) {
    $pid = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
    $stmt->execute([$pid, $owner_id]);
    header('Location: /renteasy/owner/manage.php');
    exit;
}

// ----------------- Approve/Reject bookings -----------------
if (isset($_GET['booking']) && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'approve' || $action === 'reject') {
        $bid = (int) $_GET['booking'];
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $sql = "UPDATE bookings 
                SET status = ? 
                WHERE id = ? AND property_id IN (SELECT id FROM properties WHERE owner_id = ?)";
        $pdo->prepare($sql)->execute([$status, $bid, $owner_id]);
        header('Location: /renteasy/owner/manage.php');
        exit;
    }
}

// ----------------- Fetch properties -----------------
$props_stmt = $pdo->prepare("SELECT * FROM properties WHERE owner_id = ? ORDER BY created_at DESC");
$props_stmt->execute([$owner_id]);
$props = $props_stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------- Fetch bookings for owner's properties -----------------
$bookings_stmt = $pdo->prepare(
    "SELECT b.*, 
            u.name AS user_name, 
            p.title AS property_title,
            TIMESTAMPDIFF(MONTH, b.start_date, b.end_date) AS months_diff,
            TIMESTAMPDIFF(YEAR, b.start_date, b.end_date) AS years_diff
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN properties p ON p.id = b.property_id
     WHERE p.owner_id = ?
     ORDER BY b.created_at DESC"
);
$bookings_stmt->execute([$owner_id]);
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<h2>My Listings</h2>
<a class="btn btn-primary mb-3" href="/renteasy/owner/add_property.php">Add New Property</a>

<table class="table table-bordered">
  <thead>
    <tr><th>Title</th><th>Status</th><th>Price</th><th>Created</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php if ($props && count($props) > 0): ?>
      <?php foreach ($props as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['title']); ?></td>
          <td>
            <span class="badge <?= ($p['status'] === 'approved') ? 'bg-success' : (($p['status'] === 'rejected') ? 'bg-danger' : 'bg-secondary') ?>">
              <?= htmlspecialchars($p['status']) ?>
            </span>
          </td>
          <td>₹<?= number_format($p['price'], 2) ?></td>
          <td><?= htmlspecialchars($p['created_at']); ?></td>
          <td>
            <a class="btn btn-sm btn-warning" href="/renteasy/owner/edit_property.php?id=<?= (int)$p['id'] ?>">Edit</a>
            <a class="btn btn-sm btn-danger" href="?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this property?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="5">No properties found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<h2 class="mt-4">Booking Requests</h2>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Property</th>
      <th>User</th>
      <th>Status</th>
      <th>Start Date</th>
      <th>End Date</th>
      <th>Duration</th>
      <th>Requested At</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($bookings && count($bookings) > 0): ?>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['property_title']); ?></td>
          <td><?= htmlspecialchars($b['user_name']); ?></td>
          <td><?= htmlspecialchars($b['status']); ?></td>
          <td><?= htmlspecialchars($b['start_date']); ?></td>
          <td><?= htmlspecialchars($b['end_date']); ?></td>
          <td>
            <?php
              if ($b['years_diff'] > 0) {
                  echo $b['years_diff'] . " year(s)";
              } elseif ($b['months_diff'] > 0) {
                  echo $b['months_diff'] . " month(s)";
              } else {
                  echo "Less than 1 month";
              }
            ?>
          </td>
          <td><?= htmlspecialchars($b['created_at']); ?></td>
          <td>
            <?php if ($b['status'] === 'pending'): ?>
              <a class="btn btn-sm btn-success" href="?booking=<?= (int)$b['id'] ?>&action=approve">Approve</a>
              <a class="btn btn-sm btn-danger" href="?booking=<?= (int)$b['id'] ?>&action=reject">Reject</a>
            <?php else: ?>
              <span class="text-muted">No actions</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="8">No booking requests yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
