<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$entrepreneur_id = $_SESSION['entrepreneur_id'];
$idea_name = $_POST['idea_name'] ?? '';
$idea_summary = $_POST['idea_summary'] ?? '';
$problem_statement = $_POST['problem_statement'] ?? '';
$proposed_solution = $_POST['proposed_solution'] ?? '';
$field = $_POST['field'] ?? '';
$sub_fields = isset($_POST['sub_fields']) ? implode(',', $_POST['sub_fields']) : '';
$other_field_detail = $_POST['other_field_detail'] ?? '';
$target = isset($_POST['target']) ? implode(',', $_POST['target']) : '';
$target_other = $_POST['target_other'] ?? '';
$has_experience = $_POST['has_experience'] ?? '';
$has_partner = $_POST['has_partner'] ?? '';
$investment_goal = $_POST['investment_goal'] ?? '';
$sell_price = $_POST['sell_price'] ?? null;
$readiness_level = $_POST['readiness_level'] ?? '0';
$has_support_file = $_POST['has_support_file'] ?? 'لا';

// Optional supporting file
$support_file_path = '';
if ($has_support_file === "نعم" && isset($_FILES['support_file']) && $_FILES['support_file']['error'] == 0) {
    $fileTmp = $_FILES['support_file']['tmp_name'];
    $fileName = time() . "_" . basename($_FILES['support_file']['name']);
    $uploadDir = "idea_files/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    move_uploaded_file($fileTmp, $uploadDir . $fileName);
    $support_file_path = $uploadDir . $fileName;
}

$market_research_summary = $_POST['market_research_summary'] ?? '';
$main_challenge = $_POST['main_challenge'] ?? '';
$next_step = $_POST['next_step'] ?? '';

$score = 15;

if (mb_strlen(trim($idea_summary)) > 30) $score += 4;
if (mb_strlen(trim($problem_statement)) > 30) $score += 4;
if (mb_strlen(trim($proposed_solution)) > 30) $score += 4;

if ($field != '') $score += 4;
if ($target != '' || $target_other != '') $score += 4;

$readiness_map = ['0'=>0, '25'=>5, '50'=>10, '75'=>18, '100'=>25];
$score += $readiness_map[$readiness_level] ?? 0;

if ($has_experience === 'نعم') $score += 6;
if ($has_partner === 'نعم') $score += 4;

if ($has_support_file === "نعم" && $support_file_path != '') $score += 15;

if ($investment_goal === "استثمار كامل" || $investment_goal === "استثمار جزئي") $score += 5;

if (mb_strlen(trim($market_research_summary)) > 15) $score += 10;

if (mb_strlen(trim($main_challenge)) > 10) $score += 2.5;
if (mb_strlen(trim($next_step)) > 10) $score += 2.5;

if ($score > 95) $score = 95;
if ($score < 20) $score = 20;


$sql = "INSERT INTO ideas (
    entrepreneur_id, idea_name, idea_summary, problem_statement, proposed_solution, field,
    sub_fields, other_field_detail, target, target_other, has_experience, has_partner, investment_goal, sell_price, readiness_level, has_support_file, support_file, market_research_summary, main_challenge, next_step, score
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql); // هذا السطر لازم يسبق bind_param

if (!$stmt) {
    die("فشل في تحضير الاستعلام: " . $conn->error);
}

$stmt->bind_param(
    "isssssssssssssisssssi",
    $entrepreneur_id,
    $idea_name,
    $idea_summary,
    $problem_statement,
    $proposed_solution,
    $field,
    $sub_fields,
    $other_field_detail,
    $target,
    $target_other,
    $has_experience,
    $has_partner,
    $investment_goal,
    $sell_price,
    $readiness_level,
    $has_support_file,
    $support_file_path,
    $market_research_summary,
    $main_challenge,
    $next_step,
    $score
);

if ($stmt->execute()) {
    echo "<script>alert('تم إضافة فكرة المشروع بنجاح!');window.location.href='entrepreneur_homepage.php';</script>";
} else {
    echo "خطأ في الحفظ: " . $stmt->error;
}

$stmt->close();
$conn->close();


if ($stmt->execute()) {
    echo "<script>alert('تم إضافة فكرة المشروع بنجاح!');window.location.href='entrepreneur_homepage.php';</script>";
} else {
    echo "خطأ في الحفظ: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
