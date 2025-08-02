<?php
session_start();
$conn = new mysqli("localhost", "root", "", "raseen"); 
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_identity = trim($_POST['user_identity']);
    $password = $_POST['password'];

    function findUser($conn, $table, $user_identity) {
        $sql = "SELECT * FROM $table WHERE national_id = ? OR phone = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $user_identity, $user_identity, $user_identity);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    $user = findUser($conn, 'investors', $user_identity);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['user_type'] = 'investor';
        $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
        $_SESSION['investor_id'] = $user['ID']; // أضف هذا السطر الهام
        header("Location: investor_homepage.php");
        exit;
    }

    $user = findUser($conn, 'entrepreneurs', $user_identity);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['user_type'] = 'entrepreneur';
        $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
        $_SESSION['entrepreneur_id'] = $user['ID']; 
        header("Location: entrepreneur_homepage.php");
        exit;
    }

    $error = 'بيانات الدخول غير صحيحة، يرجى المحاولة مرة أخرى.';
}
?>

<html>
<head>
  <meta charset="UTF-8">
  <title>تسجيل الدخول | رصين</title>
  <style>
    body {
      font-family: 'Tajawal', Arial, sans-serif;
      background: #f7f9fc;
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-container {
      background: #fff;
      padding: 38px 32px 32px 32px;
      border-radius: 18px;
      box-shadow: 0 3px 18px rgba(24, 92, 55, 0.08);
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    .login-title {
      font-size: 2em;
      color: #185c37;
      margin-bottom: 18px;
      font-weight: bold;
    }
    .login-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .login-form input {
      padding: 11px 10px;
      border: 1px solid #c7d3c4;
      border-radius: 9px;
      font-size: 1.1em;
      background: #fafbfc;
      direction: rtl;
    }
    .login-form input:focus {
      outline: 1.5px solid #185c37;
      border-color: #185c37;
      background: #fff;
    }
    .login-btn {
      padding: 12px 0;
      background: #185c37;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1.1em;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.18s;
    }
    .login-btn:hover {
      background: #267d54;
    }
    .note {
      color: #4e4e4e;
      font-size: .95em;
      margin-top: 7px;
      margin-bottom: 0;
    }
    .signup-link {
      color: #185c37;
      font-size: .98em;
      margin-top: 13px;
      display: block;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.14s;
    }
    .signup-link:hover {
      color: #0b3b23;
      text-decoration: underline;
    }
    .error-msg {
      color: #e94335;
      font-size: .95em;
      margin-bottom: 0;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-title">تسجيل الدخول</div>
    <?php if ($error): ?>
      <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>
    <form class="login-form" action="" method="post" autocomplete="off">
      <input type="text" name="user_identity" placeholder="رقم الهوية الوطنية أو الجوال أو الإيميل" required>
      <input type="password" name="password" placeholder="كلمة المرور" required>
      <button type="submit" class="login-btn">دخول</button>
    </form>
    <div class="note">يمكنك الدخول باستخدام الهوية الوطنية أو رقم الجوال أو البريد الإلكتروني</div>
    <a class="signup-link" href="choose_account.php">ليس لديك حساب؟ سجل الآن</a>
  </div>
</body>
</html>
