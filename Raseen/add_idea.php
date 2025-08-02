<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: entrepreneur_signup.php");
    exit;
}
?>
<html>
<head>
  <title>إضافة فكرة مشروع</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f3f8f5;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 700px;
      margin: 50px auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    }
    h2 {
      text-align: center;
      color: #035917;
      margin-bottom: 30px;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    .label-note {
      color: #888;
      font-weight: normal;
      font-size: 13px;
      margin-right: 8px;
    }
    input[type="text"],
    textarea,
    select,
    input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    .checkbox-group,
    .radio-group {
      margin-top: 10px;
    }
    .radio-group label,
    .checkbox-group label {
      font-weight: normal;
      margin-right: 20px;
    }
    .hidden {
      display: none;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>إضافة فكرة مشروع</h2>
  <form action="register_idea.php" method="POST" enctype="multipart/form-data">

    <label>اسم الفكرة</label>
    <input type="text" name="idea_name" placeholder="مثال: منصة ربط المستثمرين برواد الأعمال" required>

    <label>الوصف المختصر <span class="label-note">(بحد أقصى 300 حرف)</span></label>
    <textarea name="idea_summary" maxlength="300" rows="3" placeholder="قدّم ملخص سريع للفكرة يجذب الانتباه" required></textarea>

    <label>ما المشكلة التي تستهدفها هذه الفكرة؟</label>
    <textarea name="problem_statement" rows="3" placeholder="مثال: صعوبة وصول رواد الأعمال إلى المستثمرين المناسبين" required></textarea>

    <label>ما هو الحل أو الابتكار الذي تقدمه فكرتك؟</label>
    <textarea name="proposed_solution" rows="3" placeholder="وضح الحل أو الميزة الرئيسية في فكرتك" required></textarea>

    <label>المجال</label>
    <select id="mainField" name="field" onchange="showSubFields()" required>
      <option value="">اختر المجال الرئيسي</option>
      <option value="tech">تقني</option>
      <option value="business">تجاري</option>
      <option value="industrial">صناعي</option>
      <option value="logistics">لوجستي</option>
      <option value="admin">إداري</option>
      <option value="realestate">عقاري</option>
      <option value="tourism">سياحي</option>
      <option value="other">مجال آخر</option>
    </select>
    <div id="subFieldsContainer" style="margin-top: 15px;"></div>

    <label>من هي الفئة المستهدفة؟</label>
    <div class="checkbox-group">
      <label><input type="checkbox" name="target[]" value="الأفراد"> الأفراد</label>
      <label><input type="checkbox" name="target[]" value="الشركات"> الشركات</label>
      <label><input type="checkbox" name="target[]" value="الجهات الحكومية"> الجهات الحكومية</label>
      <label><input type="checkbox" name="target[]" value="طلاب"> طلاب / أكاديميون</label>
      <label><input type="checkbox" name="target[]" value="ذوي الدخل المحدود"> ذوي الدخل المحدود</label>
      <label><input type="text" name="target_other" placeholder="فئة أخرى (اكتب إذا لم تكن مذكورة)"></label>
    </div>

    <label>هل لديك خبرة سابقة في هذا المجال؟</label>
    <div class="radio-group">
      <label><input type="radio" name="has_experience" value="نعم" required> نعم</label>
      <label><input type="radio" name="has_experience" value="لا"> لا</label>
    </div>

    <label>هل لديك شريك في الفكرة؟</label>
    <div class="radio-group">
      <label><input type="radio" name="has_partner" value="نعم" required> نعم</label>
      <label><input type="radio" name="has_partner" value="لا"> لا</label>
    </div>

    <label>ما الهدف الذي ترغب فيه من طرح هذه الفكرة؟</label>
    <div class="radio-group" onchange="togglePriceField()">
      <label><input type="radio" name="investment_goal" value="استثمار كامل" required> أبحث عن مستثمر ينفذ الفكرة بالكامل</label><br>
      <label><input type="radio" name="investment_goal" value="استثمار جزئي"> أبحث عن استثمار جزئي مع شراكة</label><br>
      <label><input type="radio" name="investment_goal" value="بيع الفكرة"> أرغب في بيع الفكرة بالكامل</label><br>
      <label><input type="radio" name="investment_goal" value="تنفيذ جماعي"> أبحث عن فريق عمل لتنفيذها معي</label>
    </div>
    <div id="priceContainer" class="hidden">
      <label>كم هو السعر المطلوب لبيع الفكرة؟</label>
      <input type="number" name="sell_price" min="0" placeholder="حدد المبلغ إذا اخترت بيع الفكرة">
    </div>

    <label>ما مدى جاهزية الفكرة؟</label>
    <div class="radio-group">
      <label><input type="radio" name="readiness_level" value="0" required> 0% – فكرة أولية</label>
      <label><input type="radio" name="readiness_level" value="25"> 25% – تم بحث السوق</label>
      <label><input type="radio" name="readiness_level" value="50"> 50% – تم تحديد النموذج الربحي</label>
      <label><input type="radio" name="readiness_level" value="75"> 75% – تم إعداد خطة العمل</label>
      <label><input type="radio" name="readiness_level" value="100"> 100% – جاهزة للتنفيذ</label>
    </div>

    <label>هل لديك ملف أو خطة عمل أو نموذج يدعم فكرتك؟ <span class="label-note">(اختياري)</span></label>
    <div class="radio-group">
      <label><input type="radio" name="has_support_file" value="نعم" onchange="toggleSupportFileField()"> نعم</label>
      <label><input type="radio" name="has_support_file" value="لا" onchange="toggleSupportFileField()"> لا</label>
    </div>
    <div id="supportFileInput" class="hidden">
      <input type="file" name="support_file" accept=".pdf,.png,.jpg,.jpeg">
      <span style="font-size:12px;color:#888;">(ملف واحد فقط)</span>
    </div>

    <label>اذكر باختصار نتائج بحث السوق أو أسماء المنافسين الرئيسيين <span class="label-note">(اختياري)</span></label>
    <textarea name="market_research_summary" rows="2" placeholder="يمكنك ذكر نتائج بحث أو أسماء منافسين"></textarea>

    <label>ما هي أهم التحديات أو العقبات التي تتوقعها؟ <span class="label-note">(اختياري)</span></label>
    <textarea name="main_challenge" rows="2" placeholder="اكتب أبرز الصعوبات المتوقعة"></textarea>

    <label>ما هي خطوتك القادمة إذا حصلت على الدعم أو التمويل؟ <span class="label-note">(اختياري)</span></label>
    <textarea name="next_step" rows="2" placeholder="حدد ماذا ستفعل إذا وجدت دعم"></textarea>

    <br>
    <button type="submit" style="margin-top:25px;background-color:#035917;color:#fff;padding:12px 30px;font-size:18px;border:none;border-radius:8px;cursor:pointer;">إرسال الفكرة</button>
  </form>
</div>
<script>
  function togglePriceField() {
    const value = document.querySelector('input[name="investment_goal"]:checked')?.value;
    const priceContainer = document.getElementById("priceContainer");
    if (value === "بيع الفكرة") {
      priceContainer.classList.remove("hidden");
    } else {
      priceContainer.classList.add("hidden");
    }
  }
  function toggleSupportFileField() {
    const hasFile = document.querySelector('input[name="has_support_file"]:checked')?.value;
    const fileInput = document.getElementById("supportFileInput");
    fileInput.style.display = hasFile === "نعم" ? "block" : "none";
  }

  // مجالات فرعية حسب المجال الرئيسي
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

    if (!selected) return;

    if (selected !== "other" && subFields[selected]) {
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

      // خيار أخرى
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

      const otherLabel = document.createElement("label");
      otherLabel.appendChild(otherCheckbox);
      otherLabel.append(" أخرى");

      const otherInput = document.createElement("input");
      otherInput.type = "text";
      otherInput.name = "other_field_detail";
      otherInput.placeholder = "اذكر نوع المجال الآخر";
      otherInput.style.display = "none";
      otherInput.id = "otherFieldInput";
      otherInput.style.marginTop = "10px";
      otherInput.style.width = "100%";
      otherInput.style.padding = "8px";
      otherInput.style.border = "1px solid #ccc";
      otherInput.style.borderRadius = "5px";

      otherWrapper.appendChild(otherLabel);
      otherWrapper.appendChild(otherInput);

      group.appendChild(otherWrapper);
      container.appendChild(group);

    } else if (selected === "other") {
      const label = document.createElement("label");
      label.innerHTML = "اذكر نوع المجال الآخر:";
      container.appendChild(label);

      const input = document.createElement("input");
      input.type = "text";
      input.name = "other_field_detail";
      input.placeholder = "اكتب المجال";
      input.required = true;
      container.appendChild(input);
    }
  }
</script>
</body>
</html>
