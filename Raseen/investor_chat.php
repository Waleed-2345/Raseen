<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: signin.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$conv_id = 0;
if (isset($_GET['conv_id'])) {
    $conv_id = intval($_GET['conv_id']);
} elseif (isset($_POST['conv_id'])) {
    $conv_id = intval($_POST['conv_id']);
}

if (isset($_GET['project_id']) && !$conv_id) {
    $project_id = intval($_GET['project_id']);
    $stmt = $conn->prepare("SELECT entrepreneur_id FROM projects WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) die("المشروع غير موجود");
    $entrepreneur_id = $res->fetch_assoc()['entrepreneur_id'];
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT id FROM conversations
         WHERE project_id=? AND investor_id=? AND entrepreneur_id=?
         LIMIT 1"
    );
    $stmt->bind_param("iii", $project_id, $investor_id, $entrepreneur_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $conv_id = $res->fetch_assoc()['id'];
    } else {
        $ins = $conn->prepare(
            "INSERT INTO conversations
             (project_id, investor_id, entrepreneur_id, started_at)
             VALUES (?, ?, ?, NOW())"
        );
        $ins->bind_param("iii", $project_id, $investor_id, $entrepreneur_id);
        $ins->execute();
        $conv_id = $ins->insert_id;
        $ins->close();
    }
    $stmt->close();
    header("Location: investor_chat.php?conv_id=$conv_id");
    exit;
}

if ($conv_id <= 0) {
    die("لم يتم تحديد المحادثة");
}

if (isset($_GET['fetch']) && $_GET['fetch'] === '1') {
    $msgs = $conn->query("
      SELECT sender_type, message_text, sent_at
      FROM messages
      WHERE conversation_id = $conv_id
      ORDER BY sent_at ASC
    ");
    if ($msgs->num_rows === 0) {
        echo '<div class="no-messages">لا توجد رسائل بعد.</div>';
    } else {
        while ($m = $msgs->fetch_assoc()) {
            $cls  = $m['sender_type'] === 'investor' ? 'investor' : 'entrepreneur';
            $text = htmlspecialchars($m['message_text']);
            $time = date('Y-m-d H:i', strtotime($m['sent_at']));
            echo <<<HTML
<div class="msg-bubble $cls">
  <div class="msg-text">$text</div>
  <div class="msg-time">$time</div>
</div>
HTML;
        }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    $msg = trim($_POST['msg']);
    if (preg_match('/\d{8,}/', $msg) || preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $msg)) {
        echo 'forbidden';
        exit;
    }
    $stmt = $conn->prepare("
      INSERT INTO messages
        (conversation_id, sender_type, sender_id, message_text, sent_at)
      VALUES (?, 'investor', ?, ?, NOW())
    ");
    $stmt->bind_param("iis", $conv_id, $investor_id, $msg);
    $stmt->execute();
    $stmt->close();
    exit;
}

$stmt = $conn->prepare("
  SELECT p.id AS project_id, p.project_name,
         e.first_name, e.last_name
  FROM conversations c
  JOIN projects p ON c.project_id = p.id
  JOIN entrepreneurs e ON c.entrepreneur_id = e.ID
  WHERE c.id = ? AND c.investor_id = ?
  LIMIT 1
");
$stmt->bind_param("ii", $conv_id, $investor_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res->num_rows) die("المحادثة غير موجودة أو غير مصرح لك.");
$row = $res->fetch_assoc();
$project_id        = $row['project_id'];
$project_name      = htmlspecialchars($row['project_name'], ENT_QUOTES);
$entrepreneur_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES);
$stmt->close();
$conn->close();
?>
<html>
<head>
  <meta charset="UTF-8">
  <title>محادثة مع <?= $entrepreneur_name ?> – <?= $project_name ?></title>
  <style>
    body { font-family:'Segoe UI',sans-serif; background:#f6f9f6; margin:0; }
    .chat-container { max-width:550px; margin:40px auto; background:#fff; border-radius:15px; box-shadow:0 4px 22px rgba(0,0,0,0.1); overflow:hidden; }
    .chat-header { display:flex; align-items:center; justify-content:space-between; background:#0e6445; color:#fff; padding:14px 18px; }
    .back-btn, .start-deal-btn { background:none; border:none; color:#fff; font-size:1.2em; cursor:pointer; }
    .start-deal-btn { background:#f3faff; color:#11775d; border-radius:6px; padding:6px 12px; font-size:.9em; font-weight:600; text-decoration:none; }
    .start-deal-btn:hover { background:#d5ede3; }
    .chat-title { font-size:1.1em; font-weight:600; flex:1; text-align:center; }
    .messages { padding:16px; min-height:300px; max-height:400px; overflow-y:auto; background:#f7fafd; }
    .msg-bubble { display:block; padding:10px 14px; margin:6px 0; border-radius:8px; word-wrap:break-word; width:100%; box-sizing:border-box; font-size:0.95em; position:relative; }
    .msg-bubble.investor { background:#11657e; color:#fff; text-align:right; }
    .msg-bubble.entrepreneur { background:#dbeff2; color:#205047; text-align:left; }
    .msg-time { display:block; font-size:0.65em; margin-top:4px; line-height:1.1; }
    .msg-bubble.investor .msg-time { color: rgba(255,255,255,0.85); }
    .msg-bubble.entrepreneur .msg-time { color:#555; }
    .no-messages { text-align:center; color:#888; margin-top:40px; }
    .send-box { display:flex; padding:12px; border-top:1px solid #e0e0e0; background:#fff; }
    .send-box input { flex:1; padding:8px 12px; border:1px solid #c2d6ef; border-radius:20px; font-size:.95em; outline:none; }
    .send-box button { background:#11657e; color:#fff; border:none; border-radius:50%; width:36px; height:36px; font-size:18px; cursor:pointer; }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <div class="chat-container">
    <div class="chat-header">
      <button class="back-btn" onclick="history.back()" title="عودة">&#8592;</button>
      <div class="chat-title"><?= $entrepreneur_name ?> – <?= $project_name ?></div>
      <a href="complete_deal.php?project_id=<?= $project_id ?>" class="start-deal-btn">بدء التمويل</a>
    </div>

    <div class="messages" id="messages"></div>

    <form class="send-box" id="sendMsgForm" autocomplete="off">
      <input type="text" id="msgInput" maxlength="450" placeholder="اكتب رسالتك هنا..." required>
      <button type="submit">&#9658;</button>
    </form>
  </div>

  <script>
    const convId = <?= intval($conv_id) ?>;

    function loadMessages(){
      $.get('investor_chat.php', { conv_id: convId, fetch: 1 }, data => {
        $('#messages').html(data).scrollTop($('#messages')[0]?.scrollHeight || 0);
      });
    }

    $('#sendMsgForm').on('submit', function(e){
      e.preventDefault();
      let msg = $('#msgInput').val().trim();
      if (!msg) return;
      if (/\d{8,}/.test(msg) || /[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/.test(msg)) {
        alert("لا يسمح بإرسال أرقام جوال أو إيميل أو بيانات حساسة!");
        return;
      }
      $.post('investor_chat.php', { conv_id: convId, msg: msg }, res => {
        if (res === 'forbidden') {
          alert("لا يسمح بإرسال بيانات حساسة!");
        } else {
          $('#msgInput').val('');
          loadMessages();
        }
      });
    });

    $(document).ready(() => {
      loadMessages();
      setInterval(loadMessages, 2000);
    });
  </script>
</body>
</html>
