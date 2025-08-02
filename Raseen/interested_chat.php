<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: signin.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
if (!isset($_GET['project_id'])) {
    die("لم يتم تحديد المشروع");
}
$project_id = intval($_GET['project_id']);

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) { die("فشل الاتصال: " . $conn->connect_error); }

$q = $conn->query("SELECT p.project_name, e.ID as entrepreneur_id, e.first_name, e.last_name
                   FROM projects p
                   JOIN entrepreneurs e ON p.entrepreneur_id = e.ID
                   WHERE p.id = $project_id LIMIT 1");
if (!$q || $q->num_rows == 0) die("المشروع غير موجود");
$info = $q->fetch_assoc();
$entrepreneur_id = $info['entrepreneur_id'];
$entrepreneur_name = $info['first_name'] . " " . $info['last_name'];
$project_name = $info['project_name'];

$check = $conn->query("SELECT id FROM conversations WHERE project_id=$project_id AND investor_id=$investor_id AND entrepreneur_id=$entrepreneur_id LIMIT 1");
if ($check && $check->num_rows > 0) {
    $conv_id = $check->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO conversations (project_id, investor_id, entrepreneur_id, started_at) VALUES ($project_id, $investor_id, $entrepreneur_id, NOW())");
    $conv_id = $conn->insert_id;
}
$conn->close();
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>محادثة مع <?php echo htmlspecialchars($entrepreneur_name); ?> حول "<?php echo htmlspecialchars($project_name); ?>"</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f6f9f6; margin: 0; }
        .chat-container { max-width: 550px; margin: 44px auto; background: #fff; border-radius: 15px; box-shadow: 0 4px 22px #02754c2b; padding: 0 0 16px 0; }
        .chat-header { background: #0e6445; color: #fff; padding: 18px 18px 9px 14px; border-radius: 15px 15px 0 0; display: flex; align-items: center; justify-content: space-between; }
        .chat-title { font-size: 1.13em; font-weight: bold; }
        .back-btn, .start-deal-btn { background: none; border: none; color: #fff; font-size: 1.3em; cursor: pointer; margin-left: 9px;}
        .start-deal-btn { background: #f3faff; color: #11775d; border-radius: 7px; padding: 5px 15px; font-size: 1em; font-weight: 600; transition: .16s; margin-left: 0;}
        .start-deal-btn:hover { background: #d5ede3;}
        .messages { min-height: 300px; max-height: 330px; overflow-y: auto; padding: 18px 18px 6px 18px; background: #f7fafd; }
        .msg-bubble { max-width: 70%; padding: 10px 15px; border-radius: 17px; margin-bottom: 10px; font-size: 15px; line-height:1.7; }
        .msg-bubble.investor { background: #11657e; color: #fff; margin-left: auto; text-align: right;}
        .msg-bubble.entrepreneur { background: #dbeff2; color: #205047; margin-right: auto; text-align: left;}
        .msg-name { font-size: .89em; font-weight: 600; margin-bottom: 2px; color: #0e6445; display:block; }
        .msg-time { font-size: 11px; color: #888; margin-top: 2px; text-align: left;}
        .send-box { padding: 15px 18px 0 18px; display: flex; gap: 7px; }
        .send-box input { flex: 1; padding: 9px 12px; border-radius: 17px; border: 1px solid #c2d6ef; font-size: 15px; }
        .send-box button { background: #11657e; color: #fff; border: none; border-radius: 50%; width: 36px; height: 36px; font-size: 19px; cursor: pointer;}
        .no-messages { color: #8a8a8a; text-align: center; margin-top: 35px;}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <button class="back-btn" onclick="window.history.back()" title="عودة">&#8592;</button>
        <span class="chat-title"><?php echo htmlspecialchars($entrepreneur_name); ?> (<?php echo htmlspecialchars($project_name); ?>)</span>
        <a href="complete_deal.php?project_id=<?php echo $project_id; ?>" class="start-deal-btn">بدء التمويل</a>
    </div>
    <div class="messages" id="messages"></div>
    <form class="send-box" id="sendMsgForm" autocomplete="off">
        <input type="text" id="msgInput" maxlength="450" placeholder="اكتب رسالتك هنا..." required>
        <button type="submit">&#9658;</button>
    </form>
</div>

<script>
let conv_id = <?php echo json_encode($conv_id); ?>;
let investor_id = <?php echo json_encode($investor_id); ?>;
function loadMessages(){
    $.get('get_messages.php', { conv_id: conv_id }, function(data){
        $('#messages').html(data).scrollTop($('#messages')[0].scrollHeight);
    });
}
$('#sendMsgForm').on('submit', function(e){
    e.preventDefault();
    let msg = $('#msgInput').val().trim();
    if (!msg) return;
    if(/\d{8,}/.test(msg)) {
        alert("لا يسمح بإرسال أرقام الجوال أو البيانات الحساسة!");
        return;
    }
    $.post('send_message.php', { conv_id: conv_id, msg: msg }, function(){
        $('#msgInput').val('');
        loadMessages();
    });
});
setInterval(loadMessages, 2000);
$(document).ready(loadMessages);
</script>
</body>
</html>
