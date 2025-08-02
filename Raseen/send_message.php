<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['investor_id'])) {
    echo json_encode(['error' => 'no_session']);
    exit;
}
$sender_id = $_SESSION['investor_id'];
$sender_type = 'investor';

$conv_id = intval($_POST['conv_id'] ?? 0);
$msg = trim($_POST['msg'] ?? '');

if ($conv_id <= 0) {
    echo json_encode(['error' => 'empty_conv_id']);
    exit;
}
if ($msg === '') {
    echo json_encode(['error' => 'empty_message']);
    exit;
}
if (preg_match('/\d{8,}/', $msg) || preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $msg)) {
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    echo json_encode(['error' => 'db_connect', 'msg' => $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("
  INSERT INTO messages (conversation_id, sender_type, sender_id, message_text, sent_at)
  VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("isis", $conv_id, $sender_type, $sender_id, $msg);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'insert_failed', 'detail' => $stmt->error]);
    exit;
}

echo json_encode(['status' => 'ok']);
$stmt->close();
$conn->close();
