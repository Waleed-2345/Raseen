<?php
session_start();
if (!isset($_GET['id'])) {
    die("لم يتم تحديد الاستثمار");
}
$investment_id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) { die("فشل الاتصال: " . $conn->connect_error); }

// جلب بيانات الاستثمار والمشروع وأسماء الأطراف
$q = $conn->query("
    SELECT 
        i.*, 
        p.project_name,
        inv.first_name AS investor_name,
        ent.first_name AS entrepreneur_name
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN investors inv ON i.investor_id = inv.ID
    JOIN entrepreneurs ent ON p.entrepreneur_id = ent.ID
    WHERE i.id = $investment_id
    LIMIT 1
");
if (!$q || $q->num_rows == 0) die("الاستثمار غير موجود");
$inv = $q->fetch_assoc();
$conn->close();

// تحديد نوع المستخدم بناءً على الجلسة
$user_type = "";
if (isset($_SESSION['investor_id']) && $_SESSION['investor_id'] == $inv['investor_id']) {
    $user_type = "investor";
}
if (isset($_SESSION['entrepreneur_id']) && $_SESSION['entrepreneur_id'] == $inv['entrepreneur_id']) {
    $user_type = "entrepreneur";
}

// جلب حالة التوقيع مباشرة من $inv
$investor_signed = intval($inv['investor_signed']); // تأكد أنه رقم وليس نص
$entrepreneur_signed = intval($inv['entrepreneur_signed']); // تأكد أنه رقم وليس نص

// معالجة التوقيع حسب نوع المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "raseen");
    if ($user_type == "investor" && $investor_signed == 0) {
        $conn->query("UPDATE investments SET investor_signed=1 WHERE id=$investment_id LIMIT 1");
    } elseif ($user_type == "entrepreneur" && $entrepreneur_signed == 0) {
        $conn->query("UPDATE investments SET entrepreneur_signed=1 WHERE id=$investment_id LIMIT 1");
    }
    $conn->close();
    header("Location: investment_sign.php?id=$investment_id");
    exit;
}
?>

<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: investor_login.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];
$investment_id = intval($_GET['id'] ?? 0);

$conn = new mysqli("localhost", "root", "", "raseen");
$q = $conn->query("
    SELECT i.*, 
           p.project_name, p.loan_amount, p.investor_share, 
           inv.first_name AS investor_fname, inv.last_name AS investor_lname, inv.national_id AS investor_nid,
           ent.first_name AS ent_fname, ent.last_name AS ent_lname, ent.national_id AS ent_nid
    FROM investments i
    JOIN projects p ON i.project_id = p.id
    JOIN investors inv ON i.investor_id = inv.ID
    JOIN entrepreneurs ent ON p.entrepreneur_id = ent.ID
    WHERE i.id=$investment_id AND i.investor_id=$investor_id
    LIMIT 1
");
if (!$q || $q->num_rows == 0) die("لا تملك صلاحية التوقيع على هذا الاستثمار.");
$inv = $q->fetch_assoc();
$conn->close();

// نوع العقد: equity = استثمار، loan = قرض
$finance_type = $inv['type']; // يجب أن يكون لديك هذا الحقل (type) بقيمة 'equity' أو 'loan'

// متغيرات عامة
$project_name     = $inv['project_name'];
$investor_name    = $inv['investor_fname'] . ' ' . $inv['investor_lname'];
$investor_id_val  = $inv['investor_nid'];
$entrepreneur_name= $inv['ent_fname'] . ' ' . $inv['ent_lname'];
$entrepreneur_id_val = $inv['ent_nid'];
$amount           = $inv['amount']; // القرض أو الاستثمار
$equity           = $inv['equity_percentage']; // النسبة للاستثمار
$installment      = $inv['installment_amount'] ?? ''; // مبلغ القسط (للقرض)
$loan_duration    = $inv['loan_duration'] ?? 'سنة';   // مدة القرض (إن وجد)
$investor_signed  = $inv['investor_signed'];

// معالجة التوقيع (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$investor_signed) {
    $conn = new mysqli("localhost", "root", "", "raseen");
    $conn->query("UPDATE investments SET investor_signed=1 WHERE id=$investment_id LIMIT 1");
    $conn->close();
    header("Location: investment_sign_investor.php?id=$investment_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اتفاقية الاستثمار</title>
<style>
  body { font-family:'Tajawal',Arial,sans-serif; background:#f7f9f6; }
  .sign-container { background:#fff; margin:40px auto; max-width:650px; border-radius:16px; box-shadow:0 4px 24px #00968815; padding:30px; }
  h2 { color:#11775d; text-align:center; }
  .agree-text { background:#f3f7f5; padding:15px; border-radius:8px; margin:24px 0; font-size:1.08em; color:#125537; }
  .sign-btn { display:block; width:100%; padding:13px; background:#168a53; color:#fff; border:none; border-radius:8px; font-size:1.14em; font-weight:bold; cursor:pointer; margin:22px 0 0; }
  .sign-btn:disabled { background:#b7d3c2; cursor:not-allowed; }
  .back-btn { display:block; width:100%; padding:11px; margin-top:14px; background:#f3f7f5; color:#11775d; border:none; border-radius:8px; font-size:1.09em; font-weight:bold; text-align:center; text-decoration:none; transition:.2s; }
  .back-btn:hover { background: #e2f2ea; color: #168a53; }
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
      <?php if($finance_type == 'equity'): ?>
        <!-- ====== صيغة الاستثمار ====== -->
        <b>عقد استثمار بين مستثمر ورائد أعمال</b><br><br>
        <b>الطرف الأول: المستثمر</b><br>
        الاسم: <?= htmlspecialchars($investor_name) ?><br>
        رقم الهوية/الإقامة: <?= htmlspecialchars($investor_id_val) ?><br>
        <b>الطرف الثاني: رائد الأعمال (المؤسس)</b><br>
        الاسم: <?= htmlspecialchars($entrepreneur_name) ?><br>
        رقم الهوية: <?= htmlspecialchars($entrepreneur_id_val) ?><br>
        يمثل المشروع: "<?= htmlspecialchars($project_name) ?>"<br>
        <hr>
        <b>التمهيد</b><br>
        حيث أن الطرف الثاني يملك وحيث أن الطرف الأول، لذا فقد أبدى الطرفان موافقتهما وهما بكامل الأهلية المعتبرة شرعًا ونظامًا على مايلي:
        <br><br>
        <b>البند الأول: يعد التمهيد أعلاه جزءًا لايتجزأ من هذا العقد ومكملًا له.</b>
        <br><br>
        <b>البند الثاني: التعاريف</b><br>
        في هذا العقد، تكون للكلمات والعبارات التالية المعاني الموضحة قرين كل منها ما لم يقتضِ السياق خلاف ذلك:<br>
        &bull; “المنصة”: تعني منصة “رصين”، وهي المنصة الرقمية الوسيطة التي تم من خلالها عرض، تحليل، وتوثيق الصفقة.<br>
        &bull; “الصفقة”: اتفاقية الاستثمار بين الطرفين الموقعة عبر منصة “رصين”.<br>
        &bull; “المشروع”: النشاط التجاري أو الريادي المملوك لرائد الأعمال، موضوع هذا العقد.<br>
        &bull; “الحصة”: النسبة المئوية من ملكية المشروع التي يحصل عليها المستثمر مقابل مبلغ الاستثمار.<br>
        &bull; “قيمة الاستثمار”: المبلغ المالي المقدم من المستثمر وفقًا لهذا العقد.<br><br>
        <b>البند الثالث: موضوع العقد</b><br>
        بموجب هذا العقد، يلتزم المستثمر بتقديم مبلغ وقدره <b><?= number_format($amount) ?> ريال سعودي</b>
        كرأسمال استثماري في المشروع المملوك لرائد الأعمال، مقابل حصة نسبتها <b><?= number_format($equity,2) ?>%</b> من أسهم أو ملكية المشروع، وفقًا للتقييم المتفق عليه بين الطرفين.<br><br>
        <b>البند الرابع: التزامات رائد الأعمال</b>
        <ol>
          <li>تخصيص المبلغ المستثمر لتطوير المشروع وتحقيق أهدافه التجارية.</li>
          <li>تقديم تقارير دورية حول الأداء المالي والتشغيلي للمشروع.</li>
          <li>عدم التصرف في أي من الحصص أو تغيير هيكل الملكية دون إشعار المستثمر وموافقته الخطية.</li>
          <li>التعاون الكامل في أي إجراءات نظامية مطلوبة لتسجيل أو نقل الملكية.</li>
          <li>الحفاظ على سرية كافة البيانات والمعلومات المتعلقة بالمستثمر.</li>
        </ol>
        <b>البند الخامس: التزامات المستثمر</b>
        <ol>
          <li>دفع قيمة الاستثمار المتفق عليه خلال مدة لا تتجاوز (سنتين) من تاريخ توقيع العقد.</li>
          <li>عدم التدخل في الشؤون التشغيلية اليومية للمشروع، إلا فيما يتعلق بحقوقه الاستثمارية.</li>
          <li>الحفاظ على سرية المعلومات والبيانات الخاصة بالمشروع وعدم مشاركتها مع أي طرف ثالث دون موافقة خطية.</li>
        </ol>
        <b>البند السادس: نسبة منصة “رصين”</b><br>
        اتفق الطرفان على أن تحصل منصة “رصين” على نسبة 1% (واحد في المئة) من إجمالي قيمة الاستثمار كرسوم توثيق وتسهيل، وتُخصم مباشرة من إجمالي الصفقة عند تنفيذها.<br>
        <b>البند السابع: مدة العقد</b><br>
        يبدأ سريان هذا العقد من تاريخ توقيعه، ويظل نافذًا ما دامت الحصة مملوكة للمستثمر، أو إلى حين نقلها أو بيعها رسميًا باتفاق الطرفين.<br>
        <b>البند الثامن: السرية</b><br>
        يتعهد الطرفان بالحفاظ على سرية جميع المعلومات والمستندات والبيانات المتعلقة بالمشروع أو الاستثمار وعدم الكشف عنها لأي طرف ثالث دون موافقة خطية مسبقة.<br>
        <b>البند التاسع: حل النزاعات</b><br>
        في حال نشوء أي نزاع بين الطرفين بخصوص هذا العقد، يتم أولًا اللجوء إلى التسوية الودية خلال مدة (30) يومًا، وفي حال عدم التوصل إلى حل، يتم عرض النزاع على الجهة القضائية المختصة في المملكة العربية السعودية.<br>
        <b>البند العاشر: أحكام عامة</b>
        <ol>
          <li>هذا العقد يُعد الوثيقة القانونية المعتمدة والملزمة للطرفين.</li>
          <li>لا يجوز تعديل أي بند من بنوده إلا بموجب اتفاق خطي موقع من الطرفين.</li>
          <li>تُعتمد جميع المراسلات المنفذة عبر منصة “رصين” كوسيلة رسمية موثوقة.</li>
          <li>حُرر هذا العقد من نسختين أصليتين، موقعة من قبل طرفي العقد، وتم توثيقه عبر منصة رصين، وقد تسلم كل طرف نسخة منه.</li>
        </ol>
        <br>
        منصة رصين (للتوثيق)
      <?php else: ?>
        <!-- ====== صيغة القرض ====== -->
        <b>عقد قرض بين مُقرض (مستثمر) ومُقترض (رائد أعمال)</b><br><br>
        <b>الطرف الأول: المُقرض (المستثمر)</b><br>
        الاسم: <?= htmlspecialchars($investor_name) ?><br>
        رقم الهوية / الإقامة: <?= htmlspecialchars($investor_id_val) ?><br>
        <b>الطرف الثاني: المُقترض (رائد الأعمال)</b><br>
        الاسم: <?= htmlspecialchars($entrepreneur_name) ?><br>
        رقم الهوية / الإقامة: <?= htmlspecialchars($entrepreneur_id_val) ?><br>
        يمثل المشروع: "<?= htmlspecialchars($project_name) ?>"<br>
        <hr>
        <b>التمهيد</b><br>
        حيث أن الطرف الثاني يرغب في تمويل مشروعه عن طريق الحصول على قرض مالي، وحيث أن الطرف الأول وافق على إقراضه، فقد أبدى الطرفان موافقتهما وهما بكامل الأهلية المعتبرة شرعًا ونظامًا على ما يلي:<br><br>
        <b>البند الأول: التمهيد</b><br>
        يعد التمهيد أعلاه جزءًا لا يتجزأ من هذا العقد ومكملًا له.<br><br>
        <b>البند الثاني: التعاريف</b><br>
        في هذا العقد، تكون للكلمات والعبارات التالية المعاني الموضحة قرين كل منها ما لم يقتضِ السياق خلاف ذلك:<br>
        &bull; “المنصة”: تعني منصة “رصين”، وهي المنصة الرقمية الوسيطة التي تم من خلالها عرض، تحليل، وتوثيق الصفقة.<br>
        &bull; “الصفقة”: اتفاقية القرض بين الطرفين الموقعة عبر منصة “رصين”.<br>
        &bull; “المشروع”: النشاط التجاري أو الريادي المملوك لرائد الأعمال، موضوع هذا العقد.<br>
        &bull; “القرض”: المبلغ المالي المقدم من المُقرض وفقًا لهذا العقد، والذي يتعين على المُقترض سداده.<br>
        &bull; “فترة السماح”: المدة المحددة قبل بدء سداد القرض، إن وجدت.<br><br>
        <b>البند الثالث: موضوع العقد</b><br>
        بموجب هذا العقد، يلتزم المُقرض بتقديم مبلغ وقدره <b><?= number_format($amount) ?> ريال سعودي</b> للطرف الثاني (المُقترض) كقرض مالي مخصص لتمويل مشروعه، على أن يلتزم المُقترض بسداد كامل مبلغ القرض وفقًا لشروط هذا العقد.<br>
        <b>البند الرابع: التزامات المُقترض (رائد الأعمال)</b>
        <ol>
          <li>تخصيص مبلغ القرض حصريًا لتطوير المشروع وتحقيق أهدافه التجارية.</li>
          <li>الالتزام بسداد القرض خلال مدة لا تتجاوز (<?= htmlspecialchars($loan_duration) ?>) تبدأ بعد فترة سماح مدتها (6 أشهر) من تاريخ توقيع العقد.</li>
          <li>تقديم تقارير دورية حول الأداء المالي للمشروع عند طلب المُقرض.</li>
          <li>عدم الاقتراض من طرف آخر بشكل يخل بقدرته على سداد هذا القرض.</li>
          <li>الحفاظ على سرية كافة البيانات والمعلومات المتعلقة بالمُقرض.</li>
        </ol>
        <b>البند الخامس: التزامات المُقرض (المستثمر)</b>
        <ol>
          <li>دفع مبلغ القرض المتفق عليه بالكامل خلال مدة لا تتجاوز (7 أيام) من تاريخ توقيع العقد.</li>
          <li>عدم التدخل في إدارة المشروع أو شؤونه التشغيلية اليومية.</li>
          <li>الحفاظ على سرية جميع المعلومات والبيانات المتعلقة بالمشروع.</li>
        </ol>
        <b>البند السادس: نسبة منصة “رصين”</b><br>
        اتفق الطرفان على أن تحصل منصة “رصين” على نسبة 1% (واحد في المئة) من إجمالي قيمة القرض كرسوم توثيق وتسهيل، وتُخصم مباشرة عند تنفيذ الصفقة.<br>
        <b>البند السابع: السداد</b>
        <ol>
          <li>يبدأ سداد القرض بعد فترة السماح المتفق عليها ستة شهور بعد اخذ القرض.</li>
          <li>يتم السداد على شكل أقساط شهرية متساوية قدرها <b><?= $installment ? number_format($installment)." ريال سعودي" : "لم يُحدد" ?></b> حتى استيفاء كامل مبلغ القرض.</li>
          <li>في حالة التأخر عن السداد لمدة تتجاوز (60) يومًا، يحق للمُقرض المطالبة بكامل مبلغ القرض المتبقي دفعة واحدة.</li>
        </ol>
        <b>البند الثامن: السرية</b><br>
        يلتزم الطرفان بالحفاظ على سرية جميع المعلومات والبيانات المتعلقة بالقرض أو المشروع وعدم الكشف عنها لأي طرف ثالث دون موافقة خطية مسبقة.<br>
        <b>البند التاسع: حل النزاعات</b><br>
        في حال نشوء أي نزاع بين الطرفين بخصوص هذا العقد، يتم أولًا اللجوء إلى التسوية الودية خلال مدة (30) يومًا، وفي حال عدم التوصل إلى حل، يتم عرض النزاع على الجهة القضائية المختصة في المملكة العربية السعودية.<br>
        <b>البند العاشر: أحكام عامة</b>
        <ol>
          <li>هذا العقد يُعد الوثيقة القانونية المعتمدة والملزمة للطرفين.</li>
          <li>لا يجوز تعديل أي بند من بنوده إلا بموجب اتفاق خطي موقع من الطرفين.</li>
          <li>تُعتمد جميع المراسلات المنفذة عبر منصة “رصين” كوسيلة رسمية موثوقة.</li>
          <li>حُرر هذا العقد من نسختين أصليتين، موقعة من قبل طرفي العقد، وتم توثيقه عبر منصة رصين، وقد تسلم كل طرف نسخة منه.</li>
        </ol>
        <br>
        منصة رصين (للتوثيق)
      <?php endif; ?>
    </div>
    <?php if (!$investor_signed): ?>
      <form method="post">
        <button class="sign-btn" type="submit" name="sign" value="1">أوافق على الشروط وأوقع إلكترونيًا</button>
      </form>
    <?php else: ?>
      <button class="sign-btn" type="button" disabled>تم التوقيع</button>
      <p style='color:#11775d;text-align:center;'>تم توقيعك بنجاح! بانتظار موافقة الطرف الآخر.</p>
    <?php endif; ?>
    <a href="javascript:history.back()" class="back-btn">← تراجع</a>
  </div>
</body>
</html>
