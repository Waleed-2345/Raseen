<?php
session_start();  
if (!isset($_SESSION['investor_id'])) {
    header("Location: signin.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
if (!isset($_GET['id'])) {
    die("لم يتم تحديد المشروع");
}
$project_id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب بيانات المشروع ورائد الأعمال
$project_stmt = $conn->prepare("
  SELECT p.*, CONCAT(e.first_name, ' ', e.last_name) AS owner_name 
  FROM projects p 
  JOIN entrepreneurs e ON p.entrepreneur_id = e.ID 
  WHERE p.id = ? LIMIT 1
");
$project_stmt->bind_param("i", $project_id);
$project_stmt->execute();
$project_q = $project_stmt->get_result();
if (!$project_q || $project_q->num_rows == 0) {
    die("المشروع غير موجود");
}
$project = $project_q->fetch_assoc();

// جلب آخر investment لهذا المستثمر على هذا المشروع
$inv_stmt = $conn->prepare("
  SELECT * FROM investments 
  WHERE project_id = ? AND investor_id = ? 
  ORDER BY created_at DESC LIMIT 1
");
$inv_stmt->bind_param("ii", $project_id, $investor_id);
$inv_stmt->execute();
$inv_res = $inv_stmt->get_result();
$last_investment = $inv_res->fetch_assoc(); // ممكن يكون null

// جلب conversation إذا موجود
$conv_stmt = $conn->prepare("
  SELECT id FROM conversations 
  WHERE project_id=? AND investor_id=? AND entrepreneur_id=? LIMIT 1
");
$conv_stmt->bind_param("iii", $project_id, $investor_id, $project['entrepreneur_id']);
$conv_stmt->execute();
$conv_res = $conv_stmt->get_result();
$conversation = $conv_res->fetch_assoc(); // ممكن يكون null

$conn->close();

$field_map = [
    'logistics'   => 'لوجستي',
    'tech'        => 'تقني',
    'tourism'     => 'سياحي',
    'business'    => 'تجاري',
    'industrial'  => 'صناعي',
    'realestate'  => 'عقاري',
    'real_estate' => 'عقاري'
];
$field_ar = isset($field_map[$project['field']]) ? $field_map[$project['field']] : $project['field'];

$financeType = ($project['finance_type'] === 'equity') ? 'استثمار' : 'قرض';
if ($financeType === 'استثمار') {
    $reason = !empty($project['investment_reason_equity']) ? $project['investment_reason_equity'] : $project['investment_reason'];
    $requested_amount = $project['requested_amount_equity'];
    $investorShare = $project['investor_share'] > 0 ? $project['investor_share'] . '%' : '';
} else {
    $reason = !empty($project['loan_reason']) ? $project['loan_reason'] : $project['investment_reason'];
    $requested_amount = $project['loan_amount'];
    $investorShare = '';
}

function show_if_valid($label, $value, $suffix = '') {
    if ($value !== null && $value !== "" && $value != 0 && $value != '0.00') {
        echo '<div class="detail-row"><span class="detail-label">'.$label.'</span><span class="detail-value">'.htmlspecialchars($value).$suffix.'</span></div>';
    }
}

// نص الزر وحالة الصفقة
$quick_label = "";
$status_badge = "";
$lastAccepted = false;
if ($last_investment) {
    $st = $last_investment['status'];
    if ($st === 'accepted') {
        $quick_label = "تم الاتفاق";
        $status_badge = "<div style='margin:8px 0; padding:8px; background:#e6ffe6; border:1px solid #1f8f3e; border-radius:6px; display:inline-block;'>Status: Accepted</div>";
        $lastAccepted = true;
    } elseif ($st === 'negotiating') {
        $quick_label = "استثمر الآن";
        $status_badge = "<div style='margin:8px 0; padding:8px; background:#fff8e1; border:1px solid #ffb300; border-radius:6px; display:inline-block;'>Status: Negotiating</div>";
    } elseif ($st === 'pending') {
        $quick_label = "استثمر الآن";
        $status_badge = "<div style='margin:8px 0; padding:8px; background:#f0f4ff; border:1px solid #3f51b5; border-radius:6px; display:inline-block;'>Status: Pending</div>";
    } elseif ($st === 'rejected') {
        $quick_label = "استثمر الآن";
        $status_badge = "<div style='margin:8px 0; padding:8px; background:#ffe6e6; border:1px solid #d32f2f; border-radius:6px; display:inline-block;'>Status: Rejected</div>";
    }
}
if ($quick_label === "") {
    $quick_label = "استثمر الآن";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تفاصيل المشروع: <?php echo htmlspecialchars($project['project_name']); ?></title>
<style>
  body { font-family: 'Segoe UI', Tahoma; background: #f7faf7; margin:0; padding:0; }
  .detail-container { max-width:650px; margin:40px auto; background:#fff; border-radius:14px; box-shadow:0 6px 30px #00968817; padding:30px 24px; }
  .detail-title { font-size:1.5em; font-weight:bold; color:#11775d; margin-bottom:15px;}
  .detail-row { margin-bottom:14px; }
  .detail-label { font-weight:600; color:#00796b; min-width: 130px; display: inline-block;}
  .detail-value { color:#2d2d2d; font-weight:500; }
  .detail-actions { margin-top:34px; text-align:center; position: relative; }
  .btn { display:inline-block; padding:10px 20px; margin:0 6px; border-radius:8px; border:none; text-decoration:none; font-weight:700; font-size:1em; cursor:pointer; transition:0.19s; background:#1f8f3e; color:#fff; min-width:120px; }
  .btn:hover { filter:brightness(1.05); }
  .btn-back { background:#d5ede3; color:#155846; }
  .btn-back:hover { background:#b9e4d4;}
  .btn-secondary { background:#1057a3; }
  #actionMessage { margin:10px auto 0; max-width:600px; padding:10px 14px; border-radius:6px; display:none; font-weight:600; }

  /* Modal styles */
  #dealModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.24); z-index:2000; align-items:center; justify-content:center; }
  #dealModal .modal-box { background:#fff; border-radius:12px; max-width:370px; width:96vw; padding:22px 18px; position:relative; font-family:Arial,sans-serif; direction:rtl; box-shadow:0 8px 38px rgba(0,0,0,0.18);}
  #dealModal h3 { margin:0 0 18px 0; color:#11775d; font-size:1.2em;}
  #dealModal label { display:block; margin-bottom:7px; color:#155846;}
  #dealModal input { width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; font-size:1em; box-sizing:border-box; margin-bottom:10px;}
  #dealModal .actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap;}
  #dealModal button { flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
  #modalSubmit { background:#057d38; color:#fff; }
  #modalCancel { background:#ccc; color:#333; }
  #modalClose { position:absolute; top:8px; left:8px; background:none; border:none; font-size:22px; cursor:pointer; }
  #modalError { color:#a00; margin-bottom:8px; display:none; }
</style>
</head>
<body>
  <div class="detail-container">
    <div class="detail-title"><?php echo htmlspecialchars($project['project_name']); ?></div>
    <?php echo $status_badge; ?>
    <?php show_if_valid('صاحب المشروع:', $project['owner_name']); ?>
    <?php show_if_valid('نبذة عن المشروع:', $project['project_summary']); ?>
    <?php show_if_valid('المنطقة:', $project['region']); ?>
    <?php show_if_valid('الموقع:', $project['city']); ?>
    <?php show_if_valid('القطاع:', $field_ar); ?>
    <?php show_if_valid('نوع التمويل:', $financeType); ?>
    <?php show_if_valid('قيمة التمويل المطلوبة:', $requested_amount, ' ريال'); ?>
    <?php show_if_valid('الغرض من التمويل:', $reason); ?>
    <?php if($financeType === 'استثمار') show_if_valid('الحصة الاستثمارية:', $investorShare); ?>
    <?php show_if_valid('عدد الموظفين:', $project['team_size']); ?>
    <?php show_if_valid('قيمة الإيجار الشهري:', $project['rent_cost'], ' ريال'); ?>
    <?php show_if_valid('المصاريف التشغيلية الشهرية:', $project['operating_costs'], ' ريال'); ?>
    <?php show_if_valid('ميزانية التسويق:', $project['marketing_cost'], ' ريال'); ?>
    <?php show_if_valid('تقييم موثوقية المشروع:', $project['trust_score']); ?>

    <div class="detail-actions">
      <!-- زر "أنا مهتم" -->
      <a class="btn btn-secondary" href="interested_chat.php?project_id=<?= $project['id'] ?>">
        أنا مهتم
      </a>

      <button id="quickDealBtn" class="btn" <?= $lastAccepted ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : '' ?>>
        <?= htmlspecialchars($quick_label); ?>
      </button>

      <?php if ($financeType === 'استثمار'): ?>
        <button id="negotiateBtn" class="btn" <?= $lastAccepted ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : '' ?>>
          تفاوض على النسبة
        </button>
      <?php endif; ?>

      <a class="btn btn-back" href="javascript:history.back();">عودة</a>
      <div id="actionMessage"></div>
    </div>
  </div>

  <div id="dealModal">
    <div class="modal-box">
      <button id="modalClose" aria-label="إغلاق">&times;</button>
      <h3 id="modalTitle">تفاوض على الاستثمار</h3>
      <div id="modalBody">
        <label for="modalAmount">المبلغ المقترح (ريال):</label>
        <input id="modalAmount" type="number" min="1" step="0.01" placeholder="مثال: 50000">
        <div id="equityFields">
          <label for="modalEquity">النسبة (%)</label>
          <input id="modalEquity" type="number" min="0.01" step="0.01" placeholder="مثال: 10">
        </div>
        <div id="modalError"></div>
      </div>
      <div class="actions">
        <button id="modalSubmit" type="button">تنفيذ التفاوض</button>
        <button id="modalCancel" type="button">إلغاء</button>
      </div>
    </div>
  </div>

<script>
const projectId = <?= $project_id ?>;
const investorId = <?= $investor_id ?>;
const entrepreneurId = <?= $project['entrepreneur_id'] ?>;
const trustScore = <?= intval($project['trust_score'] ?? 0) ?>;
const requestedAmount = <?= floatval($requested_amount) ?>;
const lastAccepted = <?= ($last_investment && $last_investment['status'] === 'accepted') ? 'true' : 'false'; ?>;
let isNegotiation = false;

const modal = document.getElementById("dealModal");
const modalTitle = document.getElementById("modalTitle");
const equityFields = document.getElementById("equityFields");
const amountInput = document.getElementById("modalAmount");
const equityInput = document.getElementById("modalEquity");
const modalError = document.getElementById("modalError");

function showMessage(text, isError = false) {
  const box = document.getElementById("actionMessage");
  box.textContent = text;
  box.style.display = "block";
  box.style.background = isError ? "#ffe6e6" : "#e6ffed";
  box.style.border = isError ? "1px solid #d32f2f" : "1px solid #1f8f3e";
  box.style.color = isError ? "#a00" : "#075a35";
}

function post(formData) {
  fetch("quick_invest.php", { method: "POST", body: formData })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showMessage("خطأ: " + resp.error, true);
      } else if (resp.investment_id && resp.status === 'accepted') {
        window.location.href = "investor_sign.php?investment_id=" + resp.investment_id;
      } else {
        showMessage(resp.message || "تم بنجاح");
        setTimeout(() => { location.reload(); }, 1200);
      }
    })
    .catch(e => {
      showMessage("خطأ شبكة: " + e.message, true);
    });
}

document.getElementById("quickDealBtn").onclick = function() {
  if (lastAccepted) return;
  if (confirm("هل أنت متأكد أنك تريد استثمار مبلغ <?= number_format($requested_amount) ?> ريال في المشروع؟")) {
    const form = new FormData();
    form.append("project_id", projectId);
    form.append("investor_id", investorId);
    form.append("entrepreneur_id", entrepreneurId);
    form.append("trust_score_at_proposal", trustScore);
    form.append("amount", requestedAmount);
    form.append("type", "equity");
    form.append("equity_percentage", <?= floatval($project['investor_share']) ?>);
    form.append("proposal_notes", "استثمار مباشر بنفس مبلغ المشروع المطلوب.");
    form.append("status", "accepted");
    post(form);
  }
};

document.getElementById("negotiateBtn").onclick = function() {
  if (lastAccepted) return;
  modalError.style.display = "none";
  amountInput.value = "";
  equityInput.value = "";
  modal.style.display = "flex";
};

document.getElementById("modalCancel").onclick = closeModal;
document.getElementById("modalClose").onclick = closeModal;
function closeModal() { modal.style.display = "none"; }

document.getElementById("modalSubmit").onclick = function() {
  modalError.style.display = "none";
  const amount = parseFloat(amountInput.value);
  const equity_percent = parseFloat(equityInput.value);
  if (!amount || amount <= 0) {
    modalError.textContent = "ادخل مبلغ صالح.";
    modalError.style.display = "block";
    return;
  }
  if (!equity_percent || equity_percent <= 0) {
    modalError.textContent = "ادخل نسبة حصة صحيحة.";
    modalError.style.display = "block";
    return;
  }
  const form = new FormData();
  form.append("project_id", projectId);
  form.append("investor_id", investorId);
  form.append("entrepreneur_id", entrepreneurId);
  form.append("trust_score_at_proposal", trustScore);
  form.append("amount", amount);
  form.append("type", "equity");
  form.append("equity_percentage", equity_percent);
  form.append("proposal_notes", `تفاوض: ${equity_percent}% مقابل ${amount} ريال.`);
  form.append("status", "negotiating");
  post(form);
  closeModal();
};
</script>
</body>
</html>




