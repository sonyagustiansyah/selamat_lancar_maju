<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role']; // role dari dropdown

    $sql = "INSERT INTO users (username,password,role) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        if ($conn->errno == 1062) { // Duplicate entry
            $error = "USERNAME SUDAH TERDAFTAR!";
        } else {
            $error = "GAGAL REGISTER: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>REGISTER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .register-card {
      max-width: 420px;
      width: 100%;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .register-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .register-header h2 {
      font-weight: bold;
      color: #4e73df;
    }
  </style>
</head>
<body>
  <div class="card register-card p-4">
    <div class="register-header">
      <h2>REGISTER</h2>
    </div>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>".htmlspecialchars($error)."</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">USERNAME</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">PASSWORD</label>
        <input type="password" name="password" class="form-control" required minlength="6">
      </div>
      <div class="mb-3">
        <label class="form-label">ROLE</label>
        <select name="role" class="form-select" required>
          <option value="USER" selected>USER</option>
          <option value="ADMIN">ADMIN</option>
        </select>
      </div>
      <button type="submit" class="btn btn-success w-100">REGISTER</button>
      <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">SUDAH PUNYA AKUN? LOGIN</a>
      </div>
    </form>
  </div>
</body>
</html>