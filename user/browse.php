<?php
session_start();
require __DIR__ . '/../db/config.php';

// filters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

// build query
$qry = "SELECT p.*, u.name AS owner_name 
        FROM properties p 
        JOIN users u ON u.id = p.owner_id 
        WHERE p.status = 'approved'";
$params = [];

if ($type && in_array($type, ['Hotel','PG','Apartment'])) {
    $qry .= " AND p.type = ?";
    $params[] = $type;
}
if ($location) {
    $qry .= " AND p.location LIKE ?";
    $params[] = "%$location%";
}
$qry .= " ORDER BY p.created_at DESC";

// execute
$stmt = $pdo->prepare($qry);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2 class="mb-3">Browse Properties</h2>

  <!-- Filter Form -->
  <form class="row g-3 mb-4" method="get">
    <div class="col-md-3">
      <select name="type" class="form-select">
        <option value="">All Types</option>
        <?php foreach (['Hotel','PG','Apartment'] as $t): ?>
          <option value="<?php echo $t; ?>" <?php echo ($type === $t ? 'selected' : ''); ?>>
            <?php echo $t; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <input type="text" name="location" placeholder="Search by location..." 
             class="form-control" value="<?php echo htmlspecialchars($location); ?>">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <!-- Properties List -->
  <div class="row g-3">
  <?php if ($rows): ?>
    <?php foreach ($rows as $r): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <?php if (!empty($r['image_path'])): ?>
            <img class="card-img-top" src="<?php echo htmlspecialchars($r['image_path']); ?>" alt="">
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($r['title']); ?></h5>
            <p class="card-text mb-1"><strong>Type:</strong> <?php echo htmlspecialchars($r['type']); ?></p>
            <p class="card-text mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($r['location']); ?></p>
            <p class="card-text mb-1"><strong>Owner:</strong> <?php echo htmlspecialchars($r['owner_name']); ?></p>
            <p class="card-text"><strong>Price:</strong> ₹<?php echo htmlspecialchars($r['price']); ?></p>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
              <!-- Booking Form -->
              <form method="post" action="/renteasy/user/book.php" class="d-grid">
                <input type="hidden" name="property_id" value="<?php echo $r['id']; ?>">

                <label class="form-label mt-2">Start Date</label>
                <input type="date" name="start_date" class="form-control mb-2" required>

                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control mb-2" required>

                <button class="btn btn-success">Request Booking</button>
              </form>
            <?php else: ?>
              <a href="/renteasy/login.php" class="btn btn-outline-secondary w-100">
                Login to Book
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-muted">No properties found.</p>
  <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
