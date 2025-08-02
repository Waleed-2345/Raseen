<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "raseen";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

if ($_POST['password'] !== $_POST['confirm_password']) {
    echo "<script>
        alert('كلمة المرور وتأكيدها غير متطابقتين.');
        window.location.href = 'investor_signup.php';
    </script>";
    exit;
}

$interests = isset($_POST['interests']) ? implode(',', $_POST['interests']) : '';
$regions = isset($_POST['region']) ? implode(',', $_POST['region']) : '';

$email = $_POST['email'];
$check = $conn->prepare("SELECT * FROM investors WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    echo "<script>
        alert('البريد الإلكتروني مسجّل مسبقًا.');
        window.location.href = 'investor_signup.php';
    </script>";
    exit;
}

$stmt = $conn->prepare("INSERT INTO investors (first_name, last_name, national_id, phone, email, interests, region, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("خطأ في تحضير الاستعلام: " . $conn->error);
}

$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt->bind_param("ssssssss",
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['national_id'],
    $_POST['phone'],
    $_POST['email'],
    $interests,
    $regions,
    $hashed_password
);

if ($stmt->execute()) {
    $_SESSION['investor_id'] = $conn->insert_id;
    $_SESSION['user_id']     = $conn->insert_id;       // جديد
    $_SESSION['user_type']   = 'investor';             // جديد
    $_SESSION['user_name']   = $_POST['first_name'] . ' ' . $_POST['last_name']; // اختياري
    $_SESSION['just_registered'] = true;
    echo "<script>
        alert('تم إنشاء الحساب بنجاح!');
        window.location.href = 'investor_homepage.php';
    </script>";
}

 else {
    echo "<script>
        alert('حدث خطأ أثناء حفظ البيانات.');
        window.location.href = 'investor_signup.php';
    </script>";
}

$stmt->close();
$conn->close();
?>
