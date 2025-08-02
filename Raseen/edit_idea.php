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

$idea_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM ideas WHERE id = ? AND entrepreneur_id = ?");
$stmt->bind_param("ii", $idea_id, $entrepreneur_id);
$stmt->execute();
$result = $stmt->get_result();
$idea = $result->fetch_assoc();

if (!$idea) {
    echo "الفكرة غير موجودة أو لا تملك صلاحية تعديلها.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['idea_name'];
    $summary = $_POST['idea_summary'];
    $problem = $_POST['problem_statement'];
    $solution = $_POST['proposed_solution'];
    $stmt = $conn->prepare("UPDATE ideas SET idea_name=?, idea_summary=?, problem_statement=?, proposed_solution=? WHERE id=? AND entrepreneur_id=?");
    $stmt->bind_param("ssssii", $name, $summary, $problem, $solution, $idea_id, $entrepreneur_id);
    $stmt->execute();
    header("Location: ideas_projects.php");
    exit;
}

function translateField($field) {
    $fields = [
        'tech' => 'تقني', 'business' => 'تجاري', 'industrial' => 'صناعي', 'logistics' => 'لوجستي',
        'admin' => 'إداري', 'realestate' => 'عقاري', 'tourism' => 'سياحي', 'other' => 'أخرى'
    ];
    return $fields[$field] ?? $field;
}
?>
<html>
<head>
  <title>تعديل فكرة المشروع</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f8f8f8; padding: 40px; }
    .form-box { max-width: 850px; margin: auto; background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #ccc; }
    label { display: block; margin-top: 15px; font-weight: bold; color: #333; }
    input[type="text"], textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
    input[readonly], textarea[readonly] { background-color: #f2f2f2; color: #666; }
    button { margin-top: 25px; padding: 10px 20px; background: #007b5e; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
    a.back { display: inline-block; margin-top: 20px; text-decoration: none; color: #007b5e; }
    h1 { margin-bottom: 30px; color: #004d40; }
  </style>
</head>
<body>
<div class="form-box">
  <h1>تعديل فكرة المشروع</h1>
  <form method="post">
    <label>اسم الفكرة:</label>
    <input type="text" name="idea_name" value="<?= htmlspecialchars($idea['idea_name']) ?>">

    <label>الوصف المختصر:</label>
    <textarea name="idea_summary" rows="3"><?= htmlspecialchars($idea['idea_summary']) ?></textarea>

    <label>المشكلة المستهدفة:</label>
    <textarea name="problem_statement" rows="3"><?= htmlspecialchars($idea['problem_statement']) ?></textarea>

    <label>الحل المقترح:</label>
    <textarea name="proposed_solution" rows="3"><?= htmlspecialchars($idea['proposed_solution']) ?></textarea>

    <label>المجال:</label>
    <input type="text" value="<?= translateField($idea['field']) ?>" readonly>

    <?php if (!empty($idea['target'])): ?>
    <label>الفئة المستهدفة:</label>
    <input type="text" value="<?= htmlspecialchars($idea['target']) ?>" readonly>
    <?php endif; ?>

    <?php if ($idea['has_experience'] === 'نعم'): ?>
    <label>تمتلك خبرة في هذا المجال.</label>
    <?php endif; ?>

    <?php if ($idea['has_partner'] === 'نعم'): ?>
    <label>يوجد شريك في الفكرة.</label>
    <?php endif; ?>

    <label>الهدف من عرض الفكرة:</label>
    <input type="text" value="<?= htmlspecialchars($idea['investment_goal']) ?>" readonly>

    <?php if ($idea['investment_goal'] === 'بيع الفكرة'): ?>
    <label>السعر المطلوب:</label>
    <input type="text" value="<?= number_format($idea['sell_price'], 2) ?> ريال" readonly>
    <?php endif; ?>

    <label>نسبة الجاهزية:</label>
    <input type="text" value="<?= $idea['readiness_level'] ?>%" readonly>

    <label>نسبة موثوقية الفكرة:</label>
    <input type="text" value="<?= isset($idea['score']) ? $idea['score'] . '%' : 'غير محسوبة' ?>" readonly>

    <button type="submit">💾 حفظ التعديلات</button>
  </form>
  <a href="ideas_projects.php" class="back">← العودة</a>
</div>
</body>
</html>
