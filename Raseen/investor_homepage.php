<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: investor_signup.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) { die("فشل الاتصال: " . $conn->connect_error); }

if (isset($_GET['delete_conversation'])) {
    $conv_id = intval($_GET['delete_conversation']);
    $conn->query("DELETE FROM conversations WHERE id=$conv_id AND investor_id=$investor_id");
    header("Location: investor_homepage.php");
    exit;
}

$conversations = [];
$q = $conn->query("SELECT conversations.*, entrepreneurs.first_name, entrepreneurs.last_name, projects.project_name
                   FROM conversations
                   JOIN entrepreneurs ON conversations.entrepreneur_id = entrepreneurs.ID
                   JOIN projects ON conversations.project_id = projects.id
                   WHERE conversations.investor_id = $investor_id
                   ORDER BY conversations.started_at DESC");
while($row = $q->fetch_assoc()) $conversations[] = $row;
$conn->close();

// جلب تقييمات وآراء المستثمرين
$reviews = [];
$conn2 = new mysqli("localhost", "root", "", "raseen");
if (!$conn2->connect_error) {
    $q2 = $conn2->query("SELECT comment, overall_star FROM platform_ratings WHERE comment IS NOT NULL AND comment <> '' ORDER BY updated_at DESC LIMIT 12");
    while($row = $q2->fetch_assoc()) $reviews[] = $row;
    $conn2->close();
}
?>
<html>
<head>
  <meta charset="UTF-8">
  <title>لوحة تحكم المستثمر</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f9f7;
      margin: 0;
      padding: 0;
      direction: rtl;
    }
    .header {
      background-color: #035917;
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 24px;
      position: relative;
    }
    .dashboard {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      padding: 60px 20px 30px 20px;
    }
    .card {
      background-color: white;
      width: 240px;
      height: 150px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.07);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      transition: 0.3s;
      cursor: pointer;
      text-decoration: none;
      color: #035917;
      font-weight: bold;
      font-size: 18px;
    }
    .card:hover {
      background-color: #eaf5ee;
    }
    /* آراء المستثمرين */
    .reviews-section {
      max-width:1200px;
      margin: 0 auto 50px auto;
      margin-bottom: 42px;
    }
    .reviews-header {
      color:#128a57;
      text-align:center;
      margin:36px 0 18px;
      font-size:1.4em;
      letter-spacing:.5px;
      font-weight: bold;
    }
    .reviews-list {
      display:flex;
      flex-wrap:wrap;
      gap:22px;
      justify-content:center;
      margin-bottom:8px;
    }
    .review-box {
      background:#fff;
      box-shadow:0 2px 16px #17c37d12;
      border-radius:13px;
      min-width:200px;
      max-width:320px;
      padding:20px 16px 16px 16px;
      margin:7px 2px;
      text-align:center;
      flex:1 0 220px;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }
    .review-text {
      font-size:1.05em;
      color:#236d3c;
      margin-bottom:10px;
      min-height:48px;
      word-break: break-word;
    }
    .review-stars {
      margin-top:14px;
      letter-spacing:2px;
      font-size: 1.32em;
    }
    @media (max-width:750px) {
      .dashboard { flex-direction: column; gap: 20px;}
      .reviews-list { gap: 12px;}
      .review-box { min-width:140px; }
    }
    /* رسائل المحادثة (popup) */
    .modal-bg { display:none; position:fixed; top:0; right:0; bottom:0; left:0; background:rgba(36,67,49,0.18); z-index:998; }
    .modal-content { position:fixed; top:60px; right:0; left:0; margin:auto; width:440px; max-width:90vw; background:#fff; border-radius:16px; box-shadow:0 6px 38px #17c37d28; z-index:999; padding: 0 0 14px 0; animation:popup-in 0.26s cubic-bezier(.27,.84,.63,1.32);}
    @keyframes popup-in { 0%{transform:scale(.90);opacity:.4;} 100%{transform:scale(1);opacity:1;} }
    .modal-header { padding:22px 26px 12px 18px; font-size:1.16em; color:#14833b; font-weight:800; border-bottom:1px solid #e5f8ee; display:flex; align-items:center; justify-content:space-between;}
    .modal-close { background:none; border:none; font-size:1.7em; color:#c1c1c1; cursor:pointer; line-height:1;}
    .modal-close:hover { color:#e83d5d;}
    .convs-list { max-height:370px; overflow-y:auto; padding:10px 18px;}
    .conv-row {
      background:#f9fcfa;
      border-radius:13px;
      margin-bottom:13px;
      padding:16px 15px 16px 12px;
      box-shadow:0 1px 8px #19c37d12;
      display:flex;
      flex-direction:row;
      align-items:center;
      justify-content:space-between;
      cursor:pointer;
      transition:background 0.14s, box-shadow 0.2s;
    }
    .conv-row:hover {
      background: #e9f7ee;
      box-shadow: 0 6px 18px #19c37d22;
    }
    .conv-details { flex:1 1 auto; }
    .conv-title { font-size:1.01em; font-weight:700; color:#145529;}
    .conv-meta { color:#858585; font-size:.98em; margin-top:2px;}
    .conv-btns { display:flex; gap:7px; align-items:center;}
    .conv-delete {
      background: none;
      border: none;
      font-size: 1.35em;
      color: #b83030;
      cursor: pointer;
      transition: color 0.16s;
      padding: 0 7px;
      margin-left: 3px;
      text-decoration: none;
      outline: none;
      display: flex;
      align-items: center;
    }
    .conv-delete:hover {
      color: #e31d1d;
      background: none;
    }
    @media (max-width:480px) {
      .modal-content { width:98vw; }
    }
  </style>
  <script>
    function openModal() {
      document.getElementById('modal-bg').style.display = 'block';
      document.getElementById('modal-content').style.display = 'block';
    }
    function closeModal() {
      document.getElementById('modal-bg').style.display = 'none';
      document.getElementById('modal-content').style.display = 'none';
    }
  </script>
</head>
<body>

  <!-- أيقونة الرسائل في الهيدر -->
  <div class="header">
    <span style="float:right; margin-right:25px;">
      <a href="javascript:void(0);" onclick="openModal()" style="color:#fff; text-decoration:none;">
        <svg width="27" height="27" style="vertical-align:middle;" fill="#fff" viewBox="0 0 24 24"><path d="M20 2H4C2.897 2 2 2.897 2 4V20C2 21.103 2.897 22 4 22H20C21.103 22 22 21.103 22 20V4C22 2.897 21.103 2 20 2ZM20 4L12 13L4 4H20ZM4 20V7.236L11.293 15.707C11.683 16.098 12.317 16.098 12.707 15.707L20 7.236V20H4Z"></path></svg>
      </a>
    </span>
    منصة رصين – المستثمر
  </div>

  <div id="modal-bg" class="modal-bg" onclick="closeModal()"></div>
  <div id="modal-content" class="modal-content" style="display:none;">
    <div class="modal-header">
      <span>كل المحادثات</span>
      <button class="modal-close" onclick="closeModal()" title="إغلاق">&times;</button>
    </div>
    <div class="convs-list">
      <?php
if (empty($conversations)) {
    echo '<div style="text-align:center; color:#888; margin-top:60px;">لا توجد محادثات حتى الآن</div>';
} else {
    foreach($conversations as $conv) {
        echo '<div class="conv-row" style="cursor:pointer;" onclick="window.location.href=\'investor_chat.php?project_id=' . $conv['project_id'] . '\'">';
        echo '<div class="conv-details">';
        echo '<div class="conv-title">مشروع: ' . htmlspecialchars($conv['project_name']) . '</div>';
        echo '<div class="conv-meta">رائد الأعمال: ' . htmlspecialchars($conv['first_name']) . ' ' . htmlspecialchars($conv['last_name']) . ' | منذ: ' . date('Y-m-d', strtotime($conv['started_at'])) . '</div>';
        echo '</div>';
        echo '<button class="conv-delete" onclick="event.stopPropagation(); if(confirm(\'هل أنت متأكد من حذف المحادثة؟\')) window.location.href=\'?delete_conversation=' . $conv['id'] . '\';" style="margin-right:10px;font-size:1.3em;background:none;border:none;cursor:pointer;" title=\"حذف المحادثة\">🗑️</button>';
        echo '</div>';
    }
}
      ?>
    </div>
  </div>

  <div id="openai-chat-btn" style="position: fixed; bottom: 30px; left: 30px; z-index: 1000;">
    <button onclick="toggleOpenAIChat()" style="background: #008060; color: white; border: none; border-radius: 50%; width: 55px; height: 55px; font-size: 30px; box-shadow: 0 3px 10px #999;">🤖</button>
  </div>

  <div id="openai-chat-box" style="display:none; position: fixed; bottom: 100px; left: 30px; width: 330px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px #888; z-index: 1100; padding: 0;">
      <div style="background:#008060;color:#fff;padding:12px 16px;border-radius:16px 16px 0 0;font-size:18px;display:flex;justify-content:space-between;align-items:center;">
          مساعد رصين الذكي
          <button onclick="toggleOpenAIChat()" style="background:none;color:white;border:none;font-size:20px;cursor:pointer;">×</button>
      </div>
      <div id="openai-messages" style="height:240px;overflow-y:auto;padding:10px 12px 0 12px;font-size:15px;"></div>
      <form id="openaiForm" style="display:flex;gap:8px;padding:10px;">
        <input type="text" id="question" placeholder="اسأل عن أفضل الفرص..." required style="flex:1;border-radius:6px;border:1px solid #ccc;padding:8px;">
        <button type="submit" style="background:#008060;color:white;border:none;border-radius:6px;padding:8px 12px;">إرسال</button>
      </form>
  </div>

  <div class="dashboard">
    <a class="card" href="investor_profile.php">ملفي الشخصي</a>
    <a class="card" href="opportunities.php">🔍 استكشاف الفرص</a>
    <a class="card" href="my_investments.php">📂 استثماراتي</a>
    <a class="card" href="recommended_projects.php">📈 الفرص الموصى بها</a>
  </div>

  <div class="reviews-section">
    <h2 class="reviews-header">
      آراء المستثمرين 
    </h2>
    <div class="reviews-list">
      <?php if(empty($reviews)): ?>
        <div style="color:#888; font-size:1.1em; margin:25px 0;">لا توجد آراء منشورة حتى الآن.</div>
      <?php else: foreach($reviews as $review): ?>
        <div class="review-box">
          <div class="review-text"><?= htmlspecialchars($review['comment']) ?></div>
          <div class="review-stars">
            <?php for($i=1;$i<=5;$i++): ?>
              <span style="color:<?= $i <= intval($review['overall_star']) ? '#fec340' : '#e0e1e7' ?>">★</span>
            <?php endfor; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

<script>
function openModal() {
  document.getElementById('modal-bg').style.display = 'block';
  document.getElementById('modal-content').style.display = 'block';
}
function closeModal() {
  document.getElementById('modal-bg').style.display = 'none';
  document.getElementById('modal-content').style.display = 'none';
}
function toggleOpenAIChat() {
    const box = document.getElementById("openai-chat-box");
    box.style.display = (box.style.display === "none" || box.style.display === "") ? "block" : "none";
}

document.getElementById("openaiForm").onsubmit = function(e) {
    e.preventDefault();
    const q = document.getElementById("question").value.trim();
    if (!q) return;
    addOpenAImsg(q, 'right');
    document.getElementById("question").value = '';
    document.getElementById("question").focus();

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "openai_chat.php");
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
      addOpenAImsg(xhr.responseText, 'left');
      document.getElementById("openai-messages").scrollTop = document.getElementById("openai-messages").scrollHeight;
    };
    addOpenAImsg("...", 'left', 'ai-waiting');
    xhr.send("question=" + encodeURIComponent(q));
};

function addOpenAImsg(msg, align, extraClass) {
    const msgBox = document.getElementById("openai-messages");
    if (extraClass === 'ai-waiting') {
        msgBox.innerHTML += `<div style="text-align:left;opacity:.6;margin:8px 0 2px 0;"><i>${msg}</i></div>`;
        return;
    }
    msgBox.innerHTML += `<div style="margin:8px 0;text-align:${align};background:${align==='right'?'#e9ffe4':'#f4f4f4'};padding:7px 10px;border-radius:8px;max-width:90%;display:inline-block;">${msg.replace(/\n/g, '<br>')}</div>`;
    msgBox.scrollTop = msgBox.scrollHeight;
}
</script>
</body>
</html>
