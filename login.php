<?php
include 'config.php';

// Cegah login ulang jika sudah login
if (isset($_SESSION['username'])) {
    header("Location: customers.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql  = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res  = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] == 'ADMIN') {
                header("Location: customers.php");
            } else {
                header("Location: customers.php");
            }
            exit();
        } else {
            $error = "USERNAME ATAU PASSWORD SALAH!";
        }
    } else {
        $error = "USER TIDAK DITEMUKAN!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>LOGIN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      max-width: 400px;
      width: 100%;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .login-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .login-header h2 {
      font-weight: bold;
      color: #4e73df;
    }
  </style>
</head>
<body>
  <div class="card login-card p-4">
    <div class="login-header">
      <h2>LOGIN</h2>
    </div>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>".htmlspecialchars($error)."</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">USERNAME</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">PASSWORD</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">LOGIN</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>