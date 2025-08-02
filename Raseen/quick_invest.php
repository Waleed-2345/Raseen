<?php
ob_start();
error_reporting(0); 

header('Content-Type: application/json; charset=utf-8');
session_start();

$response = [];

if (!isset($_SESSION['investor_id'])) {
    $response['error'] = "لازم تسجل دخول كمستثمر.";
    echo json_encode($response);
    exit;
}
$investor_id = $_SESSION['investor_id'];

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    $response['error'] = "فشل الاتصال بقاعدة البيانات.";
    echo json_encode($response);
    exit;
}

$project_id = intval($_POST['project_id'] ?? 0);
$entrepreneur_id = intval($_POST['entrepreneur_id'] ?? 0);
$type = (isset($_POST['type']) && $_POST['type'] === 'loan') ? 'loan' : 'equity';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$status = in_array($_POST['status'] ?? '', ['accepted', 'negotiating', 'pending', 'rejected']) ? $_POST['status'] : 'pending';
$proposal_notes = $conn->real_escape_string($_POST['proposal_notes'] ?? '');
$trust_score_at_proposal = intval($_POST['trust_score_at_proposal'] ?? 0);

$equity_percentage = isset($_POST['equity_percentage']) && $_POST['equity_percentage'] !== '' ? floatval($_POST['equity_percentage']) : null;
$loan_term_months = isset($_POST['loan_term_months']) && $_POST['loan_term_months'] !== '' ? intval($_POST['loan_term_months']) : null;
$interest_rate = isset($_POST['interest_rate']) && $_POST['interest_rate'] !== '' ? floatval($_POST['interest_rate']) : null;
$repayment_start_date = !empty($_POST['repayment_start_date']) ? $_POST['repayment_start_date'] : null;

if ($trust_score_at_proposal === 0) {
    $p = $conn->prepare("SELECT trust_score FROM projects WHERE id=? LIMIT 1");
    $p->bind_param("i", $project_id);
    $p->execute();
    $pr = $p->get_result()->fetch_assoc();
    $trust_score_at_proposal = intval($pr['trust_score'] ?? 0);
    $p->close();
}

$stmt = $conn->prepare("
  INSERT INTO investments 
    (project_id, investor_id, entrepreneur_id, type, amount, equity_percentage, loan_term_months, interest_rate, repayment_start_date, trust_score_at_proposal, proposal_notes, status)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
");

if (!$stmt) {
    $response['error'] = "خطأ في تحضير الاستعلام: " . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param(
    "iiissddidsss",
    $project_id,
    $investor_id,
    $entrepreneur_id,
    $type,
    $amount,
    $equity_percentage,
    $loan_term_months,  
    $interest_rate,    
    $repayment_start_date, 
    $trust_score_at_proposal,
    $proposal_notes,
    $status
);

$stmt->execute();
if ($stmt->error) {
    $response['error'] = "فشل الحفظ: " . $stmt->error;
    echo json_encode($response);
    exit;
}
$investment_id = $stmt->insert_id;
$stmt->close();

$conversation_id = null;
$conv_stmt = $conn->prepare("SELECT id FROM conversations WHERE project_id=? AND investor_id=? AND entrepreneur_id=? LIMIT 1");
$conv_stmt->bind_param("iii", $project_id, $investor_id, $entrepreneur_id);
$conv_stmt->execute();
$res = $conv_stmt->get_result();
if ($res->num_rows === 0) {
    $ins = $conn->prepare("INSERT INTO conversations (project_id, investor_id, entrepreneur_id, started_at) VALUES (?,?,?,NOW())");
    $ins->bind_param("iii", $project_id, $investor_id, $entrepreneur_id);
    $ins->execute();
    $conversation_id = $ins->insert_id;
    $ins->close();
} else {
    $row = $res->fetch_assoc();
    $conversation_id = $row['id'];
}
$conv_stmt->close();

$sender_type = 'investor';
$welcome_msg = ucfirst($type) . " proposal created with status {$status}. Notes: {$proposal_notes}";
$msg = $conn->prepare("INSERT INTO messages (conversation_id, sender_type, sender_id, message_text, sent_at) VALUES (?,?,?,?,NOW())");
$msg->bind_param("isis", $conversation_id, $sender_type, $investor_id, $welcome_msg);
$msg->execute();
$msg->close();

$conn->close();

$response = [
    "success" => true,
    "investment_id" => $investment_id,
    "conversation_id" => $conversation_id,
    "status" => $status,
    "message" => "تم حفظ الاقتراح/الصفقة."
];

ob_clean();
echo json_encode($response);
exit;
