<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: signin.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$entrepreneur_id = $_SESSION['entrepreneur_id'];
if (!isset($_GET['id'])) {
    header("Location: ideas_projects.php");
    exit;
}

$project_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND entrepreneur_id = ?");
$stmt->bind_param("ii", $project_id, $entrepreneur_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    echo "المشروع غير موجود أو لا تملكه.";
    exit;
}

function translateField($field) {
    $fields = [
        'tech' => 'تقني',
        'business' => 'تجاري',
        'industrial' => 'صناعي',
        'logistics' => 'لوجستي',
        'admin' => 'إداري',
        'realestate' => 'عقاري',
        'tourism' => 'سياحي'
    ];
    return $fields[$field] ?? $field;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['project_name'];
    $summary = $_POST['project_summary'];

    $stmt = $conn->prepare("UPDATE projects SET project_name=?, project_summary=? WHERE id=? AND entrepreneur_id=?");
    $stmt->bind_param("ssii", $name, $summary, $project_id, $entrepreneur_id);
    $stmt->execute();

    header("Location: ideas_projects.php");
    exit;
}
?>

<html>
<head>
  <title>تعديل المشروع</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f8f8f8;
      padding: 40px;
    }

    .form-box {
      max-width: 850px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      border: 1px solid #ccc;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #333;
    }

    input[type="text"],
    input[type="number"],
    textarea {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    input[readonly],
    textarea[readonly] {
      background-color: #f2f2f2;
      color: #666;
    }

    button {
      margin-top: 25px;
      padding: 10px 20px;
      background: #007b5e;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }

    a.back {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #007b5e;
    }

    h1 {
      margin-bottom: 30px;
      color: #004d40;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h1>تعديل بيانات المشروع</h1>
  <form method="post">
    <label>اسم المشروع:</label>
    <input type="text" name="project_name" value="<?= htmlspecialchars($project['project_name'] ?? '') ?>">

    <label>نبذة تعريفية:</label>
    <textarea name="project_summary" rows="3"><?= htmlspecialchars($project['project_summary'] ?? '') ?></textarea>

    <label>رقم السجل التجاري:</label>
    <input type="text" value="<?= $project['commercial_registration'] ?? '' ?>" readonly>

    <label>المجال:</label>
    <input type="text" value="<?= translateField($project['field'] ?? '') ?>" readonly>

    <label>المنطقة / المدينة:</label>
    <input type="text" value="<?= ($project['region'] ?? '') . ' / ' . ($project['city'] ?? '') ?>" readonly>

    <label>عدد أعضاء الفريق:</label>
    <input type="number" value="<?= $project['team_size'] ?? '' ?>" readonly>

    <label>(ريال)معدل الرواتب الشهرية:</label>
    <input type="text" value="<?= isset($project['salary_range']) ? number_format($project['salary_range']) . ' ريال' : '' ?>" readonly>

    <label>الإيجار الشهري (ريال):</label>
    <input type="text" value="<?= isset($project['rent_cost']) ? number_format($project['rent_cost']) . ' ريال' : '' ?>" readonly>

    <label>تكاليف التشغيل الأخرى (ريال):</label>
    <input type="text" value="<?= isset($project['operating_costs']) ? number_format($project['operating_costs']) . ' ريال' : '' ?>" readonly>

    <label>ميزانية التسويق الشهرية (ريال):</label>
    <input type="text" value="<?= isset($project['marketing_cost']) ? number_format($project['marketing_cost']) . ' ريال' : '' ?>" readonly>

    <label>تكاليف إضافية (ريال):</label>
    <input type="text" value="<?= isset($project['other_costs']) ? number_format($project['other_costs']) . ' ريال' : '' ?>" readonly>

    <label>نوع التمويل:</label>
    <input type="text" value="<?= ($project['finance_type'] === 'loan' ? 'قرض' : 'استثمار') ?>" readonly>

    <?php if ($project['finance_type'] === 'equity'): ?>
      <label>العائد الشهري قبل الاستثمار:</label>
      <input type="text" value="<?= isset($project['expected_monthly_return_before']) ? number_format($project['expected_monthly_return_before']) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>العائد المتوقع بعد الاستثمار:</label>
      <input type="text" value="<?= isset($project['expected_monthly_return']) ? number_format($project['expected_monthly_return']) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>المبلغ المطلوب:</label>
      <input type="text" value="<?= isset($project['requested_amount_equity']) ? number_format($project['requested_amount_equity']) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>نسبة المستثمر (%):</label>
      <input type="text" value="<?= $project['investor_share'] ?? '' ?>" readonly>

      <label>سبب طلب الاستثمار:</label>
      <textarea readonly><?= htmlspecialchars($project['investment_reason_equity'] ?? '') ?></textarea>

    <?php elseif ($project['finance_type'] === 'loan'): ?>
      <?php
        $beforeLoan = $project['expected_monthly_return_before'] ?? '';
        $afterLoan = $project['expected_monthly_return'] ?? '';
        $loanAmount = $project['loan_amount'] ?? '';
        $repayment = $project['loan_repayment_type'] ?? '';
        $installment = $project['installment_period'] ?? '';

        $repayment_text = match($repayment) {
            'one_payment' => 'دفعة واحدة بعد 6 أشهر',
            'installments' => 'دفعات منتظمة تبدأ بعد 6 أشهر',
            default => 'غير محددة',
        };
      ?>
      <label>العائد الشهري قبل القرض:</label>
      <input type="text" value="<?= $beforeLoan !== '' ? number_format($beforeLoan) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>العائد المتوقع بعد القرض:</label>
      <input type="text" value="<?= $afterLoan !== '' ? number_format($afterLoan) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>المبلغ المطلوب:</label>
      <input type="text" value="<?= $loanAmount !== '' ? number_format($loanAmount) . ' ريال' : 'غير متوفر' ?>" readonly>

      <label>مدة السداد:</label>
      <input type="text" value="<?= $installment ? $installment . ' شهر' : 'غير محددة' ?>" readonly>

      <label>سبب طلب القرض:</label>
      <textarea><?= htmlspecialchars($project['loan_reason'] ?? '') ?></textarea>
    <?php endif; ?>

    <label>نسبة موثوقية المشروع:</label>
    <input type="text" value="<?= $project['trust_score'] ?? '' ?>" readonly>

    <button type="submit"> حفظ التعديلات</button>
  </form>

  <a href="ideas_projects.php" class="back">← العودة</a>
</div>

</body>
</html>
