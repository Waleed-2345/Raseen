<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$conv_id = intval($_GET['conv_id'] ?? 0);
if ($conv_id <= 0) {
    echo json_encode(['error' => 'missing_conv_id']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    echo json_encode(['error' => 'db_connect', 'msg' => $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("
  SELECT sender_type, sender_id, message_text, sent_at
  FROM messages
  WHERE conversation_id = ?
  ORDER BY sent_at ASC
");
$stmt->bind_param("i", $conv_id);
$stmt->execute();
$res = $stmt->get_result();

$messages = [];
while ($r = $res->fetch_assoc()) {
    $messages[] = [
        'sender_type' => $r['sender_type'],
        'sender_id' => $r['sender_id'],
        'text' => $r['message_text'],
        'sent_at' => $r['sent_at'],
    ];
}

echo json_encode(['messages' => $messages]);
$stmt->close();
$conn->close();
