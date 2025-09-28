<?php
session_start();
require __DIR__ . '/../db/config.php';

// check owner login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: /renteasy/login.php');
    exit;
}

$owner_id = isset($_SESSION['owner_id']) ? intval($_SESSION['owner_id']) : 0;
$pid = isset($_GET['id']) ? intval($_GET['id']) : 0;

// fetch property (only if it belongs to logged owner)
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ?");
$stmt->execute(array($pid, $owner_id));
$prop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prop) {
    die("Property not found or access denied.");
}

$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : 'Apartment';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($title === '' || $location === '' || $price <= 0) {
        $error = 'Please fill required fields (title, location, price).';
    } else {
        // Keep existing image by default
        $image_path = isset($prop['image_path']) ? $prop['image_path'] : null;

        // Handle image upload if new file provided
        if (isset($_FILES['image']) && isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
            $upDir = __DIR__ . '/../uploads/';
            if (!is_dir($upDir)) {
                @mkdir($upDir, 0777, true);
            }

            // sanitize filename
            $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['image']['name']);
            $dest = $upDir . $fname;

            // basic validation: file size and type (optional, small checks)
            $maxBytes = 5 * 1024 * 1024; // 5 MB
            $allowed = array('image/jpeg', 'image/png', 'image/gif');

            if ($_FILES['image']['size'] > $maxBytes) {
                $error = 'Image is too large (max 5 MB).';
            } elseif (!in_array($_FILES['image']['type'], $allowed)) {
                $error = 'Invalid image type. Use JPG/PNG/GIF.';
            } else {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    // URL path used by other pages
                    $image_path = '/renteasy/uploads/' . $fname;
                } else {
                    $error = 'Failed to move uploaded file.';
                }
            }
        }

        if ($error === '') {
            try {
                // Update and reset status to pending so admin must approve edits
                $sql = "UPDATE properties
                        SET title = ?, type = ?, location = ?, description = ?, price = ?, image_path = ?, status = 'pending'
                        WHERE id = ? AND owner_id = ?";
                $pdo->prepare($sql)->execute(array($title, $type, $location, $desc, $price, $image_path, $pid, $owner_id));

                $msg = 'Property updated and sent for re-approval.';

                // reload updated property values
                $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ?");
                $stmt->execute(array($pid, $owner_id));
                $prop = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h2>Edit Property</h2>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($msg !== ''): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars(isset($prop['title']) ? $prop['title'] : ''); ?>">
    </div>

    <div class="col-md-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select">
        <option <?php echo (isset($prop['type']) && $prop['type'] === 'Hotel') ? 'selected' : ''; ?>>Hotel</option>
        <option <?php echo (isset($prop['type']) && $prop['type'] === 'PG') ? 'selected' : ''; ?>>PG</option>
        <option <?php echo (isset($prop['type']) && $prop['type'] === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Price (per night/month)</label>
      <input type="number" name="price" min="1" step="0.01" class="form-control" required value="<?php echo htmlspecialchars(isset($prop['price']) ? $prop['price'] : ''); ?>">
    </div>

    <div class="col-md-8">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" required value="<?php echo htmlspecialchars(isset($prop['location']) ? $prop['location'] : ''); ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Image (leave empty to keep current)</label>
      <input type="file" name="image" class="form-control" accept="image/*">
      <?php if (isset($prop['image_path']) && $prop['image_path']): ?>
        <div class="mt-2">
          <img src="<?php echo htmlspecialchars($prop['image_path']); ?>" alt="current image" style="max-width:120px;">
        </div>
      <?php endif; ?>
    </div>

    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars(isset($prop['description']) ? $prop['description'] : ''); ?></textarea>
    </div>
  </div>

  <button class="btn btn-primary mt-3">Update</button>
  <a href="/renteasy/owner/manage.php" class="btn btn-secondary mt-3">Back</a>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
