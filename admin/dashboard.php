<?php
session_start();
require __DIR__ . '/../db/config.php';

// ✅ check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /renteasy/login.php');
    exit;
}

// ✅ Approve / Reject / Delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_GET['action'] === 'approve') {
        $status = 'approved';
        $stmt = $pdo->prepare("UPDATE properties SET status=? WHERE id=?");
        $stmt->execute(array($status, $id));

    } elseif ($_GET['action'] === 'reject') {
        $status = 'rejected';
        $stmt = $pdo->prepare("UPDATE properties SET status=? WHERE id=?");
        $stmt->execute(array($status, $id));

    } elseif ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id=?");
        $stmt->execute(array($id));
    }

    header('Location: /renteasy/admin/dashboard.php');
    exit;
}

// ✅ Fetch stats
$totalUsers  = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch();
$totalOwners = $pdo->query("SELECT COUNT(*) AS c FROM owners")->fetch();
$totalProps  = $pdo->query("SELECT COUNT(*) AS c FROM properties")->fetch();

$totalUsers  = isset($totalUsers['c']) ? $totalUsers['c'] : 0;
$totalOwners = isset($totalOwners['c']) ? $totalOwners['c'] : 0;
$totalProps  = isset($totalProps['c']) ? $totalProps['c'] : 0;

// ✅ Fetch pending properties
$pending = $pdo->query("SELECT p.*, o.name AS owner_name 
                        FROM properties p 
                        JOIN owners o ON o.id=p.owner_id 
                        WHERE p.status='pending' 
                        ORDER BY p.created_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <h2 class="mb-4">Admin Dashboard</h2>

  <!-- Stats -->
  <div class="row g-3 my-2">
    <div class="col-md-4">
      <div class="card shadow-sm"><div class="card-body text-center">
        <h5 class="card-title">Users</h5>
        <p class="display-6 mb-0"><?php echo $totalUsers; ?></p>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm"><div class="card-body text-center">
        <h5 class="card-title">Owners</h5>
        <p class="display-6 mb-0"><?php echo $totalOwners; ?></p>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm"><div class="card-body text-center">
        <h5 class="card-title">Properties</h5>
        <p class="display-6 mb-0"><?php echo $totalProps; ?></p>
      </div></div>
    </div>
  </div>

  <!-- Pending approvals -->
  <h4 class="mt-4">Pending Property Approvals</h4>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>Title</th>
        <th>Owner</th>
        <th>Type</th>
        <th>Location</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($pending): ?>
        <?php foreach ($pending as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
            <td><?php echo htmlspecialchars($row['type']); ?></td>
            <td><?php echo htmlspecialchars($row['location']); ?></td>
            <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
            <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['status']); ?></span></td>
            <td>
              <a class="btn btn-sm btn-success" href="?action=approve&id=<?php echo $row['id']; ?>">Approve</a>
              <a class="btn btn-sm btn-danger" href="?action=reject&id=<?php echo $row['id']; ?>">Reject</a>
              <a class="btn btn-sm btn-outline-dark" href="?action=delete&id=<?php echo $row['id']; ?>" 
                 onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center text-muted">No pending properties</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

    <div class="mt-4">
    <a href="/renteasy/index.php" class="btn btn-secondary">⬅ Back to Home</a>
    <a href="/renteasy/admin/properties.php" class="btn btn-primary">Manage Properties</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
