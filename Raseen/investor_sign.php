<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: investor_login.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
$investment_id = intval($_GET['investment_id'] ?? 0);

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب البيانات مع التحقق من صلاحية المستثمر
$stmt = $conn->prepare("
    SELECT 
        i.investor_signed, 
        i.type, 
        p.project_name, 
        i.amount, 
        i.equity_percentage,
        inv.first_name AS investor_fname,
        inv.last_name AS investor_lname,
        inv.national_id AS investor_nid,
        ent.first_name AS entrepreneur_fname,
        ent.last_name AS entrepreneur_lname,
        ent.national_id AS entrepreneur_nid
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN investors inv ON i.investor_id = inv.ID
    JOIN entrepreneurs ent ON p.entrepreneur_id = ent.ID
    WHERE i.id = ? AND i.investor_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $investment_id, $investor_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    die("ليس لديك صلاحية التوقيع على هذه الاتفاقية.");
}
$inv = $result->fetch_assoc();
$investor_signed = intval($inv['investor_signed']);
$finance_type = $inv['type'];
$project_name = $inv['project_name'];
$amount = $inv['amount'];
$equity = $inv['equity_percentage'];
$investor_name = trim($inv['investor_fname'] . ' ' . $inv['investor_lname']);
$investor_id_val = $inv['investor_nid'];
$entrepreneur_name = trim($inv['entrepreneur_fname'] . ' ' . $inv['entrepreneur_lname']);
$entrepreneur_id_val = $inv['entrepreneur_nid'];
$stmt->close();

// معالجة التوقيع (POST) مع PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$investor_signed) {
    $update = $conn->prepare("UPDATE investments SET investor_signed=1 WHERE id = ? AND investor_id = ? LIMIT 1");
    $update->bind_param("ii", $investment_id, $investor_id);
    $update->execute();
    $update->close();
    $conn->close();
    header("Location: investor_sign.php?investment_id=" . $investment_id);
    exit;
}
$conn->close();
?>
<html>
<head>
  <title>اتفاقية <?= $finance_type === 'loan' ? 'قرض' : 'استثمار' ?> في مشروع: <?= htmlspecialchars($project_name) ?></title>
  <style>
    body { font-family:'Tajawal', Arial, sans-serif; background:#f7f9f6; }
    .sign-container { background:#fff; margin:40px auto; max-width:800px; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,0.08); padding:30px; }
    h2 { color:#11775d; text-align:center; }
    .agree-text { background:#f3f7f5; padding:20px; border-radius:8px; margin:24px 0; font-size:1em; color:#125537; line-height:1.4; }
    .sign-btn { display:block; width:100%; padding:13px; background:#168a53; color:#fff; border:none; border-radius:8px; font-size:1.1em; font-weight:bold; cursor:pointer; margin:22px 0 0; }
    .sign-btn:disabled { background:#b7d3c2; cursor:not-allowed; }
    .back-btn { display:inline-block; padding:10px 14px; background:#f3f7f5; color:#11775d; border-radius:8px; text-decoration:none; margin-top:8px; }
    .notice { text-align:center; color:#11775d; margin-top:12px; }
    .party { font-weight:bold; }
  </style>
</head>
<body>
  <div class="sign-container">
    <h2>
      <?php if($finance_type == 'loan'): ?>
        اتفاقية قرض للمشروع:<br><?= htmlspecialchars($project_name) ?>
      <?php else: ?>
        اتفاقية الاستثمار في مشروع:<br><?= htmlspecialchars($project_name) ?>
      <?php endif; ?>
    </h2>

    <div class="agree-text" style="direction:rtl;">
      <?php if($finance_type === 'equity'): ?>
        <b>عقد استثمار بين مستثمر ورائد أعمال</b><br><br>

        <div class="party"><b>الطرف الأول: المستثمر</b></div>
        الاسم: <?= htmlspecialchars($investor_name) ?><br>
        رقم الهوية/الإقامة: <?= htmlspecialchars($investor_id_val) ?><br><br>

        <div class="party"><b>الطرف الثاني: رائد الأعمال (المؤسس)</b></div>
        الاسم: <?= htmlspecialchars($entrepreneur_name) ?><br>
        رقم الهوية: <?= htmlspecialchars($entrepreneur_id_val) ?><br>
        يمثل المشروع: "<?= htmlspecialchars($project_name) ?>"<br>
        <hr>

        <b>التمهيد</b><br>
        حيث أن الطرف الثاني يملك المشروع أعلاه، وحيث أن الطرف الأول يرغب في الاستثمار فيه؛ لذا فقد أبدى الطرفان موافقتهما وهما بكامل الأهلية الشرعية والنظامية على ما يلي:<br><br>

        <b>البند الأول:</b> يعد التمهيد أعلاه جزءًا لا يتجزأ من هذا العقد ومكملًا له.<br><br>

        <b>البند الثاني: التعاريف</b><br>
        &bull; “المنصة”: تعني منصة “رصين”، وهي المنصة الرقمية الوسيطة التي تم من خلالها عرض وتحليل وتوثيق الصفقة.<br>
        &bull; “الصفقة”: اتفاقية الاستثمار بين الطرفين الموقعة عبر منصة “رصين”.<br>
        &bull; “المشروع”: النشاط التجاري أو الريادي موضوع هذا العقد.<br>
        &bull; “الحصة”: النسبة المئوية من ملكية المشروع التي يحصل عليها المستثمر مقابل مبلغ الاستثمار.<br>
        &bull; “قيمة الاستثمار”: المبلغ المالي المقدم من المستثمر وفقًا لهذا العقد.<br><br>

        <b>البند الثالث: موضوع العقد</b><br>
        يلتزم المستثمر بتقديم مبلغ وقدره <b><?= number_format($amount) ?> ريال سعودي</b> كرأسمال استثماري في المشروع، مقابل حصة نسبتها <b><?= number_format($equity,2) ?>%</b> من ملكية المشروع، وفقًا للتقييم المتفق عليه بين الطرفين.<br><br>

        <b>البند الرابع: التزامات رائد الأعمال</b>
        <ol>
          <li>تخصيص المبلغ المستثمر لتطوير المشروع وتحقيق أهدافه.</li>
          <li>تقديم تقارير دورية عن الأداء المالي والتشغيلي.</li>
          <li>عدم التصرف في الحصص أو تغيير الملكية دون إشعار المستثمر وموافقته الخطية.</li>
          <li>التعاون في الإجراءات النظامية اللازمة.</li>
          <li>الحفاظ على سرية المعلومات المتعلقة بالمستثمر.</li>
        </ol>

        <b>البند الخامس: التزامات المستثمر</b>
        <ol>
          <li>دفع قيمة الاستثمار المتفق عليه في الإطار الزمني المتفق عليه.</li>
          <li>عدم التدخل في الإدارة التشغيلية اليومية إلا فيما يتعلق بحقوقه الاستثمارية.</li>
          <li>الحفاظ على سرية البيانات الخاصة بالمشروع.</li>
        </ol>

        <b>البند السادس: نسبة منصة “رصين”</b><br>
        يحصل الطرفان على أن تحصل منصة “رصين” على نسبة 1% من قيمة الاستثمار كرسوم توثيق وتسهيل.<br>

        <b>البند السابع: مدة العقد</b><br>
        يبدأ سريان العقد من تاريخ توقيعه ويظل نافذًا ما دامت الحصة مملوكة أو حتى نقلها باتفاق الطرفين.<br>

        <b>البند الثامن: السرية</b><br>
        يتعهد الطرفان بالحفاظ على سرية المعلومات وعدم الكشف عنها دون موافقة خطية.<br>

        <b>البند التاسع: حل النزاعات</b><br>
        يُسعى أولًا للتسوية الودية خلال 30 يومًا، ثم تُعرض القضية على الجهة القضائية المختصة في حال الفشل.<br>

        <b>البند العاشر: أحكام عامة</b>
        <ol>
          <li>هذا العقد هو الوثيقة القانونية الملزمة.</li>
          <li>أي تعديل يجب أن يكون بخطية من الطرفين.</li>
          <li>المراسلات عبر منصة رصين تعتبر رسمية.</li>
          <li>حرر من نسختين أصلية لكل طرف.</li>
        </ol>

        <br>منصة رصين (للتوثيق)
      <?php else: ?>
        <!-- صيغة القرض -->
        <b>عقد قرض بين مُقرض (المستثمر) ومُقترض (رائد الأعمال)</b><br><br>

        <div class="party"><b>الطرف الأول: المُقرض (المستثمر)</b></div>
        الاسم: <?= htmlspecialchars($investor_name) ?><br>
        رقم الهوية / الإقامة: <?= htmlspecialchars($investor_id_val) ?><br><br>

        <div class="party"><b>الطرف الثاني: المُقترض (رائد الأعمال)</b></div>
        الاسم: <?= htmlspecialchars($entrepreneur_name) ?><br>
        رقم الهوية / الإقامة: <?= htmlspecialchars($entrepreneur_id_val) ?><br>
        يمثل المشروع: "<?= htmlspecialchars($project_name) ?>"<br>
        <hr>

        <b>التمهيد</b><br>
        حيث أن الطرف الثاني يرغب في تمويل مشروعه عن طريق قرض، وحيث أن الطرف الأول وافق على إقراضه، فقد اتفق الطرفان على ما يلي:<br><br>

        <b>البند الأول:</b> التمهيد جزء لا يتجزأ من هذا العقد.<br><br>

        <b>البند الثاني: التعاريف</b><br>
        &bull; “المنصة”: منصة رصين.<br>
        &bull; “الصفقة”: اتفاقية القرض.<br>
        &bull; “القرض”: المبلغ المقدم ويُسدد وفق شروط هذا العقد.<br><br>

        <b>البند الثالث: موضوع العقد</b><br>
        يلتزم المُقرض بتقديم مبلغ قدره <b><?= number_format($amount) ?> ريال سعودي</b>، ويُسدد المُقترض هذا القرض حسب شروط السداد المتفق عليها.<br><br>

        <b>البند الرابع: التزامات المُقترض</b>
        <ol>
          <li>استخدام القرض لتطوير المشروع.</li>
          <li>السداد حسب الجدول المتفق عليه بعد فترة سماح إن وُجدت.</li>
          <li>تقديم تقارير عند الطلب.</li>
          <li>عدم الاقتراض من جهة أخرى تُضعف السداد.</li>
          <li>الحفاظ على السرية.</li>
        </ol>

        <b>البند الخامس: التزامات المُقرض</b>
        <ol>
          <li>صرف مبلغ القرض المتفق عليه.</li>
          <li>عدم التدخل في إدارة المشروع.</li>
          <li>الحفاظ على سرية المعلومات.</li>
        </ol>

        <b>البند السادس: نسبة منصة “رصين”</b><br>
        تحصل المنصة على 1% كرسوم تسهيل وتوثيق.<br>

        <b>البند السابع: السداد</b>
        <ol>
          <li>يبدأ السداد بعد فترة السماح.</li>
          <li>يُسدد على أقساط منتظمة.</li>
          <li>التأخر يمنح الحق للمُقرض بالمطالبة بالمبلغ المتبقي.</li>
        </ol>

        <b>البند الثامن: السرية</b><br>
        الحفاظ على سرية البيانات من الطرفين.<br>

        <b>البند التاسع: حل النزاعات</b><br>
        تسوية ودية أولًا، ثم جهة قضائية مختصة إذا لم تُحل.<br>

        <b>البند العاشر: أحكام عامة</b>
        <ol>
          <li>العقد ملزم قانونيًا.</li>
          <li>التعديلات بخطية من الطرفين.</li>
          <li>المراسلات عبر رصين رسمية.</li>
          <li>حرر بنسختين أصلية.</li>
        </ol>

        <br>منصة رصين (للتوثيق)
      <?php endif; ?>
    </div>

    <?php if (!$investor_signed): ?>
      <form method="post">
        <button class="sign-btn" type="submit">أوافق على الشروط وأوقع إلكترونيًا</button>
      </form>
    <?php else: ?>
      <button class="sign-btn" disabled>تم التوقيع</button>
      <p class="notice">تم توقيعك بنجاح! بانتظار الطرف الآخر.</p>
    <?php endif; ?>

    <a href="javascript:history.back()" class="back-btn">← تراجع</a>
  </div>
</body>
</html>
