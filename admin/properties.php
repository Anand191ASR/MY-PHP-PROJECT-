<?php
session_start();
require __DIR__ . '/../db/config.php';

// ✅ Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ Delete property
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM properties WHERE id=?")->execute([$id]);
    header("Location: properties.php");
    exit;
}

// ✅ Approve property
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE properties SET status='approved' WHERE id=?")->execute([$id]);
    header("Location: properties.php");
    exit;
}

// ✅ Reject property
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $pdo->prepare("UPDATE properties SET status='rejected' WHERE id=?")->execute([$id]);
    header("Location: properties.php");
    exit;
}

// ✅ Fetch all properties with owner info
$props = $pdo->query("
    SELECT p.*, o.name AS owner_name 
    FROM properties p 
    LEFT JOIN owners o ON p.owner_id = o.id 
    ORDER BY p.created_at DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Manage Properties</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Title</th>
        <th>Type</th>
        <th>Location</th>
        <th>Owner</th>
        <th>Status</th>
        <th>Price</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($props as $p): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['title']); ?></td>
          <td><?php echo htmlspecialchars($p['type']); ?></td>
          <td><?php echo htmlspecialchars($p['location']); ?></td>
          <td><?php echo htmlspecialchars($p['owner_name']); ?></td>
          <td>
            <span class="badge bg-<?php 
              echo $p['status']=='approved'?'success':($p['status']=='rejected'?'danger':'secondary'); ?>">
              <?php echo htmlspecialchars(ucfirst($p['status'])); ?>
            </span>
          </td>
          <td>₹<?php echo htmlspecialchars($p['price']); ?></td>
          <td><?php echo htmlspecialchars($p['created_at']); ?></td>
          <td>
            <?php if ($p['status'] === 'pending'): ?>
              <a href="?approve=<?php echo $p['id']; ?>" class="btn btn-sm btn-success">Approve</a>
              <a href="?reject=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">Reject</a>
            <?php endif; ?>
            <a href="?delete=<?php echo $p['id']; ?>" 
               class="btn btn-sm btn-danger" 
               onclick="return confirm('Are you sure you want to delete this property?');">
               Delete
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
