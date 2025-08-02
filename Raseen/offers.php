<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}
$entrepreneur_id = $_SESSION['entrepreneur_id'];

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) die("فشل الاتصال: " . $conn->connect_error);

function canAddUpdate($last_update_at) {
    if (!$last_update_at) return true;
    $last = new DateTime($last_update_at);
    $now = new DateTime();
    $diff = $now->diff($last);
    if ($diff->y > 0) return true;
    if ($diff->m >= 3) return true;
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['offer_id'])) {
    $offer_id = intval($_POST['offer_id']);
    $action = $_POST['action'] === 'accept' ? 'accepted' : 'rejected';

    $upd = $conn->prepare("UPDATE investments SET status = ? WHERE id = ? AND entrepreneur_id = ?");
    $upd->bind_param("sii", $action, $offer_id, $entrepreneur_id);
    $upd->execute();
    $upd->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_update'], $_POST['investment_id'])) {
    $investment_id = intval($_POST['investment_id']);
    $update_text = trim($_POST['update_text']);
    $pdf_name = null;

    if (!empty($_FILES['pdf_report']['name'])) {
        $pdf = $_FILES['pdf_report'];
        $ext = strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION));
        if ($ext === "pdf" && $pdf['size'] <= 5 * 1024 * 1024) {
            $pdf_name = 'update_' . time() . '_' . rand(1000, 9999) . '.pdf';
            move_uploaded_file($pdf['tmp_name'], __DIR__ . '/uploads/' . $pdf_name);
        }
    }

    $check = $conn->prepare("SELECT entrepreneur_signed, investor_signed, project_id FROM investments WHERE id = ? AND entrepreneur_id = ? LIMIT 1");
    $check->bind_param("ii", $investment_id, $entrepreneur_id);
    $check->execute();
    $r = $check->get_result();
    if ($r && $r->num_rows) {
        $row = $r->fetch_assoc();
        if (intval($row['entrepreneur_signed']) && intval($row['investor_signed'])) {
            $stmtLast = $conn->prepare("SELECT MAX(created_at) AS last_update_at FROM project_updates WHERE investment_id = ?");
            $stmtLast->bind_param("i", $investment_id);
            $stmtLast->execute();
            $resLast = $stmtLast->get_result();
            $last_update_at = null;
            if ($resLast && $resLast->num_rows) {
                $d = $resLast->fetch_assoc();
                $last_update_at = $d['last_update_at'];
            }
            $stmtLast->close();

            if (canAddUpdate($last_update_at)) {
                $project_id = intval($row['project_id']);
                $stmt = $conn->prepare("INSERT INTO project_updates (project_id, investment_id, update_text, pdf_report, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiss", $project_id, $investment_id, $update_text, $pdf_name);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    $check->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$query = "
SELECT 
  inv.*, 
  p.project_name, 
  i.first_name, i.last_name,
  (SELECT MAX(created_at) FROM project_updates pu WHERE pu.investment_id = inv.id) AS last_update_at
FROM investments inv
JOIN projects p ON inv.project_id = p.id
JOIN investors i ON inv.investor_id = i.ID
WHERE inv.entrepreneur_id = ?
ORDER BY inv.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $entrepreneur_id);
$stmt->execute();
$result = $stmt->get_result();
$offers = [];
while ($row = $result->fetch_assoc()) {
    $offers[] = $row;
}
$stmt->close();
$conn->close();
?>
<html>
<head>
  <meta charset="UTF-8">
  <title>سير المشروع</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma; background: #f9fdfb; margin:0; padding:0;}
    h2 { text-align: center; color: #14497b; margin-top: 36px; font-weight:bold; letter-spacing:.5px;}
    .offers-table { width: 96%; margin: 30px auto 0; border-collapse: collapse; background: #fff; border-radius: 13px; box-shadow: 0 4px 28px #b8eedb22; }
    .offers-table th, .offers-table td { padding: 13px 10px; border-bottom: 1px solid #e0eee8; text-align: center; }
    .offers-table th { background: #e5f2fb; color: #14497b; font-size: 1.03em;}
    .offer-type-badge { border-radius: 7px; padding: 4px 13px; font-size: 0.99em; font-weight: 600;}
    .offer-equity { background: #e4ffea; color: #14b05a;}
    .offer-loan   { background: #f7faff; color: #155c8c;}
    .btn-accept, .btn-reject {
      padding: 7px 17px; border-radius: 7px; border: none; font-weight: 700; margin-right: 7px;
      cursor: pointer; transition: .13s; font-size: 1em;
    }
    .btn-accept { background: #1f5fbf; color: #fff; }
    .btn-accept:hover { filter:brightness(1.1); }
    .btn-reject { background: #e83d5d; color: #fff;}
    .btn-reject:hover { filter:brightness(1.1); }
    .status-badge {
      border-radius: 8px; padding: 5px 12px; font-size: .99em; font-weight:600;
      display:inline-block; min-width: 80px;
    }
    .status-negotiating { background:#fff8e1; color:#b37409; border:1px solid #ffeeb3;}
    .status-accepted { background:#e9faef; color:#177d43; border:1px solid #42d993;}
    .status-pending { background:#f0f4ff; color:#0178c0; border:1px solid #b2dcff;}
    .status-rejected { background:#ffe6e6; color:#e83d5d; border:1px solid #fbb7b7;}
    .signed-btn { background: #4a9f4a !important; color: #fff !important; cursor: default; border:none; padding:7px 14px; border-radius:7px; }
    .add-update-btn { background:#174c7f;color:#fff;padding:8px 25px;border:none;border-radius:8px;font-weight:bold;cursor:pointer; margin-bottom:7px;}
    .add-update-form { display:none; background:#f4f8fb; border:1px solid #b0c9df; border-radius:9px; padding:18px 14px; margin:10px 0;}
    .add-update-form textarea {width:95%;min-height:65px;margin-bottom:7px;padding:7px; border-radius:5px; border:1px solid #bed8ef;}
    .add-update-form input[type=file] { margin-bottom:7px; }
    .add-update-form button { background:#154678;color:#fff;padding:8px 18px;border:none;border-radius:7px;font-weight:600;}
    .add-update-form .close-btn {background: #eee;color: #555; margin-right:8px; border:none; padding:7px 14px; border-radius:6px; cursor:pointer;}
    .info-note { background:#f0f4ff; padding:8px 12px; border-radius:6px; display:inline-block; font-size:.95em; color:#055; }
    @media (max-width: 600px){
      .offers-table, .offers-table th, .offers-table td { font-size: .98em;}
    }
  </style>
</head>
<body>
  <h2>📁 سير المشروع</h2>
  <table class="offers-table">
    <tr>
      <th>المشروع</th>
      <th>المستثمر</th>
      <th>المبلغ</th>
      <th>نوع العرض</th>
      <th>الحالة</th>
      <th>ملاحظات/تفاصيل</th>
      <th>متابعة</th>
    </tr>
    <?php if (empty($offers)): ?>
      <tr><td colspan="7" style="text-align:center;color:#888;">لا توجد مشاريع أو عروض بعد.</td></tr>
    <?php else: foreach($offers as $off): ?>
      <tr>
        <td><?= htmlspecialchars($off['project_name']) ?></td>
        <td><?= htmlspecialchars($off['first_name'].' '.$off['last_name']) ?></td>
        <td><?= number_format($off['amount']) ?> ريال</td>
        <td>
          <?php if ($off['type'] == 'equity'): ?>
            <span class="offer-type-badge offer-equity">استثمار<?= isset($off['equity_percentage']) ? ' (' . floatval($off['equity_percentage']) . '%)' : '' ?></span>
          <?php else: ?>
            <span class="offer-type-badge offer-loan">قرض<?= isset($off['loan_term_months']) ? ' (' . intval($off['loan_term_months']) . ' شهر)' : '' ?></span>
          <?php endif; ?>
        </td>
        <td>
          <?php
            $status = $off['status'];
            $statusClass = [
              'negotiating'=>'status-negotiating',
              'accepted'=>'status-accepted',
              'pending'=>'status-pending',
              'rejected'=>'status-rejected'
            ][$status] ?? '';
          ?>
          <span class="status-badge <?= $statusClass ?>">
            <?php
              if ($status == 'negotiating') echo 'تفاوض';
              elseif ($status == 'accepted') echo 'مقبول';
              elseif ($status == 'pending') echo 'بانتظار';
              elseif ($status == 'rejected') echo 'مرفوض';
              else echo htmlspecialchars($status);
            ?>
          </span>
        </td>
        <td><?= htmlspecialchars($off['proposal_notes'] ?? '') ?></td>
        <td>
          <?php
            if ($off['status'] === 'negotiating' || $off['status'] === 'pending'):
          ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="offer_id" value="<?= $off['id'] ?>">
              <button class="btn-accept" name="action" value="accept">قبول الاتفاق</button>
              <button class="btn-reject" name="action" value="reject">رفض</button>
            </form>
          <?php
            elseif ($off['status'] === 'accepted'):
              $entrepreneur_signed = intval($off['entrepreneur_signed']);
              $investor_signed = intval($off['investor_signed']);
              if (!$entrepreneur_signed):
          ?>
                <a href="entrepreneur_sign.php?investment_id=<?= $off['id'] ?>" class="btn-accept" style="padding:7px 14px;font-size:0.97em;">توقيع الاتفاقية</a>
          <?php
              elseif ($entrepreneur_signed && !$investor_signed):
                echo "<span class='status-badge status-pending'>بانتظار توقيع المستثمر</span>";
              else:
          ?>
                <button class="signed-btn" disabled>تم التوقيع النهائي</button>
          <?php
              endif;
            else:
              echo "<span style='color:#bbb;'>-</span>";
            endif;
          ?>
        </td>
      </tr>

      <?php 
        $entrepreneur_signed = intval($off['entrepreneur_signed']);
        $investor_signed = intval($off['investor_signed']);
        $showUpdateSection = ($off['status'] === 'accepted' && $entrepreneur_signed && $investor_signed);
        $allowedToAdd = canAddUpdate($off['last_update_at']);
        if ($showUpdateSection):
      ?>
        <tr>
          <td colspan="7" style="text-align:center;">
            <?php if ($allowedToAdd): ?>
              <button class="add-update-btn" data-id="<?= $off['id'] ?>">
                + إضافة تحديث/تقرير ربع سنوي
              </button>
              <form id="add-update-<?= $off['id'] ?>" class="add-update-form" method="post" enctype="multipart/form-data" style="display:none;">
                <input type="hidden" name="add_update" value="1">
                <input type="hidden" name="investment_id" value="<?= $off['id'] ?>">
                <textarea name="update_text" placeholder="أدخل ملخص التحديث أو التقرير..." required></textarea><br>
                <input type="file" name="pdf_report" accept="application/pdf"><br>
                <button type="submit">حفظ التحديث</button>
                <button type="button" class="close-btn">إغلاق</button>
              </form>
            <?php else: 
                $last = new DateTime($off['last_update_at']);
                $nextAllowed = $last->modify('+3 months');
            ?>
              <div class="info-note">
                تقدر تضيف تحديث بعد: <?= $nextAllowed->format('Y-m-d') ?>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>

    <?php endforeach; endif; ?>
  </table>

  <script>
    document.addEventListener('click', function(e) {
      if (e.target.matches('.add-update-btn')) {
        const id = e.target.dataset.id;
        const form = document.getElementById(`add-update-${id}`);
        if (form) {
          form.style.display = 'block';
          e.target.style.display = 'none';
        }
      }

      if (e.target.matches('.close-btn')) {
        const form = e.target.closest('.add-update-form');
        if (!form) return;
        form.style.display = 'none';
        const idInput = form.querySelector('input[name="investment_id"]');
        if (!idInput) return;
        const id = idInput.value;
        const addBtn = document.querySelector(`.add-update-btn[data-id="${id}"]`);
        if (addBtn) {
          addBtn.style.display = 'inline-block';
        }
      }
    });
  </script>
</body>
</html>

