<?php
session_start();

if (!isset($_SESSION['investor_id'])) {
    header("Location: investor_signup.php");
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

$investor_id = $_SESSION['investor_id'];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $interests = isset($_POST['interests']) ? implode(',', $_POST['interests']) : '';
    $regions = isset($_POST['region']) ? implode(',', $_POST['region']) : '';

    $update = $conn->prepare("UPDATE investors SET first_name = ?, last_name = ?, interests = ?, region = ? WHERE id = ?");
    $update->bind_param("ssssi", $first_name, $last_name, $interests, $regions, $investor_id);
    if ($update->execute()) {
        $success_message = "✅ تم حفظ التعديلات بنجاح.";
    } else {
        $success_message = "❌ حدث خطأ أثناء حفظ التعديلات.";
    }
}

$sql = "SELECT * FROM investors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $investor_id);
$stmt->execute();
$result = $stmt->get_result();
$investor = $result->fetch_assoc();

$selected_interests = explode(',', $investor['interests']);
$selected_regions = explode(',', $investor['region']);

$all_interests = ['تقنية', 'لوجستية', 'تجارية', 'سياحية', 'صناعية'];
$all_regions = ['الشرقية', 'الوسطى', 'الغربية', 'الجنوبية'];

?>

<html>
<head>
  <meta charset="UTF-8">
  <title>ملفي كمستثمر</title>
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
    input, .checkbox-group {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    input[readonly] {
      background-color: #eee;
    }
    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding: 10px;
    }
    .checkbox-group label {
      width: 48%;
      background-color: #f2f2f2;
      padding: 8px;
      border-radius: 6px;
      display: flex;
      align-items: center;
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
    <h2 style="text-align:center;">ملفي الشخصي كمستثمر</h2>

    <?php if ($success_message): ?>
      <div class="message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <label>الاسم الأول</label>
    <input type="text" name="first_name" value="<?php echo $investor['first_name']; ?>" required>

    <label>الاسم الثاني</label>
    <input type="text" name="last_name" value="<?php echo $investor['last_name']; ?>" required>

    <label>رقم الهوية</label>
    <input type="text" value="<?php echo $investor['national_id']; ?>" readonly>

    <label>البريد الإلكتروني</label>
    <input type="email" value="<?php echo $investor['email']; ?>" readonly>

    <label>رقم الجوال</label>
    <input type="text" value="<?php echo $investor['phone']; ?>" readonly>

    <label>الاهتمامات</label>
    <div class="checkbox-group">
      <?php foreach ($all_interests as $item): ?>
        <label>
          <input type="checkbox" name="interests[]" value="<?php echo $item; ?>"
            <?php if (in_array($item, $selected_interests)) echo "checked"; ?>>
          <?php echo $item; ?>
        </label>
      <?php endforeach; ?>
    </div>

    <label>المناطق الجغرافية المهتم بها</label>
    <div class="checkbox-group">
      <?php foreach ($all_regions as $region): ?>
        <label>
          <input type="checkbox" name="region[]" value="<?php echo $region; ?>"
            <?php if (in_array($region, $selected_regions)) echo "checked"; ?>>
          <?php echo $region; ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit">💾 حفظ التعديلات</button>
  </form>

</body>
</html>
