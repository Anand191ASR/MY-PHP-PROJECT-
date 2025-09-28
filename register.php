<?php
require __DIR__ . '/db/config.php';

$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // user | owner

    if (!$name || !$email || !$password) {
        $error = 'All fields are required';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            if ($role === 'owner') {
                $stmt = $pdo->prepare("INSERT INTO owners (name,email,password_hash) VALUES (?,?,?)");
                $stmt->execute(array($name,$email,$hash));
                // $_SESSION['role']='owner'; 
                // $_SESSION['name']=$name; 
                // $_SESSION['owner_id']=$pdo->lastInsertId();
                header('Location: /renteasy/login.php'); 
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
                $stmt->execute(array($name,$email,$hash));
                // $_SESSION['role']='user'; 
                // $_SESSION['name']=$name; 
                // $_SESSION['user_id']=$pdo->lastInsertId();
                header('Location: /renteasy/login.php'); 
                exit;
            }
        } catch (Exception $e) {
            $error = 'Registration error: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h2>Register</h2>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Register as</label>
          <select name="role" class="form-select">
            <option value="user">User</option>
            <option value="owner">Owner</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary">Create Account</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
