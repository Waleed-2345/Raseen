<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: signin.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
$investment_id = intval($_GET['investment_id'] ?? 0);
if ($investment_id <= 0) {
    die("الاستثمار غير محدد بشكل صحيح.");
}

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// التحقق أن هذا المستثمر يملك هذا الاستثمار وجلب بيانات المشروع
$stmt = $conn->prepare("
    SELECT inv.id, inv.amount, inv.equity_percentage, inv.status,
           p.project_name, p.city, p.region
    FROM investments inv
    JOIN projects p ON inv.project_id = p.id
    WHERE inv.id = ? AND inv.investor_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $investment_id, $investor_id);
$stmt->execute();
$inv_res = $stmt->get_result();
if (!$inv_res || $inv_res->num_rows === 0) {
    die("ليس لديك صلاحية عرض التحديثات لهذا الاستثمار.");
}
$investment = $inv_res->fetch_assoc();
$stmt->close();

// جلب كل التحديثات المرتبطة بهذا الاستثمار
$upd_stmt = $conn->prepare("
    SELECT update_text, pdf_report, created_at
    FROM project_updates
    WHERE investment_id = ?
    ORDER BY created_at DESC
");
$upd_stmt->bind_param("i", $investment_id);
$upd_stmt->execute();
$updates_res = $upd_stmt->get_result();
$updates = [];
while ($u = $updates_res->fetch_assoc()) {
    $updates[] = $u;
}
$upd_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تحديثات الاستثمار #<?= htmlspecialchars($investment_id) ?></title>
  <style>
    body { font-family: 'Segoe UI', Tahoma; background:#f7faf7; margin:0; padding:0; }
    .wrapper { max-width: 900px; margin: 40px auto; background:#fff; border-radius:12px; padding:28px; box-shadow:0 6px 30px rgba(0,0,0,0.05); }
    h1 { margin-top:0; color:#1f5fbf; }
    .meta { display:flex; gap:16px; flex-wrap: wrap; margin-bottom:18px; }
    .badge { background:#e5f2fb; padding:6px 12px; border-radius:8px; font-weight:600; }
    .status { font-weight:bold; }
    .updates { margin-top:25px; }
    .update { border:1px solid #dce8f1; border-radius:10px; padding:16px; margin-bottom:16px; background:#f9fcff; position:relative; }
    .update h3 { margin:0 0 6px; font-size:1.1em; }
    .update .date { font-size:.85em; color:#555; }
    .update .text { margin:10px 0; white-space:pre-wrap; }
    .pdf { margin-top:6px; display:inline-block; background:#11775d; color:#fff; padding:6px 14px; border-radius:6px; text-decoration:none; font-size:.9em; }
    .no-updates { text-align:center; padding:28px; color:#666; font-size:1em; }
    .back { display:inline-block; margin-bottom:12px; text-decoration:none; color:#11775d; }
  </style>
</head>
<body>
  <div class="wrapper">
    <a href="my_investments.php" class="back">← رجوع إلى استثماراتي</a>
    <h1>تحديثات الاستثمار في: <?= htmlspecialchars($investment['project_name']) ?></h1>
    <div class="meta">
      <div class="badge">المبلغ: <?= number_format($investment['amount']) ?> ريال</div>
      <div class="badge">الحصة: <?= htmlspecialchars($investment['equity_percentage']) ?: '-' ?>%</div>
      <div class="badge status">الحالة: 
        <?php 
          $st = $investment['status'];
          if ($st === 'accepted') echo "موقّع";
          elseif ($st === 'pending') echo "بانتظار";
          elseif ($st === 'negotiating') echo "تفاوض";
          elseif ($st === 'rejected') echo "مرفوض";
          else echo htmlspecialchars($st);
        ?>
      </div>
      <div class="badge"><?= htmlspecialchars($investment['city']) ?> - <?= htmlspecialchars($investment['region']) ?></div>
    </div>

    <div class="updates">
      <?php if (empty($updates)): ?>
        <div class="no-updates">لم يتم إرسال أي تحديث حتى الآن.</div>
      <?php else: ?>
        <?php foreach ($updates as $u): ?>
          <div class="update">
            <h3>تحديث بتاريخ <?= date("Y-m-d", strtotime($u['created_at'])) ?></h3>
            <div class="date">وقت الإرسال: <?= date("H:i:s", strtotime($u['created_at'])) ?></div>
            <?php if (!empty($u['update_text'])): ?>
              <div class="text"><?= nl2br(htmlspecialchars($u['update_text'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($u['pdf_report'])): ?>
              <a class="pdf" href="uploads/<?= rawurlencode($u['pdf_report']) ?>" target="_blank">تحميل ملف التقرير (PDF)</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
