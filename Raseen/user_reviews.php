<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['investor_id'])) {
    echo json_encode(['ok'=>false, 'msg'=>'غير مصرح']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false, 'msg'=>'Invalid method']);
    exit;
}
$investor_id = intval($_SESSION['investor_id']);
$investment_id = intval($_POST['investment_id'] ?? 0);

$overall_star = intval($_POST['overall_star'] ?? 5);
$usability_star = intval($_POST['usability_star'] ?? 5);
$trust_star = intval($_POST['trust_star'] ?? 5);
$communication_star = intval($_POST['communication_star'] ?? 5);
$feedback = trim($_POST['feedback'] ?? '');

if ($investment_id == 0) {
    echo json_encode(['ok'=>false, 'msg'=>'رقم الاستثمار ناقص.']);
    exit;
}

foreach ([$overall_star, $usability_star, $trust_star, $communication_star] as $star) {
    if ($star < 1 || $star > 5) {
        echo json_encode(['ok'=>false, 'msg'=>'قيمة النجوم غير صحيحة.']);
        exit;
    }
}

$conn = new mysqli("localhost", "root", "", "raseen");
$conn->set_charset('utf8mb4');
if ($conn->connect_error) {
    echo json_encode(['ok'=>false, 'msg'=>'DB connection failed.']);
    exit;
}

// تأكد أن الاستثمار يخص هذا المستثمر (حماية إضافية)
$checkOwner = $conn->prepare("SELECT id FROM investments WHERE id=? AND investor_id=?");
$checkOwner->bind_param("ii", $investment_id, $investor_id);
$checkOwner->execute(); $checkOwner->store_result();
if ($checkOwner->num_rows == 0) {
    echo json_encode(['ok'=>false, 'msg'=>'لا تملك هذا الاستثمار']);
    $checkOwner->close(); $conn->close(); exit;
}
$checkOwner->close();

// تحقق من عدم التكرار
$check = $conn->prepare("SELECT id FROM platform_ratings WHERE investor_id=? AND investment_id=?");
$check->bind_param("ii", $investor_id, $investment_id);
$check->execute(); $check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['ok'=>false, 'msg'=>'تم التقييم مسبقًا.']);
    $check->close(); $conn->close(); exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO platform_ratings (investor_id, investment_id, overall_star, usability_star, trust_star, communication_star, comment, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iiiiiss", $investor_id, $investment_id, $overall_star, $usability_star, $trust_star, $communication_star, $feedback);
if ($stmt->execute()) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false, 'msg'=>'DB error: '.$conn->error]);
}
$stmt->close();
$conn->close();
?>
