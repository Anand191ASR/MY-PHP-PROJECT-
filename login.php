<?php
require __DIR__ . '/db/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // user | owner | admin

    try {
        if ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute(array($email));
            $row = $stmt->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['role'] = 'admin';
                $_SESSION['name'] = $row['username'];
                $_SESSION['admin_id'] = $row['id'];
                header('Location: /renteasy/admin/dashboard.php');
                exit;
            }
        } elseif ($role === 'owner') {
            $stmt = $pdo->prepare("SELECT * FROM owners WHERE email = ?");
            $stmt->execute(array($email));
            $row = $stmt->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['role'] = 'owner';
                $_SESSION['name'] = $row['name'];
                $_SESSION['owner_id'] = $row['id'];
                header('Location: /renteasy/owner/manage.php');
                exit;
            }
        } else { // user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(array($email));
            $row = $stmt->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['role'] = 'user';
                $_SESSION['name'] = $row['name'];
                $_SESSION['user_id'] = $row['id'];
                header('Location: /renteasy/user/browse.php');
                exit;
            }
        }
        $error = 'Invalid credentials or role.';
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h2>Login</h2>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="user">User</option>
            <option value="owner">Owner</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Email / Username (for Admin)</label>
          <input type="text" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-primary">Login</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
