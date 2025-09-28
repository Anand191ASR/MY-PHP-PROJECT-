<?php
require __DIR__ . '/../db/config.php';

// check owner login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header('Location: /renteasy/login.php');
    exit;
}

$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $type = isset($_POST['type']) ? $_POST['type'] : 'Apartment';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
    $owner_id = isset($_SESSION['owner_id']) ? $_SESSION['owner_id'] : 0;

    if (!$title || !$location || $price <= 0) {
        $error = 'Please fill required fields';
    } else {
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            $upDir = __DIR__ . '/../uploads/';
            if (!is_dir($upDir)) {
                @mkdir($upDir, 0777, true);
            }
            $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','_', $_FILES['image']['name']);
            $dest = $upDir . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = '/renteasy/uploads/' . $fname;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO properties (owner_id,title,type,location,description,price,image_path) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute(array($owner_id,$title,$type,$location,$desc,$price,$image_path));
        $msg = 'Property submitted for approval';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h2>Add Property</h2>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card p-3">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select">
        <option>Hotel</option>
        <option>PG</option>
        <option>Apartment</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Price (per night/month)</label>
      <input type="number" name="price" min="1" step="0.01" class="form-control" required>
    </div>
    <div class="col-md-8">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Image</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"></textarea>
    </div>
  </div>
  <button class="btn btn-primary mt-3">Submit</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
