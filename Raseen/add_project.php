<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة مشروعي</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="common.css">
</head>
<body class="add-project">

  <h1>إضافة مشروعي</h1>

  <form id="projectForm" action="register_project.php" method="POST" enctype="multipart/form-data">
    <h2>بيانات المشروع</h2>
    <label>اسم المشروع:</label>
    <input type="text" name="project_name" required>

    <label>نبذة تعريفية عن المشروع:</label>
    <textarea name="project_summary" rows="2" placeholder="سيُعرض هذا النص للمستثمرين كتعريف أولي بالمشروع" required></textarea>

    <label>رقم السجل التجاري:</label>
    <input type="text" name="commercial_registration" required>

    <label>المجال:</label>
    <select id="mainField" name="field" onchange="showSubFields()" required>
      <option value="">اختر المجال</option>
      <option value="tech">تقني</option>
      <option value="business">تجاري</option>
      <option value="industrial">صناعي</option>
      <option value="logistics">لوجستي</option>
      <option value="admin">إداري</option>
      <option value="realestate">عقاري</option>
      <option value="tourism">سياحي</option>
    </select>

    <div id="subFieldsContainer" style="margin-top: 15px;"></div>
    <div id="subFieldsContainer" style="margin-top: 20px;"></div>

    <label>المنطقة:</label>
    <select name="region" required>
      <option value="الوسطى">الوسطى</option>
      <option value="الشرقية">الشرقية</option>
      <option value="الغربية">الغربية</option>
      <option value="الجنوبية">الجنوبية</option>
    </select>

    <label for="city">اسم المدينة:</label>
    <input type="text" name="city" id="city" placeholder="مثال: الرياض" required>

    <h2>التشغيل الحالي</h2>
    <label>هل يوجد فريق عمل؟</label>
    <input type="radio" name="has_team" value="نعم" onclick="toggleTeam(true)"> نعم
    <input type="radio" name="has_team" value="لا" onclick="toggleTeam(false)" checked> لا

    <div id="teamSizeContainer" style="display:none">
      <label>عدد أفراد الفريق:</label>
      <input type="number" name="team_size" min="1">
    </div>
    
    <label>هل تدفع رواتب شهرية؟</label>
    <input type="radio" name="has_salaries" value="نعم" onclick="toggleField('salaryInput', true)"> نعم
    <input type="radio" name="has_salaries" value="لا" onclick="toggleField('salaryInput', false)" checked> لا
    <div id="salaryInput" style="display:none">
      <input type="number" name="salary_range" placeholder="أدخل متوسط الراتب للموظف الواحد">
    </div>

    <label>هل تدفع إيجار شهري؟</label>
    <input type="radio" name="has_rent" value="نعم" onclick="toggleField('rentInput', true)"> نعم
    <input type="radio" name="has_rent" value="لا" onclick="toggleField('rentInput', false)" checked> لا
    <div id="rentInput" style="display:none">
      <input type="number" name="rent_cost" placeholder="أدخل قيمة الإيجار الشهري">
    </div>

    <label>هل لديك تكاليف تشغيل أخرى؟</label>
    <input type="radio" name="has_operating_costs" value="نعم" onclick="toggleField('opInput', true)"> نعم
    <input type="radio" name="has_operating_costs" value="لا" onclick="toggleField('opInput', false)" checked> لا
    <div id="opInput" style="display:none">
      <input type="number" name="operating_costs" placeholder="أدخل مجموع تكاليف التشغيل الأخرى">
    </div>

    <label>هل تصرف على التسويق شهريًا؟</label>
    <input type="radio" name="has_marketing" value="نعم" onclick="toggleField('marketingInput', true)"> نعم
    <input type="radio" name="has_marketing" value="لا" onclick="toggleField('marketingInput', false)" checked> لا
    <div id="marketingInput" style="display:none">
      <input type="number" name="marketing_cost" placeholder="أدخل ميزانية التسويق الشهرية">
    </div>

    <label>هل توجد تكاليف أخرى؟</label>
    <input type="radio" name="has_other_costs" value="نعم" onclick="toggleField('otherInput', true)"> نعم
    <input type="radio" name="has_other_costs" value="لا" onclick="toggleField('otherInput', false)" checked> لا
    <div id="otherInput" style="display:none">
      <input type="number" name="other_costs" placeholder="أدخل مجموع التكاليف الأخرى">
    </div>

    <h2>طلب التمويل</h2>

    <label>نوع التمويل المطلوب:</label>
    <div style="margin-bottom:8px;">
      <input type="radio" name="finance_type" value="loan" id="loanRadio" onclick="toggleFinanceForm()" required>
      <label for="loanRadio" style="display:inline;font-weight:normal;">قرض</label>
      <input type="radio" name="finance_type" value="equity" id="equityRadio" onclick="toggleFinanceForm()">
      <label for="equityRadio" style="display:inline;font-weight:normal;">استثمار</label>
    </div>

    <div id="equitySection" style="display:none;">  
      <label>العائد الشهري الحالي قبل الاستثمار:</label>
      <input type="number" name="expected_monthly_return_before_equity" placeholder="مثال: 5000" min="0">

      <label>العائد الشهري المتوقع بعد الاستثمار:</label>
      <input type="number" name="expected_monthly_return_equity" placeholder="مثال: 12000" min="0">
      
      <label>قيمة الاستثمار المطلوب (بالريال):</label>
      <input type="number" name="requested_amount_equity" placeholder="مثال: 150000" min="0">

      <label>سبب طلب الاستثمار:</label>
      <textarea name="investment_reason_equity" rows="3" placeholder="وضح باختصار سبب احتياجك للاستثمار"></textarea>

      <label>نسبة المستثمر من المشروع (%):</label>
      <input type="number" name="investor_share" placeholder="مثال: 20" min="1" max="100">
    </div>

    <div id="loanSection" style="display:none;">
      <label>العائد الشهري الحالي قبل القرض:</label>
      <input type="number" name="expected_monthly_return_before_loan" placeholder="مثال: 5000" min="0">

      <label>العائد الشهري المتوقع بعد القرض:</label>
      <input type="number" name="expected_monthly_return_loan" placeholder="مثال: 12000" min="0">

      <label>قيمة القرض المطلوب (بالريال):</label>
      <input type="number" name="loan_amount" placeholder="مثال: 100000" min="0">

      <label>سبب طلب القرض:</label>
      <textarea name="loan_reason" rows="3" placeholder="وضح لماذا تحتاج إلى القرض"></textarea>

      <label>خطة السداد:</label>
      <select name="loan_repayment_type" id="loanRepaymentType" onchange="toggleLoanRepaymentDetails()">
        <option value="">اختر طريقة السداد</option>
        <option value="one_payment">دفعة واحدة بعد 6 أشهر من استلام القرض</option>
        <option value="installments">دفعات منتظمة تبدأ بعد 6 أشهر من استلام القرض</option>
      </select>

      <div id="installmentDetails" style="display:none;">
        <label>مدة السداد:</label>
        <select name="installment_period">
          <option value="6">ستة أشهر</option>
          <option value="9">تسعة أشهر</option>
          <option value="12">سنة واحدة</option>
          <option value="24">سنتان</option>
          <option value="36">ثلاث سنين</option>
          <option value="48">اربع سنوات</option>
        </select>
      </div>
    </div>

    <button type="submit">إرسال</button>
  </form>

  <script>
    const subFields = {
      tech: ['تطبيقات', 'منصات', 'حلول ذكاء اصطناعي', 'أنظمة إدارة'],
      business: ['محلات', 'متاجر إلكترونية', 'مطاعم', 'مقاهي'],
      industrial: ['مصانع صغيرة', 'منتجات محلية', 'ورش صناعية'],
      logistics: ['مستودعات', 'نقل', 'شحن', 'خدمات لوجستية'],
      admin: ['استشارات', 'موارد بشرية', 'مراكز تدريب إداري'],
      realestate: ['شقق', 'عمائر', 'أراضي', 'تطوير عقاري'],
      tourism: ['فنادق', 'منتجعات', 'مخيمات', 'نُزل', 'فلل سياحية']
    };

    function showSubFields() {
      const selected = document.getElementById("mainField").value;
      const container = document.getElementById("subFieldsContainer");
      container.innerHTML = "";

      if (!selected || !subFields[selected]) return;

      const title = document.createElement("label");
      title.innerHTML = "اختر التفاصيل:";
      container.appendChild(title);

      const group = document.createElement("div");
      group.classList.add("checkbox-group");
      group.style.marginTop = "10px";

      subFields[selected].forEach(item => {
        const label = document.createElement("label");
        label.style.display = "inline-block";
        label.style.marginRight = "10px";
        label.innerHTML = `<input type="checkbox" name="sub_fields[]" value="${item}"> ${item}`;
        group.appendChild(label);
      });

      const otherWrapper = document.createElement("div");
      otherWrapper.style.marginTop = "10px";

      const otherCheckbox = document.createElement("input");
      otherCheckbox.type = "checkbox";
      otherCheckbox.id = "otherCheckbox";
      otherCheckbox.name = "sub_fields[]";
      otherCheckbox.value = "أخرى";
      otherCheckbox.onchange = function () {
        document.getElementById("otherFieldInput").style.display = this.checked ? "block" : "none";
      };

    function toggleTeam(show) {
      document.getElementById("teamSizeContainer").style.display = show ? "block" : "none";
    }

    function toggleField(id, show) {
      document.getElementById(id).style.display = show ? "block" : "none";
    }
    
    function toggleFinanceForm() {
      var isLoan = document.getElementById("loanRadio").checked;
      var isEquity = document.getElementById("equityRadio").checked;
      document.getElementById("loanSection").style.display = isLoan ? "block" : "none";
      document.getElementById("equitySection").style.display = isEquity ? "block" : "none";
    }

    function toggleLoanRepaymentDetails() {
      var repaymentType = document.getElementById("loanRepaymentType").value;
      document.getElementById("installmentDetails").style.display = repaymentType === "installments" ? "block" : "none";
    }
  </script>
</body>
</html>
