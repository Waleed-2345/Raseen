<?php
session_start();

if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "raseen";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$entrepreneur_id = $_SESSION['entrepreneur_id'];
$success_message = "";

// إذا أرسل النموذج، حدث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $update = $conn->prepare("UPDATE entrepreneurs SET first_name = ?, last_name = ? WHERE id = ?");
    $update->bind_param("ssi", $first_name, $last_name, $entrepreneur_id);
    if ($update->execute()) {
        $success_message = "✅ تم تحديث البيانات بنجاح.";
    } else {
        $success_message = "❌ حدث خطأ أثناء الحفظ.";
    }
}

// جلب بيانات رائد الأعمال
$sql = "SELECT * FROM entrepreneurs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $entrepreneur_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ملفي كرائد أعمال</title>
  <style>
    body {
      font-family: sans-serif;
      background-color: #f9fdfb;
      padding: 50px;
      direction: rtl;
    }
    form {
      max-width: 700px;
      margin: auto;
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    label {
      display: block;
      margin-top: 20px;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    input[readonly] {
      background-color: #eee;
    }
    button {
      margin-top: 25px;
      padding: 12px;
      width: 100%;
      background-color: #035917;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .message {
      text-align: center;
      color: green;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <form method="POST">
    <h2 style="text-align:center;">ملفي كرائد أعمال</h2>

    <?php if ($success_message): ?>
      <div class="message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <label>الاسم الأول</label>
    <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>

    <label>الاسم الثاني</label>
    <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>

    <label>رقم الهوية</label>
    <input type="text" value="<?php echo $user['national_id']; ?>" readonly>

    <label>البريد الإلكتروني</label>
    <input type="email" value="<?php echo $user['email']; ?>" readonly>

    <label>رقم الجوال</label>
    <input type="text" value="<?php echo $user['phone']; ?>" readonly>

    <button type="submit">💾 حفظ التعديلات</button>
  </form>

</body>
</html>
