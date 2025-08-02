<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}
$entrepreneur_id = $_SESSION['entrepreneur_id'];

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// حذف محادثة
if (isset($_GET['delete_conversation'])) {
    $conv_id = intval($_GET['delete_conversation']);
    $conn->query("DELETE FROM conversations WHERE id=$conv_id AND entrepreneur_id=$entrepreneur_id");
    header("Location: entrepreneur_homepage.php");
    exit;
}

// جلب المحادثات
$conversations = [];
$q = $conn->query("
    SELECT c.id, c.project_id, c.started_at,
           p.project_name,
           i.first_name, i.last_name
    FROM conversations c
    JOIN investors i   ON c.investor_id    = i.ID
    JOIN projects   p  ON c.project_id     = p.id
    WHERE c.entrepreneur_id = $entrepreneur_id
    ORDER BY c.started_at DESC
");
while ($row = $q->fetch_assoc()) {
    $conversations[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>لوحة رائد الأعمال</title>
  <style>
    body { font-family:'Segoe UI', sans-serif; background:#f9fdfb; margin:0; padding:0; direction:rtl; }
    .header { background:#035917; color:#fff; padding:20px; text-align:center; font-size:24px; position:relative; }
    .dashboard { display:flex; flex-wrap:wrap; gap:30px; justify-content:center; padding:60px 20px; }
    .card { background:#fff; width:240px; height:150px; border-radius:12px;
            box-shadow:0 4px 12px rgba(0,0,0,0.07); display:flex;
            align-items:center; justify-content:center; text-decoration:none;
            color:#035917; font-weight:bold; font-size:18px; transition:.3s; }
    .card:hover { background:#eaf5ee; }

    /* ------ صندوق المحادثات (Modal) ------ */
    .modal-bg { display:none; position:fixed; inset:0; background:rgba(36,67,49,0.18); z-index:998; }
    .modal-content {
      position:fixed; top:60px; left:50%; transform:translateX(-50%);
      width:440px; max-width:90vw; background:#fff; border-radius:16px;
      box-shadow:0 6px 38px rgba(23,195,125,0.16); z-index:999;
      animation:popup-in .26s;
    }
    @keyframes popup-in {
      from { transform:translateX(-50%) scale(.9); opacity:.4; }
      to   { transform:translateX(-50%) scale(1);   opacity:1; }
    }
    .modal-header {
      padding:22px 26px 12px; font-size:1.16em; font-weight:800; color:#14833b;
      border-bottom:1px solid #e5f8ee; display:flex; justify-content:space-between; align-items:center;
    }
    .modal-close { background:none; border:none; font-size:1.7em; color:#c1c1c1; cursor:pointer; }
    .modal-close:hover { color:#e83d5d; }
    .convs-list { max-height:370px; overflow-y:auto; padding:10px 18px; }
    .conv-row {
      background:#f9fcfa; border-radius:13px; padding:16px 12px; margin-bottom:13px;
      box-shadow:0 1px 8px rgba(25,195,125,0.07); display:flex; align-items:center;
      justify-content:space-between; cursor:pointer; transition:background .14s,box-shadow .2s;
    }
    .conv-row:hover {
      background:#e9f7ee; box-shadow:0 6px 18px rgba(25,195,125,0.14);
    }
    .conv-details { flex:1; }
    .conv-title { font-size:1.01em; font-weight:700; color:#145529; }
    .conv-meta  { color:#858585; font-size:.98em; margin-top:2px; }
    .conv-btns { display:flex; align-items:center; gap:7px; }
    .conv-delete {
      background:none; border:none; font-size:1.3em; color:#b83030;
      cursor:pointer; transition:color .16s; padding:0;
    }
    .conv-delete:hover { color:#e31d1d; }
    @media (max-width:480px) {
      .modal-content { width:98vw; }
    }

    /* --- أيقونة معلوماتي --- */
    .profile-icon {
      position:absolute;
      top:20px;
      left:20px;
      background:none;
      border:none;
      cursor:pointer;
      padding:0;
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
    function stopClose(e) { e.stopPropagation(); }
  </script>
</head>
<body>

  <!-- الهيدر مع أيقونة معلوماتي -->
  <div class="header">
    منصة رصين – رائد الأعمال

    <!-- أيقونة الرسائل -->
    <button onclick="openModal()"
            style="position:absolute; top:20px; right:20px; background:none; border:none; cursor:pointer;">
      <svg width="27" height="27" fill="#fff" viewBox="0 0 24 24">
        <path d="M20 2H4C2.897 2 2 2.897 2 4V20C2 21.103 2.897 22 4 22H20
                 C21.103 22 22 21.103 22 20V4C22 2.897 21.103 2 20 2ZM20 4L12 13
                 L4 4H20ZM4 20V7.236L11.293 15.707C11.683 16.098 12.317 16.098
                 12.707 15.707L20 7.236V20H4Z"/>
      </svg>
    </button>

    <!-- أيقونة معلوماتي -->
    <a href="entrepreneur_profile.php" class="profile-icon" title="معلوماتي">
      <svg width="27" height="27" fill="#fff" viewBox="0 0 24 24">
        <path d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2A4 4 0 1 1 12 4a4 4 0 0 1 0 8z"/>
      </svg>
    </a>
  </div>

  <!-- صندوق المحادثات -->
  <div id="modal-bg" class="modal-bg" onclick="closeModal()"></div>
  <div id="modal-content" class="modal-content" onclick="stopClose(event)">
    <div class="modal-header">
      <span>كل المحادثات</span>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="convs-list">
      <?php if (empty($conversations)): ?>
        <div style="text-align:center;color:#888;margin-top:60px;">
          لا توجد محادثات حتى الآن
        </div>
      <?php else: ?>
        <?php foreach($conversations as $c): ?>
          <div class="conv-row"
               onclick="window.location.href='entrepreneur_chat.php?conv_id=<?= $c['id'] ?>'">
            <div class="conv-details">
              <div class="conv-title"><?= htmlspecialchars($c['project_name']) ?></div>
              <div class="conv-meta">
                مستثمر: <?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?>
                | منذ: <?= date('Y-m-d', strtotime($c['started_at'])) ?>
              </div>
            </div>
            <button class="conv-delete"
                    onclick="event.stopPropagation();
                             if(confirm('هل تريد حذف المحادثة؟'))
                               location.href='?delete_conversation=<?= $c['id'] ?>';"
                    title="حذف المحادثة">
              🗑️
            </button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- لوحة الخيارات بعد التعديل -->
  <div class="dashboard">
    <a class="card" href="ideas_projects.php">📁 أفكاري ومشاريعي</a>
    <a class="card" href="add_project.php">➕ إضافة مشروع</a>
    <a class="card" href="add_idea.php">🧠 إضافة فكرة</a>
    <a class="card" href="offers.php">🗂️سير المشروع </a>
  </div>

</body>
</html>

