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
        alert('كلمة المرور وتأكيدها غير متطابقين.');
        window.location.href = 'entrepreneur_signup.php';
    </script>";
    exit;
}

$email = $_POST['email'];
$national_id = $_POST['national_id'];

$check = $conn->prepare("SELECT * FROM entrepreneurs WHERE email = ? OR national_id = ?");
$check->bind_param("ss", $email, $national_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<script>
        alert('البريد الإلكتروني أو رقم الهوية مستخدم مسبقًا.');
        window.location.href = 'entrepreneur_signup.php';
    </script>";
    exit;
}

$stmt = $conn->prepare("INSERT INTO entrepreneurs (first_name, last_name, national_id, phone, email, password) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("خطأ في تحضير الاستعلام: " . $conn->error);
}

$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt->bind_param("ssssss",
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['national_id'],
    $_POST['phone'],
    $_POST['email'],
    $hashed_password
);

if ($stmt->execute()) {
    $_SESSION['entrepreneur_id'] = $conn->insert_id; 
    $_SESSION['just_registered'] = true; 
    echo "<script>
        window.location.href = 'entrepreneur_homepage.php';
    </script>";
} else {
    echo "<script>
        alert('حدث خطأ أثناء حفظ البيانات.');
        window.location.href = 'entrepreneur_signup.php';
    </script>";
}

$stmt->close();
$conn->close();
?>
